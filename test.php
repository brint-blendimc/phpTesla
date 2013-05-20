<?php

/****** Prepare Variables ******/
define("IS_SAFE", true);			// Prepare the site-wide script-access variable.

/****** Script Includes ******/
require_once("./includes/autoloader.php");



// Determine the types of tests to run
$testing = new Testing;
$testing->addTest("Sanitize");

$messages = array();

// Run the appropriate tests
foreach($testing->testList as $testClass => $methodList)
{
	// Initialize the Testing Class
	$initTestClass = "Test" . $testClass;
	
	if(class_exists($initTestClass))
	{
		$nextTest = new $initTestClass();
		
		// Test all methods in the class if none were specified
		if($methodList === array())
		{
			$methods = get_class_methods($initTestClass);
			$parentMethods = get_class_methods("Testing");
			
			foreach($methods as $method)
			{
				if(!in_array($method, $parentMethods))
				{
					$messages[$testClass . "::" . $method] = $nextTest->$method();
				}
			}
		}
		
		// If certain methods were specified, only run those methods
		else
		{
			foreach($methodList as $method)
			{
				$messages[$testClass . "::" . $method] = $nextTest->$method();
			}
		}
	}
}

// Display the messages recovered from Testing
foreach($messages as $message)
{
	echo '
	<div style="background:' . ($message['success'] === true ? "#66FF66" : "#FF5555") . '">' . $message['class'] . '::' . $message['method'] . '() &gt;&gt; ' . $message['summary'] . '</div>';
}