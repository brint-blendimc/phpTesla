<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** isSanitized Class ******
* This class provides all the functionality of the Sanitize class without returning a sanitized value. Instead, it
* returns TRUE or FALSE as to whether or not the value was actually sanitized.
* 
****** In-Practice Examples ******

// To return a Sanitized Value
$value = Sanitize::word("Hello$!?");		// Returns "Hello"

// To return whether or not the value was sanitized
$value = isSanitized::word("Hello$!?");		// Returns FALSE
$value = isSanitized::word("Hello");		// Returns TRUE


****** Methods Available ******
* This class uses an identical list to the Sanitize methods available.
*/

abstract class isSanitized {

/****** Call Static ******/
	public static function __callStatic($name, $arguments)
	{
		// If this class has the method, return early:
		if(method_exists("isSanitized", $name))
		{
			return true;
		}
		
		// Check if the Method exists in the Sanitize class
		if(method_exists("Sanitize", $name))
		{
			$val = call_user_func_array(array("Sanitize", $name), $arguments);
			
			if($arguments[0] == $val)
			{
				return true;
			}
		}
		
		return false;
	}
	
/****** Sanitizes an Email String ******/
	public static function email
	(
		$email
	)
	
	// if(!isSanitized::email("joe#hotmail.com")) { echo "Email is invalid!"; }
	{
		// If the email has illegal characters
		if(!isSanitized::variable($email, "@.-+"))
		{
			return false;
		}
		
		// Note: Code was modified from <unknown>
		$strpos = strrpos($email, "@");
		
		if(is_bool($strpos) && !$strpos)
		{
			return false;
		}
		else
		{
			$domain = substr($email, $strpos + 1);
			$username = substr($email, 0, $strpos);
			$usernameLength = strlen($username);
			$domainLength = strlen($domain);
			
			// Username Length
			if($usernameLength < 1 || $usernameLength > 64)
			{
				return false;
			}
			
			// Domain length
			else if($domainLength < 1 || $domainLength > 255)
			{
				return false;
			}
			
			// Username Testing
			else if($username[0] == '.' || $username[$usernameLength-1] == '.')
			{
				return false;
			}
			
			// Reject Certain Characters in Domain
			else if(!isSanitized::variable($domain, "-."))
			{
				return false;
			}
			
			// Domain cannot have two consecutive dots
			else if(strpos('..', $domain) > -1)
			{
				return false;
			}
		}
		
		return true;
	}
}