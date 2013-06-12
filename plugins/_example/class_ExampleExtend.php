<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Example Plugin Extension ******
* This plugin extension is just an empty, example plugin extension to use as a template.
* 
****** Methods Available ******
* $plugin->example->extend->sayGoodbye()	// Returns "Goodbye World!"
*/

class ExampleExtend {

/****** Say "Goodbye World" *******/
	public static function sayGoodbye (
	)				/* RETURNS <str> : "Goodbye World" */
	
	// echo $plugin->example->extend->sayGoodbye();		// Outputs "Goodbye World"
	{
		return "Goodbye World";
	}
	
}
