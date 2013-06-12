<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Example Plugin ******
* This plugin is just an empty, example plugin to use as a template.
* 
****** Methods Available ******
* $plugin->example->sayHello()				// Returns "Hello World!"
* $plugin->example->add($a, $b)				// Returns the sum of $a and $b
*/

/*
	Don't forget to check out the extension that the plugin has. You can use the extension with the following format:
	
		$plugin->example->extend->method();
	
	Plugins can have any number of extensions, and they're created using the __construct() method.
	
	You can find the example extension at /plugins/example/class_ExampleExtend.php
*/

class ExamplePlugin {


/****** Important Values ******/
	public $extend = null;

	
/****** Initializer ******/
	function __construct()
	{
		// Extend The Example Classes
		require_once(BASE_DIR . "/plugins/example/class_ExampleExtend.php");
		
		$this->extend = new ExampleExtend();
	}
	
	
/****** Say "Hello World" *******/
	public static function sayHello (
	)				/* RETURNS <str> : "Hello World" */
	
	// echo $plugin->example->sayHello();		// Outputs "Hello World"
	{
		return "Hello World";
	}
	
	
/****** Add Two Values Together *******/
	public static function add
	(
		$a		/* <int> The value to add. */,
		$b		/* <int> The second value to add. */
	)			/* RETURNS <int> : The sum of $a and $b. */
	
	// echo $plugin->example->add(2, 5);		// Returns "7"
	{
		return ($a + $b);
	}
}