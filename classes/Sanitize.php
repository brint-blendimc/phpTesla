<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

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
* Sanitize::number($userInput);				// Whitelist allows: 0123456789
* Sanitize::number($userInput, "abcdef");	// Whitelist allows: 0123456789abcdef
* 
****** In-Practice Examples ******
* 1. User goes to the URL: page.php?myInput=YES;*!_123.01
* 
* $value = $_GET['myInput'];									// Returns "YES;*!_123.01"
* $value = Sanitize::word($_GET['myInput']);					// Returns "YES"
* $value = Sanitize::number($_GET['myInput']);					// Returns "12301"
* $value = Sanitize::number($_GET['myInput'], "_.");			// Returns "_123.01"
* $value = Sanitize::whitelist($_GET['myInput'], "ABCDE");		// Returns "E"
* 
****** Methods Available ******
* Sanitize::whitelist($userInput, $charsAllowed);		// Whitelists the exact characters you provide.
* 
* Sanitize::word($userInput, $extraChars = "");			// Allows letters
* Sanitize::variable($userInput, $extraChars = "");		// Allows letters, numbers, underscore
* Sanitize::safeword($userInput, $extraChars = "");		// Allows letters, numbers, space, and _-.,:;|
* Sanitize::punctuation($userInput, $extraChars = "");	// Allows safeword options, plus punctuation and some symbols
* Sanitize::text($userInput, $extraChars = "");			// Allows punctuation options, plus brackets and extra symbols
* 
* Sanitize::number($userInput, $maxRange = 0);			// If 2nd parameter is a number, applies a max range
* Sanitize::number($userInput, $minRange = 0, $maxRange = 0);	// Use this format for min and max range
*
* Sanitize::length($userInput, $maxLength);				// Strips content that's too long.
* 
* Sanitize::directory($userInput);						// Sanitizes an allowable directory path (including slashes)
* Sanitize::filepath($userInput);						// Sanitizes an allowable file path (including slashes)
* Sanitize::filename($userInput);						// Sanitizes an allowable filename (with proper extensions)
* Sanitize::email($userInput);							// Sanitizes a proper email
* Sanitize::url($userInput);							// Sanitizes an allowable URL
* 
* Sanitize::warnOfPotentialAttack($unsafeString);		// Alert the admins of an unsafe string that was used.
*/

abstract class Sanitize {

/****** Sanitize: Whitelist ******
Sanitizes user input so that only the characters that you want to be present are allowed. It will not care about
what position any of the characters are in - you can use regular expressions whenever that is required.

If there are characters sanitized (i.e. characters that didn't belong were stripped from the input), this method
will attempt to create a warning for the admins of a potential hack attempt. */
	public static function whitelist
	(
		$valueToSanitize		/* <str> The value you're going to sanitize. */,
		$charsAllowed			/* <str> A list of specific characters to add to the whitelist. */
	)							/* RETURNS <string> : Returns a sanitized value (as an acceptably formatted word). */
	
	// $string = Sanitize::whitelist($string, "abcd");	// The string will only allow the characters: a, b, c, and d
	{
		/****** Prepare Variables *****/
		$originalString = $valueToSanitize;
		$illegalChars = 0;
		$totalLength = strlen($valueToSanitize);
		
		// Cycle through each letter in the word to sanitize and check if there is a character that shouldn't be there.
		for($i = 0;$i < $totalLength;$i++)
		{
			// If something shouldn't be there, strip it out.
			if(strpos($charsAllowed, $valueToSanitize[$i - $illegalChars]) === false)
			{
				$valueToSanitize = substr_replace($valueToSanitize, "", $i - $illegalChars, 1);
				$illegalChars++;
			}
		}
		
		// Send a warning if the user input had to be sanitized
		if($originalString != $valueToSanitize)
		{
			// If we've encountered a level 1 warning or higher, check if the characters used are likely dangerous
			if($illegalChars >= 3)
			{
				// Increase the warning level if there is an abundance of illegal characters
				$warningLevel = ($illegalChars >= 5 ? 1 : 0);

				// Prepare variables for testing the offending characters
				$offensiveCount = 0;
				$offensiveChars = "`\\/%()?&'\';<>=+-" . chr(0);

				// Every time a potentially dangerous character is identified, increase the chance of warning
				for($i = 0;$i < strlen($originalString);$i++)
				{
					// If something shouldn't be there, strip it out.
					if(strpos($offensiveChars, $originalString[$i]) === false && strpos($charsAllowed, $originalString[$i]) === false)
					{
						$offensiveCount++;
					}
				}
				
				// Increase the warning level if multiple dangerous characters are found
				if($offensiveCount >= 2)
				{
					$warningLevel = 1;
				}
				elseif($offensiveCount >= 4)
				{
					$warningLevel = 2;
				}
			}
			
			// Prepare a warning of potential abuse
			self::warnOfPotentialAttack($originalString, "Illegal Characters", (isset($warningLevel) ? $warningLevel : 0));
		}
		
		return $valueToSanitize;
	}
/****** Sanitize Word ******
Sanitizes user input so that only letters are allowed. Capital letters and lower case letters are both allowed.
If there are characters present that don't belong, it will attempt to warn of potential hacks. */
	public static function word
	(
		$valueToSanitize		/* <str> The value you're going to sanitize. */,
		$extraChars = ""		/* <str> A list of specific characters to add to the whitelist. */
	)							/* RETURNS <str> : The sanitized value that results after sanitizing. */
	
	// $word = Sanitize::word($word);			// Allows letters
	// $word = Sanitize::word($word, "12@");	// Allows letters, the digits "1" and "2", and the symbol "@"
	{
		return self::whitelist($valueToSanitize, "eariotnslcudpmhgbfywkvEARIOTNSLCUDPMHGBFYWKV" . $extraChars . "xzjqXZJQ");
	}

/****** Sanitize a Variable ******
Sanitizes user input so that only letters, numbers, and underscores are allowed.
If there are characters present that don't belong, it will attempt to warn of potential hacks. */
	public static function variable
	(
		$valueToSanitize		/* <str> The value you're going to sanitize. */,
		$extraChars = ""		/* <str> A list of specific characters to add to the whitelist. */
	)							/* RETURNS <str> : The sanitized value that results after sanitizing. */
	
	// $str = Sanitize::variable($variable);			// Allows letters, numbers, underscores
	// $str = Sanitize::variable($variable, "#!");		// Allows letters, numbers, undescores, "#", and "!"
	{
		return self::whitelist($valueToSanitize, "eariotnslcudpmhgbfywkv0123456789_EARIOTNSLCUDPMHGBFYWKV" . $extraChars . "xzjqXZJQ");
	}

/****** Sanitize a "Safeword" ******
Sanitizes user input so that only letters, numbers, spaces, underscores, dashes, periods, and separators are allowed.
A "safeword" is basically a title (like a page title) or simple header that's meant to be primarily text. */
	public static function safeword
	(
		$valueToSanitize		/* <str> The value you're going to sanitize. */,
		$extraChars = ""		/* <str> The list of specific characters to add to the whitelist. */
	)							/* RETURNS <str> : The sanitized value that results after sanitizing. */
	
	// $str = Sanitize::safeword($valueToSanitize);			// Alphanumeric + " _-.,:;|" is allowed.
	// $str = Sanitize::safeword($valueToSanitize, "?!");	// Adds ":?" and "!" to the allowed whitelist.
	{
		return self::whitelist($valueToSanitize, "eariotnslcudpmhgbfywkv0123456789_- .,:;|EARIOTNSLCUDPMHGBFYWKV" . $extraChars . "xzjqXZJQ");
	}

	
/****** Sanitize Text with Limitations ******
Sanitizes user input so that some text found in common paragraphs can be used (including whitespace and punctuation).
Note: Consider what this is being used for before fully implementing it. Additional sanitization may be required. */
	public static function punctuation
	(
		$valueToSanitize		/* <str> The value you're going to sanitize. */,
		$extraChars = ""		/* <str> The list of specific characters to add to the whitelist. */
	)							/* RETURNS <str> : The sanitized value that results after sanitizing. */
	
	// $text = Sanitize::text($valueToSanitize);		// Common text formats and punctuation allowed.
	// $text = Sanitize::text($valueToSanitize, "<>");	// Allows typical text / punctuation, plus "<" and ">"
	{
		return self::safeword($valueToSanitize, "'\"!?@#$%^&*+=" . chr(9) . chr(10) . $extraChars);
	}
	
/****** Sanitize Text ******
Sanitizes user input so that the text found in complicated paragraphs can be used (including whitespace and symbols).
Note: this allows a considerable amount of symbols to be used - it's best to consider the ramifications of using this
before fully implementing it. Additional sanitization may likely be required. */
	public static function text
	(
		$valueToSanitize		/* <str> The value you're going to sanitize. */,
		$extraChars = ""		/* <str> The list of specific characters to add to the whitelist. */
	)							/* RETURNS <str> : The sanitized value that results after sanitizing. */
	
	// $text = Sanitize::text($valueToSanitize);		// Common text formats and punctuation allowed.
	// $text = Sanitize::text($valueToSanitize, "<>");	// Allows typical text / punctuation, plus "<" and ">"
	{
		return self::safeword($valueToSanitize, "'\"!?@#$%^&*()[]+={}" . chr(9) . chr(10) . $extraChars);
	}
	
	
/****** Sanitize Number ******/
	public static function number
	(
		$numberToSanitize		/* <str> The number you're going to sanitize. */
								/* <???> Additional parameters can be passed to adjust the range allowed. */
	)							/* RETURNS <str> : The sanitized value that results after sanitizing. */
	
	// Sanitize::number(1000, 500);			// Allows a range of 0 to 500, so it would return "500"
	// Sanitize::number(1000, 0, 2000);		// Allows a range of 0 to 2000.
	{
		$number = $numberToSanitize + 0;
		
		$args = func_get_args();
		
		// If there was a range of 0 to Y provided, force the number to be within range.
		if(count($args) == 1)
		{
			if($number > $args[0])
			{
				$number = $args[0];
			}
			
			elseif($number < 0)
			{
				$number = 0;
			}
		}
		
		// If there was a range of X to Y provided, force the number to be within range.
		if(count($args) == 2)
		{
			if($number > $args[1])
			{
				$number = $args[1];
			}
			
			elseif($number < $args[0])
			{
				$number = $args[0];
			}
		}
		
		return $number;
	}
	
	
/****** Sanitize Length ******/
	public static function length
	(
		$valueToShorten		/* <str> The number you're going to sanitize. */,
		$maxLength			/* <int> The maximum length of the string; shorten if it exceeds this. */
	)						/* RETURNS <str> : The sanitized value that results after sanitizing. */
	
	// $_POST['username'] = Sanitize::length($_POST['username'], 32);		// Limits the username to 32 characters.
	{
		return substr($valueToShorten, 0, $maxLength);
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
	* file path. If you want to test if the file exists, you'll want to use the File class.
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
		$valueToSanitize = str_replace(array(" "), array("_"), $valueToSanitize);
		$valueToSanitize = self::variable($valueToSanitize, ":/.-");

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
			
			$getExtension = substr($valueToSanitize, $dotPos);
			
			// If there are multiple file extensions allowed
			if(is_array($fileExtensionsAllowed) === true)
			{
				// If the file extension isn't one of the allowed types, report a warning and end
				// A second check is made in case the programmer added a "." to the extensions allowed list
				if(!in_array($getExtension, $fileExtensionsAllowed) && !in_array(str_replace(".", "", $getExtension), $fileExtensionsAllowed))
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