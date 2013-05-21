<?php if(!defined("IS_SAFE")) { die("No direct script access allowed."); }

/****** Sanitize Class ******
* User input can never be trusted. The best way to ensure that user input is safe is to use a whitelisting technique,
* such that all user input can only have specific characters that you've deemed acceptable. Though this doesn't
* protect against every type of vulnerability, it will help ward off many problems.
* 
* If any of these Sanitize methods catch a value that doesn't belong, it will notify the Security class for proper
* handling. The information will be documented appropriately so that you can track who sent the unsanitized data
* and what the unsanitized data was.
* 
* These methods each include a second parameter called $extraChars. The $extraChars parameter allows you to add
* specific characters to the whitelist that aren't already included with the method's default whitelist.
*
* For example, the following methods would produce the following whitelists:
* Sanitize::number($userInput);				// Whitelist: 0123456789
* Sanitize::number($userInput, "abcdef");	// Whitelist: 0123456789abcdef
* 
****** Methods Available ******
* Sanitize::word($userInput, $extraChars = "");			// Allows letters
* Sanitize::variable($userInput, $extraChars = "");		// Allows letters, numbers, underscore
* Sanitize::safeword($userInput, $extraChars = "");		// Allows letters, numbers, space, underscore, dash, period
* Sanitize::string($userInput, $extraChars = "");		// Allows letters, numbers, common symbols
* Sanitize::text($userInput, $extraChars = "");			// Allows letters, numbers, whitespace, puncutation, symbols
*
* Sanitize::number($userInput, $extraChars = "");		// Allows numbers
* Sanitize::number($userInput, $maxRange = 0);			// If 2nd parameter is a number, applies a max range
* Sanitize::number($userInput, $minRange = 0, $maxRange = 0);	// Use this format for min and max range
* 
* Sanitize::directory($userInput)						// Sanitizes an allowable directory path (including slashes)
* Sanitize::filepath($userInput);						// Sanitizes an allowable file path (including slashes)
* Sanitize::filename($userInput);						// Sanitizes an allowable filename (with proper extensions)
* Sanitize::email($userInput);							// Sanitizes a proper email
* Sanitize::url($userInput);							// Sanitizes an allowable URL
*
* Sanitize::warnOfPotentialAttack($unsafeString);		// Alert the admins of an unsafe string that was used.
*/

abstract class Sanitize
{
	/****** Sanitize Word ******
	* Sanitizes user input so that only letters are allowed. Capital letters and lower case letters are both allowed.
	* If there are characters present that don't belong, it will alert the Security class to warn of potential hacks.
	* 
	****** How to call the method ******
	* $word = Sanitize::word($word);			// Allows letters
	* $word = Sanitize::word($word, "12@");		// Allows letters, the digits "1" and "2", and the symbol "@"
	* 
	****** Parameters ******
	* @string	$valueToSanitize	The value you're going to sanitize.
	* ?string	$extraChars			A list of specific characters to add to the whitelist.
	* 
	* RETURNS <string>				Returns a sanitized value (as an acceptably formatted word).
	*/
	public static function word($valueToSanitize, $extraChars = "")
	{
		/****** Prepare Variables *****/
		$whitelist = "eariotnslcudpmhgbfywkvEARIOTNSLCUDPMHGBFYWKV" . $extraChars . "xzjqXZJQ";
		$originalString = $valueToSanitize;
		$illegalChars = 0;
		
		// Cycle through each letter in the word to sanitize and check if there is a character that shouldn't be there.
		for($i = 0;$i < strlen($valueToSanitize);$i++)
		{
			// If something shouldn't be there, strip it out.
			if(strpos($whitelist, $valueToSanitize[$i]) === false)
			{
				$valueToSanitize = substr_replace($valueToSanitize, "", $i, 1);
				$i--;
				$illegalChars++;
			}
		}
		
		// Send a warning if the user input had to be sanitized
		if($originalString != $valueToSanitize)
		{
			// Set the warning level higher if there is an abundance of illegal characters
			$warningLevel = ($illegalChars > 5 ? ($illegalChars > 10 ? 2 : 1) : 0);
			
			self::warnOfPotentialAttack($originalString, "Illegal Characters", $warningLevel);
		}
		
		return $valueToSanitize;
	}
	
	/****** Sanitize a Variable ******
	* Sanitizes user input so that only letters, numbers, and underscores are allowed.
	* If there are characters present that don't belong, it will alert the Security class to warn of potential hacks.
	* 
	****** How to call the method ******
	* $word = Sanitize::variable($variable);			// Allows letters, numbers, underscores
	* $word = Sanitize::variable($variable, "#!");		// Allows letters, numbers, undescores, "#", and "!"
	* 
	****** Parameters ******
	* @string	$valueToSanitize	The value you're going to sanitize.
	* ?string	$extraChars			A list of specific characters to add to the whitelist.
	* 
	* RETURNS <string>				Returns a sanitized value (as an acceptably formatted variable).
	*/
	public static function variable($valueToSanitize, $extraChars = "")
	{
		return self::word($valueToSanitize, $extraChars . "0123456789_");
	}
	
	/****** Sanitize a File Path ******
	* Sanitizes user input for an allowable file path. Only letters, numbers, and underscores are allowed, as well as
	* the forward slashes necessary to identify the path. Parent paths are rejected (forcing an absolute path from
	* the directory you're in, and the extension for any filename can (and should) be enforced.
	*
	* *NOTE* This is the not the safest way to protect a file path when user input is involved. If you need to
	* allow a user to have control over folders, you should use a sanitization method on the folder name itself
	* rather than allow directory slashes to be used. This method should only be used for administrative users that
	* have a reason to access multiple custom directories.
	*
	* This function DOES NOT CARE if the file exists or not - it is simply trying to validate a proper directory and
	* file path. If you want to test if the file exists, you'll want to use the FileHandler class.
	* 
	* If there are characters present that don't belong, it will alert the Security class to warn of potential hacks.
	* Severe warnings may occur if the user attempts to enter parent paths ("../") or uses null bytes.
	* 
	****** How to call the method ******
	* $filepath = "./data/" . Sanitize::filepath($myFilepath);
	* $filepath = "./documents/" . Sanitize::filepath($myTextFile, "txt");
	* $filepath = "./images/" . Sanitize::filepath($myImage, array("png", "jpg", "gif"));
	* 
	****** Parameters ******
	* @string			$valueToSanitize		The directory path that you're going to sanitize.
	* ?string or array	$fileExtensionsAllowed	If set, these are the extensions that can be used.
	* 
	* RETURNS <string>				Returns a sanitized value (as an acceptably formatted filepath).
	* RETURNS <false>				Returns FALSE if there are dangerous ramifications of the value sent.
	*/
	public static function filepath($valueToSanitize, $fileExtensionsAllowed = "")
	{
		// If a null byte is present, we assume it is an obvious hack attempt
		if(strpos($valueToSanitize, "\0") > -1)
		{
			self::warnOfPotentialAttack($valueToSanitize, "Null Byte Attack", 3);
			return false;
		}
		
		// Sanitize any improper characters out of the string
		$valueToSanitize = trim($valueToSanitize);
		$valueToSanitize = str_replace(array(" ", "-"), array("_", "_"), $valueToSanitize);
		$valueToSanitize = self::word($valueToSanitize, "1234567890_/.");
		
		/****** Check For Severe Warnings ******/
		
		// If there is a parent path entry, this is definitely too suspicious to ignore
		if(strpos($valueToSanitize, "../") > -1)
		{
			self::warnOfPotentialAttack($valueToSanitize, "Parent Path Injection", 3);
			return false;
		}
		
		// If there is a parent path entry without the slash, this is both broken and suspicious
		elseif(strpos($valueToSanitize, "..") > -1)
		{
			self::warnOfPotentialAttack($valueToSanitize, "Invalid File Path", 2);
			return false;
		}
		
		/****** Verify the File Extension ******/
		if($fileExtensionsAllowed !== "")
		{
			// Retrieve the last "." present and use that to identify the file extension
			$dotPos = strrpos($valueToSanitize, ".");
			
			$getExtension = substr($valueToSanitize, $dotPos + 1);
			
			// If there are multiple file extensions allowed
			if(is_array($fileExtensionsAllowed) === true)
			{
				// If the file extension isn't one of the allowed types, report a warning and end
				if(!in_array($getExtension, $fileExtensionsAllowed))
				{
					self::warnOfPotentialAttack($valueToSanitize, "Illegal File Extension", 2);
					return false;
				}
			}
			
			// If there is only one file extension allowed
			else
			{
				// If the file extension didn't match what was allowed
				if($getExtension !== $fileExtensionsAllowed)
				{
					self::warnOfPotentialAttack($valueToSanitize, "Illegal File Extension", 2);
					return false;
				}
			}
		}
		
		return $valueToSanitize;
	}
	
	/****** Alert the admins of a potential attack ******
	* This function is called when one of the other Sanitize methods catches a string that had to be sanitized due to
	* disallowed characters being sent. If the Security::warnOfPotentialAttack() method is available, it will send the
	* data there to be determined. Otherwise, the function will end with no effect.
	* 
	* If the warning severity is set above the default (0), then this should trigger a more important notice. The
	* ratings are as follows:
	*
	*	0 = Unsanitized data
	*	1 = Suspicious attack - involves suspicious characters not directly related to the value.
	*	2 = Probable attack - not something that is likely to be an error
	*	3 = Definitely an attack - only a trained penetration tester would do this
	* 
	****** How to call the method ******
	* self::warnOfPotentialAttack($unsafeContent);
	* self::warnOfPotentialAttack($unsafeContent, "Illegal Characters");
	* self::warnOfPotentialAttack($unsafeContent, "Invalid File Path", 2);
	* 
	****** Parameters ******
	* @string	$unsafeContent		The potentially unsafe content that had to be sanitized.
	* ?string	$typeOfWarning		If set, this is the type of warning that is being set.
	* ?int		$warningSeverity	If set above 0, this suggests an important warning.
	* 
	* RETURNS <bool>				Returns TRUE on success, FALSE on failure.
	*/
	private static function warnOfPotentialAttack($unsafeContent, $typeOfWarning = "", $warningSeverity = 0)
	{
		// Run the standard warning method if available
		if(method_exists("Security", "warnOfPotentialAttack"))
		{
			return Security::warnOfPotentialAttack($unsafeContent, $typeOfWarning, $warningSeverity);
		}
		
		return false;
	}
}
