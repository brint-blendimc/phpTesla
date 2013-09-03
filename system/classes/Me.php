<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** "Me" Class (Your Personal User Session) ******
* This class sets up and handles the active user - that is, the user currently logged in.
* This class is based around the session variable $_SESSION[USER_SESSION]
* 
****** Methods Available ******
* Me::initialize();					// Initializes the user for the page view.
* Me::id()							// Loads your personal ID.
* Me::loggedIn()					// Returns TRUE if you are logged in.
* Me::login($userID)				// Login as a particular user (sets session variable).
* Me::load($userID)					// Loads your personal user data.
* Me::logout()						// Logs the current user out.
* Me::resetToken()					// Resets the user's token.
* Me::setCookie()					// Set cookies to remember the user.
* Me::rememberMe()					// Auto-log user if they have authentic cookies.
* 
*/

abstract class Me {
	
	
/****** Prepare Variables ******/
	public static $id = 0;
	public static $token = "";
	public static $data = array();
	
	
/****** Initialize the Database ******/
	public static function initialize(
	)			/* RETURNS <bool> : TRUE. */
	
	// Me::initialize();
	{
		// If you are logged in
		if(isset($_SESSION[USER_SESSION]) && isset($_SESSION[USER_SESSION]['id']))
		{
			self::load($_SESSION[USER_SESSION]['id']);
		}
		
		// If you're not logged in, run a "remember me" check
		else
		{
			self::rememberMe();
		}
		
		return true;
	}
	
	
/****** Return My ID ******/
	public static function id (
	)				/* RETURNS <int> : Active user's current ID, or 0 on false. */
	
	// Me::id()
	{
		if(isset($_SESSION[USER_SESSION]['id']))
		{
			return $_SESSION[USER_SESSION]['id'];
		}
		
		return 0;
	}
	
	
/****** Check if Logged In ******/
	public static function loggedIn (
	)				/* RETURNS <bool> : TRUE if logged in. */
	
	// if(Me::loggedIn()) { echo "You are logged in!"; }
	{
		if(isset($_SESSION[USER_SESSION]['id']))
		{
			return true;
		}
		
		return false;
	}
	
	
/****** Login ******/
	public static function login
	(
		$userID				/* <str> The ID of the account to log in as. */,
		$remember = false	/* <bool> If set to true, add a remember me cookie. */
	)						/* RETURNS <bool> : TRUE if login validation was successful, FALSE if not. */
	
	// Me::login(15, true)
	{
		// Prepare User Session
		$_SESSION[USER_SESSION] = array(
				"id"			=> $userID
			);
		
		// Set a new token for the user
		$newToken = self::resetToken($userID);
		
		// Load your data
		self::load($userID);
		
		// Set "Remember Me" cookie if applicable
		if($remember == true && $newToken !== false)
		{
			self::setCookie($userID);
		}
		
		// Update the last login time (to right now)
		Database::query("UPDATE `users` SET date_lastLogin=? WHERE id=? LIMIT 1", array(time(), $userID));
		
		return true;
	}
	
	
/****** Log Out ******/
	public static function logout()
		/* RETURNS <bool> : TRUE after removing all login sessions and cookies. */
	
	// Me::logout()
	{
		Cookie::delete("userID");
		
		if(isset($_SESSION[USER_SESSION]))
		{
			unset($_SESSION[USER_SESSION]);
		}
		
		return true;
	}
	
	
/****** Load My Data ******/
	public static function load
	(
		$userID		/* <int> The ID of the user you would like to load. */
	)				/* RETURNS <bool> : TRUE on success, FALSE if failed. */
	
	// Me::load($userID)
	{
		// Get information from the database
		self::$data = User::getData($userID);
		
		// Make sure the user exists
		if(isset(self::$data['id']))
		{
			// Set your session ID, which corresponds to your database user ID
			self::$id = self::$data['id'];
			
			// Set your authentication token
			self::$token = self::$data['auth_token'];
		}
		
		return true;
	}
	
	
/****** Set Cookie ******/
	private static function setCookie(
	)				/* RETURNS <bool> : TRUE on success, FALSE if failed. */
	
	// self::setCookie()
	{
		// Get the User Data
		$userData = User::getData(self::$id, "password, auth_token");
		
		return Cookie::setValue("userID", self::$id, $userData['password'] . $userData['auth_token']);
	}
	
	
/****** "Remember Me" Setting ******
Run this if you're not logged in. This will check for auto-login authentication, and self-log if it's valid. */

	private static function rememberMe(
	)		/* RETURNS <bool> : TRUE on success, FALSE if failed. */
	
	// self::rememberMe()
	{
		// Make sure the user is using the remember me cookie and that the user isn't already logged in.
		if(!isset($_COOKIE['userID']) or isset($_SESSION[USER_SESSION]))
		{
			return false;
		}
		
		// Make sure the cookie points to a valid user - if it is, recover the user data
		$userData = User::getData($_COOKIE['userID'], "id, password, auth_token");
		
		if(!isset($userData['id'])) { return false; }
		
		// Check the cookie authentication
		$retVal = Cookie::returnValue("userID", $userData['password'] . $userData['auth_token']);
		
		if($retVal == false) { return false; }
		
		// Cookie checks have passed, log in
		return self::login($userData['id'], true);
	}
	
	
/****** Resets the User Token ******/
	private static function resetToken(
	)				/* RETURNS <str> : Returns the new authentication token. */
	
	// self::resetToken()
	{
		// Run the authentication token change
		$newToken = substr(base64_encode(hash('md5', rand(0, 99999999) . rand(0, 99999999) . time(), true)), 0, 22);
		
		Database::query("UPDATE users SET auth_token=? WHERE id=? LIMIT 1", array($newToken, self::$id));
		
		// Return the new authentication token
		return $newToken;
	}
}
