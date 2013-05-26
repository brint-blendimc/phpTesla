<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Security Class ******
* This class provides methods for security handling, such as fingerprinting, password hashing, etc.
*
****** Methods Available ******
* Security::setPassword($password, <$extraSalts...>)		// Hashes a plaintext password into an encrypted one.
* Security::getPassword($password, $hash, <$extraSalts...>)	// Hashes a plaintext password into an encrypted one.
* Security::fingerprintScan()								// Run this to help resist fake sessions.
*/

abstract class Security
{
	/****** Hash a Password ******
	* This function hashes a password (making it one-way) so that its plaintext form cannot be read. It uses the BCrypt
	* algorithm. BCrypt stores its salt in the return hash - the intent here is that can then generate a completely
	* random salt for every single password. This prevents rainbow tables and brute forcing that could otherwise find
	* multiple password combinations.
	* 
	* BCrypt also stores the algorithm that was used to generate it, as well as the strength of the algorithm, such as
	* '$2y$10$' - these values could be changed to reconfigure the strength and processing requirements. We will use $2y
	* and $10 for now.
	* 
	* Example of BCrypt result: $2y$10$_MixedSalt__MixedSalt_loXoraFKf6aKW7C9XOjzBgmIwSeYVha
	* 
	* To strengthen the algorithm, we will append a password salt to the password itself (prior to the encryption being
	* run). This can be done by adding additional parameters to this function. As long as the extra parameters remain
	* the same when you apply the ::getPassword() method, it will match the return hash. For example:
	*
	* $passHash = Security::setPassword("MyPassword", "^ExtraSalt^", "myUser", "MISCSALT");
	*
	* // Returns TRUE
	* return $passHash === Security::getPassword("MyPassword", $passwordHash, "^ExtraSalt^", "myUser", "MISCSALT");
	* 
	* By using additional parameters (such as the user's username or site-wide password hashes), we generate much more
	* complexity in the passwords to crack. The password in the above example would look like this:
	* 
	*		MyPassword^ExtraSalt^myUsernameMISCSALT
	* 
	****** How to call the method ******
	* $passHash = Security::setPassword($password);								// Creates hash without unique salts.
	* $passHash = Security::setPassword($password, "salt!", $userID, "etc");	// Best practice; uses related salts.
	* 
	****** To recover the value of an existing hash ******
	* $data = Database::query("SELECT password FROM users WHERE username='admin' LIMIT 1"));
	* $checkPass = Security::getPassword($_POST['password'], $data['password'], "^ExtraSalt^", "myUser", "MISCSALT");
	* 
	* if($checkPass == $data['password']) { echo "Password Successful"; }
	* 
	****** Parameters ******
	* @string	$password		The plaintext password to hash.
	* <ARGS>	<Extra Salts>	Any additional salt that you want to apply as additional parameters.		
	* 
	* RETURNS <string>			Returns the hashed password to store in the database.
	*/
	public static function setPassword($password)
	{
		/****** Complicate the password by adding extra salts as desired ******/
		$args = func_get_args();
		
		for($i = 1;$i < count($args);$i++)
		{
			$password .= $args[$i];
		}
		
		/****** Create a randomized hash salt that will be saved with the final hash ******/
		
		// BCrypt expects 128 bits of salt encoded in base 64.
		// 22 standard characters + the increase in size from base64 encoding equals 128 bits.
		// Due to the way packaging these works, the 22nd character will swap to a different character in most cases.
		// We must save this randomized hash with the password in order to solve the algorithm later.
		
		$hashSalt = '$2y$10$' . substr(
									str_replace('+', '.', 
										base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(), mt_rand())))
											, 0, 22);
		
		// This algorithm takes advantage of 124 bits of the total 128 bits.
		
		// Return the password hash
		return crypt($password, $hashSalt);
	}
	
	/****** Validate a Password ******
	* This function tests to see if a password matches what is in the database. See the ::setPassword method above for
	* more details on how this is properly done.
	*
	****** How to call the method ******
	* if($sql['password'] == Security::getPassword($_GET['password'], $sql['password'])) { echo "Password success."; }
	*
	* // Note: if the password was generated with multiple salts, you'll need to use them again. For example:
	* ... Security::getPassword($_GET['password'], $sql['password'], "^ExtraSalt^", "myUser", "MISCSALT") ...
	* 
	****** Parameters ******
	* @string	$password		The password that the user enters (or is otherwise being tested).
	* @string	$passwordHash	The encrypted hash that was saved and is now being compared against.
	* <ARGS>	<Extra Salts>	Any additional salt that you want to apply as additional parameters.		
	* 
	* RETURNS <string>			Returns the hashed password to test if it's identical to the one stored.
	*/
	public static function getPassword($password, $passwordHash)
	{
		/****** Complicate the password by adding extra salts as desired ******/
		$args = func_get_args();
		
		for($i = 1;$i < count($args);$i++)
		{
			$password .= $args[$i];
		}
		
		/****** Reuse the Algorithm Hash to solve the encryption ******/
		
		// If the entire hash string was dumped, reduce it to the first 29 characters that we're looking for:
		if(strlen($passwordHash) > 29)
		{
			$passwordHash = substr($passwordHash, 0, 29);
		}
		
		return crypt($password, $passwordHash);
	}
	
	/****** [[ Fingerprinting & Updating Sessions ]] ******
	* Scans to see if the user agent's (session) fingerprint appears legitimate. If it does, continue normally.
	* Otherwise, force a new session.
	* 
	****** How to call the method ******
	* Security::fingerprintScan();
	* 
	****** Parameters ******
	* RETURNS <bool>			Returns TRUE after running the method, or FALSE if there isn't a session.
	*/
	public static function fingerprintScan()
	{
		// Return false if there is no session active
		if(!isset($_SESSION))
		{
			return false;
		}
		
		// Check if the HTTP_REFERER value is set, and if so, make sure it was from within the site:
		// The site referer should start with "http://$_SERVER['HTTP_HOST']"
		// We want to destroy sessions if the referral was made from another site.
		if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST']) !== 0)
		{
			session_destroy();
		}
		
		// Check if the user agent matches up between page loads.
		// If it doesn't, that's suspicious - let's destroy the session, since it's probably someone
		// trying to hijack the session.
		if(isset($_SESSION['USER_AGENT']))
		{
			if($_SERVER['HTTP_USER_AGENT'] !== $_SESSION['USER_AGENT'])
			{
				session_destroy();
			}
		}
		elseif(isset($_SERVER['HTTP_USER_AGENT']))
		{
			// Keep track of the current user agent
			$_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
		}
		
		return true;
	}
}
