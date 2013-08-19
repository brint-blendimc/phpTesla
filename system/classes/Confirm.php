<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Confirm Class ******
* This class provides a method for confirmation URLs quickly and efficiently.
* In order for this class to work properly, /confirm.php must exist in your application path.
* 
****** Example of its Use ******

// Create a Confirmation Link
	
	# Returns "http://mydomain.com/confirm/reset-password/myUsername/1375207477-10-29d2bfeab207a018f8b94fe9906c"
	echo "To reset your password, please visit the following url: " . Confirm::create("reset-password", "myUsername", 10);

// Validate a Confirmation Link

	# On the Confirmation Page above, with each URL segment being $url[$x]
	if(Confirm::validate("reset-password", "myUsername", "1375207477-10-29d2bfeab207a018f8b94fe9906c"))
	{
		echo "Congratulations, this confirmation link is still valid!";
	}

****** Methods Available ******
* Confirm::create($type, $uniqueIdentifier, $expireInHours = 24)	// Generates a confirmation link.
* Confirm::validate($type, $uniqueIdentifier, $token)				// Validates a confirmation link.
*/

abstract class Confirm {


/****** Create Confirmation Link ******/
	public static function create
	(
		$type					/* <str> The type of confirmation you're creating. */,
		$uniqueIdentifier		/* <str> A string to uniquely identify the user or link being generated. */,
		$expireInHours = 24		/* <int> Hours until it becomes invalid. Setting to 0 allows it forever. */
	)							/* RETURNS <str> : Valid URL link. */
	
	// Confirm::create("email-confirm", "joebruiser@hotmail.com");
	{
		// Prepare Key
		$timeCreated = time();
		$key = Security::quickHash($uniqueIdentifier, $type, $timeCreated, $expireInHours);
		
		// Return the Confirmation URL
		return SITE_URL . "/confirm/" . $type . "/" . $uniqueIdentifier . "/" . $timeCreated . "-" . $expireInHours . "-" . $key;
	}
	
	
/****** Validate Confirmation Link ******/
	public static function validate
	(
		$type					/* <str> The type of confirmation you're validating. */,
		$uniqueIdentifier		/* <str> The unique identifier for the validation. */,
		$token					/* <str> The full confirmation key and token. */
	)							/* RETURNS <bool> : TRUE if valid, FALSE otherwise. */
	
	// Confirm::validate("email-confirm", "joebruiser@hotmail.com", "1375206681-10-f56662035ca2f1eeca6f67ee46b2");
	{
		// Check Keys
		$keys = explode("-", $token);
		
		// Make sure the key hasn't expired
		if($keys[0] + ($keys[1] * 3600) < time())
		{
			return false;
		}
		
		// Quick Hash
		$testHash = Security::quickHash($uniqueIdentifier, $type, $keys[0], $keys[1]);
		
		// Check if the Confirmation Link is valid
		if($keys[2] == $testHash)
		{
			return true;
		}
		
		return false;
	}
	
}

