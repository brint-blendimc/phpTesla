<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Cookie Class ******
* This class provides a method for remembering user safely.
* 
****** Example of its Use ******

// To set the cookies, run Cookie::setValue(...)
if($justLoggedIn == true OR $regenerateCookies == true)
{
	Cookie::setValue("userID", $user['id'], $user['password'] . $user['auth_token']);
}

// Every page view, you'll want to check if your session forgets. If it does, use the reminder.
if(!isset($_SESSION[USER_SESSION]['id']))
{
	$retVal = Cookie::returnValue("userID", $user['password'] . $user['auth_token']);
	
	if($retVal !== false)
	{
		$_SESSION[USER_SESSION]['id'] = $retVal;
	}
}

****** Methods Available ******
* Cookie::setValue($cookieName, $valueToRemember, $salt, $expiresInDays = 90) // Generates cookies for you.
* Cookie::returnValue($cookieName, $salt)		// Returns your cookie value after authenticating it.
* Cookie::delete($cookieName);					// Deletes your authenticated cookies.
*/

abstract class Cookie {


/****** Create Cookies w/ Authentication ******/
	public static function setValue
	(
		$cookieName				/* <str> The name of the cookie you're trying to remember. */,
		$valueToRemember		/* <str> The value you want to remember. */,
		$salt					/* <str> The unique salt you want to use to keep the cookie safe. */,
		$expiresInDays = 90		/* <str> The amount of time the cookie should last, in days. */
	)							/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// Cookie::setValue('userID', $user['id'], $user['password'] . "extraSalt");
	{
		// Make sure that you have a USER AGENT value set
		if(!isset($_SESSION['USER_AGENT']) or !defined("SITE_SALT"))
		{
			return false;
		}
		
		// Prepare Token
		$token = Security::strongHash($valueToRemember . md5($_SESSION['USER_AGENT']) . SITE_SALT . $salt);
		
		// Set the cookie
		setcookie($cookieName, $valueToRemember, time() + (3600 * $expiresInDays), "/");
		setcookie($cookieName . "_key", $token, time() + (3600 * $expiresInDays), "/");
		
		return true;
	}
	
	
/****** Return Cookie Value After Authentication ******/
	public static function returnValue
	(
		$cookieName		/* <str> The name of the cookie you're trying to remember. */,
		$salt			/* <str> The unique identifier for the validation. */
	)					/* RETURNS <str> : the value to remember if successful, or FALSE if validation failed. */
	
	// Cookie::returnValue("userID", $user['password'] . "extraSalt");
	{
		// Make sure that you have a USER AGENT value set
		if(!isset($_SESSION['USER_AGENT']) or !defined("SITE_SALT"))
		{
			return false;
		}
		
		// Check if Cookie is Valid
		if(isset($_COOKIE[$cookieName]) && isset($_COOKIE[$cookieName . "_key"]))
		{
			// Prepare Token
			$token = Security::strongHash($_COOKIE[$cookieName] . md5($_SESSION['USER_AGENT']) . SITE_SALT . $salt);
			
			if($_COOKIE[$cookieName . "_key"] == $token)
			{
				return $_COOKIE[$cookieName];
			}
		}
		
		return false;
	}
	
	
/****** Delete Cookies ******/
	public static function delete
	(
		$cookieName		/* <str> The name of the cookie you're trying to delete. */
	)					/* RETURNS <bool> : TRUE */
	
	// Cookie::delete("userID");
	{
		// Remove desired Cookie and its associated key
		setcookie($cookieName, "", time() - 360000, "/");
		setcookie($cookieName . "_key", "", time() - 360000, "/");
		
		// Remove Global Cookie Values Immediately
		if(isset($_COOKIE[$cookieName]))
		{
			unset($_COOKIE[$cookieName]);
		}
		
		if(isset($_COOKIE[$cookieName . "_key"]))
		{
			unset($_COOKIE[$cookieName]);
		}
		
		return true;
	}
}

