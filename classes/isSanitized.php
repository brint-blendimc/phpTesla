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

abstract class isSanitized
{
	public static function __callStatic($name, $arguments)
	{
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
}