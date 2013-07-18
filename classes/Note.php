<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Note Class ******
* This class will track any messages (or errors) that you want to keep throughout the page. This is particularly
* helpful for form validation, when error messages (or success messages) are very common.
* 
****** In-Practice Examples ******
* 
* 
****** Methods Available ******
* Note::error($errorMessage);				// Adds a new error to the error list.
* Note::error($tagname, $errorMessage);		// Adds a new error with a specific tag name.
* 
* Note::message($message);					// Adds a new message to the message list.
* Note::message($tagname, $message);		// Adds a new message with a specific tag name.
* 
* Note::hasErrors()				// Returns TRUE if there are errors, FALSE if not
* 
* Note::getMessage($tagname)	// Returns a specific message (based on the tag name)
* Note::getMessages()			// Returns the list of Messages (as an array)
* 
* Note::getError($tagname)		// Returns a specific error (based on the tag name)
* Note::getErrors()				// Returns the list of Errors (as an array)
*
* Note::display()				// Displays the notes that were created during the script.
*/

abstract class Note {

/****** Public Variables ******/
	public static $errorList = array();
	public static $messageList = array();

/****** Set Error ******/
	public static function error
	(
		/* ?? <str> If only one string argument is provided, it adds the error to the error list with an integer. */
		/* ?? <str> If two string arguments are provided, it adds the error to the error list with a tag name. */
	)	/* RETURNS <bool> : TRUE on success, FALSE if something goes wrong. */
	
	// Note::error("Your password is too short.");	
	// Note::error("No Username", "You need to add a username.");
	{
		$args = func_get_args();
		
		// If one parameter was passed:
		if(count($args) == 1)
		{
			self::$errorList[count(self::$errorList) + 1] = $args[0];
		}
		
		// If two parameters were passed:
		elseif(count($args == 2))
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
		/* ?? <str> If only one string argument is provided, it adds the message to the list with an integer. */
		/* ?? <str> If two string arguments are provided, it adds the message to the list with a tag name. */
	)	/* RETURNS <bool> : TRUE on success, FALSE if something goes wrong. */
	
	// Note::message("You have successfully logged in!");	
	// Note::message("Password Updated", "You have updated your password!");
	{
		$args = func_get_args();
		
		// If one parameter was passed:
		if(count($args) == 1)
		{
			self::$messageList[count(self::$messageList) + 1] = $args[0];
		}
		
		// If two parameters were passed:
		elseif(count($args == 2))
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
	
	// if(!Note::hasErrors()) { echo "The form has been submitted!"; }
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
	
	// echo Note::getMessage('form_valid');
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
	
	// $messages = Note::getMessages();
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
	
	// echo '<input type="text" name="username" /> ' . Note::getError('username_valid');
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
	
	// $errors = Note::getMessages();
	// foreach($errors as $error) {
	// echo $error; }
	{
		return self::$errorList;
	}
	
	
/****** Display Relevant Notes ******/
	public static function display (
	)		/* RETURNS <str> : HTML with divs that provide a simple display of relevant note data. */
	
	// Note::display()
	{
		$html = "";
		
		// If there are errors, display those:
		if(self::$errorList !== array())
		{
			$quickList = self::$errorList;
			
			$html = '
			<div class="alert-box error-box">';
			
			foreach($quickList as $key => $note)
			{
				$html .= '
				<div id="error' . $key . '" class="error">' . $note . '</div>';
			}
			
			$html .= '
			</div>';
		}
		
		// If there are messages (but no errors), display those:
		else if(self::$messageList !== array())
		{
			$quickList = self::$messageList;
			
			$html = '
			<div class="alert-box message-box">';
			
			foreach($quickList as $key => $note)
			{
				$html .= '
				<div id="message' . $key . '" class="message">' . $note . '</div>';
			}
			
			$html .= '
			</div>';
		}
		
		return $html;
	}
}
