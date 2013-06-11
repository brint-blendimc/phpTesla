<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Logging Class ******
* This class will log any messages (or errors) that you want to track throughout the page. This is particularly helpful
* for form validation, when error messages (or success messages) are very common.
* 
****** In-Practice Examples ******
* 
* 
****** Methods Available ******
* Log::error($errorMessage);				// Adds a new error to the error list.
* Log::error($tagname, $errorMessage);		// Adds a new error with a specific tag name.
* 
* Log::message($message);					// Adds a new message to the message list.
* Log::message($tagname, $message);			// Adds a new message with a specific tag name.
* 
* Log::hasErrors()				// Returns TRUE if there are errors, FALSE if not
* 
* Log::getMessage($tagname)		// Returns a specific message (based on the tag name)
* Log::getMessages()			// Returns the list of Messages (as an array)
* 
* Log::getError($tagname)		// Returns a specific error (based on the tag name)
* Log::getErrors()				// Returns the list of Errors (as an array)
*/

abstract class Log {

/****** Public Variables ******/
	public static $errorList = array();
	public static $messageList = array();

/****** Set Error ******/
	public static function error
	(
		/* ?? <str> If only one string argument is provided, it adds the error to the error list with an integer. */,
		/* ?? <str> If two string arguments are provided, it adds the error to the error list with a tag name. */
	)	/* RETURNS <bool> : TRUE on success, FALSE if something goes wrong. */
	
	// Log::error("Your password is too short.");	
	// Log::error("No Username", "You need to add a username.");
	{
		$args = func_get_args();
		
		// If one parameter was passed:
		if(count($args) == 1)
		{
			self::$errorList[count(self::$errorList) + 1] = $args[0];
		}
		
		// If two parameters were passed:
		elseif(count($args == 2)
		{
			self::$errorList[$args[0]] = $args[1];
		}
		
		// If an incorrect number of arguments were passed:
		else
		{
			return false;
		}
		
		return true;
	}

	
/****** Set Message ******/
	public static function message
	(
		/* ?? <str> If only one string argument is provided, it adds the message to the list with an integer. */,
		/* ?? <str> If two string arguments are provided, it adds the message to the list with a tag name. */
	)	/* RETURNS <bool> : TRUE on success, FALSE if something goes wrong. */
	
	// Log::message("You have successfully logged in!");	
	// Log::message("Password Updated", "You have updated your password!");
	{
		$args = func_get_args();
		
		// If one parameter was passed:
		if(count($args) == 1)
		{
			self::$messageList[count(self::$messageList) + 1] = $args[0];
		}
		
		// If two parameters were passed:
		elseif(count($args == 2)
		{
			self::$messageList[$args[0]] = $args[1];
		}
		
		// If an incorrect number of arguments were passed:
		else
		{
			return false;
		}
		
		return true;
	}
	
	
/****** Check if there are Errors ******/
	public static function hasErrors (
	)		/* RETURNS <bool> : TRUE if there are errors, FALSE if not. */
	
	// if(!Log::hasErrors()) { echo "The form has been submitted!"; }
	{
		if(self::$errorList == array())
		{
			return false;
		}
		
		return true;
	}
	
	
/****** Get a specific message, if present ******/
	public static function getMessage
	(
		$tagName		/* <str> The name of the message to retrieve. */
	)					/* RETURNS <str> : Empty string if the message never occurred, otherwise the message. */
	
	// echo Log::getMessage('form_valid');
	{
		if(isset(self::$messageList[$tagName]))
		{
			return self::$messageList[$tagName];
		}
		
		return "";
	}
	
	
/****** Get Errors ******/
	public static function getMessages (
	)		/* RETURNS <array> : Array of all the errors that have been logged. */
	
	// $messages = Log::getMessages();
	// foreach($messages as $message) {
	// echo $message; }
	{
		return self::$messageList;
	}
	
	
/****** Get a specific error, if present ******/
	public static function getError
	(
		$tagName		/* <str> The name of the error to retrieve. */
	)					/* RETURNS <str> : Empty string if the error never occurred, otherwise the error. */
	
	// echo '<input type="text" name="username" /> ' . Log::getError('username_valid');
	{
		if(isset(self::$errorList[$tagName]))
		{
			return self::$errorList[$tagName];
		}
		
		return "";
	}
	
	
/****** Get Errors ******/
	public static function getErrors (
	)		/* RETURNS <array> : Array of all the errors that have been logged. */
	
	// $errors = Log::getMessages();
	// foreach($errors as $error) {
	// echo $error; }
	{
		return self::$errorList;
	}
	
}
