<?php

/****** Set Base Configuration Values ******
* This file prepares certain site-wide configurations.
* 
****** Notes on "config-local.php" ******
* You will need to set your personal configurations (for your localhost environment) in the config-local.php file.
*
****** Notes on "config-production.php" ******
* You will need to set production configurations (for live sites) in the config-production.php file.
*/

// Choose Your Environment
define(
			"DEVELOPMENT", true);		// Set this if you're using a localhost environment.
		//	"TESTING", true);			// Set this to check testing.
		//	"PRODUCTION", true);		// Set this for live servers (where clients can view it).

// Set a Site-Wide Salt between 22 and 42 characters
// NOTE: Only change this value ONCE after installing a new copy. It will affect all passwords created in the meantime.
define("SALT", "Enter an appropriate salt value here.");

// Set the webmaster email
define("WEBMASTER_EMAIL", "webmaster@thisdomain.com");



/****************************************************
******* DO NOT CHANGE ANYTHING BELOW THIS LINE ******
****************************************************/

/****** Important Settings ******/
define("ALLOW_SCRIPT", true);			// Allows included scripts to be accessed.


/****** Prepare the Environment Type ******/

// Make sure DEVELOPMENT, TESTING, and PRODUCTION are either set to true or false
if(!defined("DEVELOPMENT")) { define("DEVELOPMENT", false); }
if(!defined("TESTING")) { define("TESTING", false); }

// Set PRODUCTION to the opposite of DEVELOPMENT
if(!defined("PRODUCTION"))
{
	define("PRODUCTION", (DEVELOPMENT ? false : true));
}

// If you are using a localhost environment, make sure you're sufficiently protected against human error:
if(DEVELOPMENT)
{
	// If the "config-local.php" file doesn't exist, we're probably in a live server:
	if(!is_file("./config-local.php"))
	{
		die("Conflict with development environment. \"config-local.php\" doesn't exist.");
	}
	
	// If a programmer accidentally allowed the "DEVELOPMENT" environment on a live server, end the script.
	if(!in_array($_SERVER["SERVER_ADDR"], array("127.0.0.1", "::1")))
	{
		die("Conflict with localhost address. Development environment only accessible locally.");
	}
	
	// Load our local configuration settings
	require_once("./config-local.php");
}

// If you are running on a production environment, load the production configurations
if(PRODUCTION)
{
	require_once("./config-production.php");
}


/****** Prepare the Auto-Loader ******/
spl_autoload_register(null, false);
spl_autoload_extensions('.php');

// Create our custom Auto-Loader Function
function autoLoader($class)
{
	// Reject class names that aren't valid
	if(!ctype_alnum($class))
	{
		return false;
	}
	
	// Cycle through all relevant directories and load the class (if found)
	$directories = array("classes", "testing");
	
	foreach($directories as $dir)
	{
		$classFile = realpath("./$dir/$class.php");
		
		if(is_file($classFile))
		{
			require_once($classFile);
			return true;
		}
	}
	
	// Check if the class is a plugin
	$pluginFile = realpath("./plugins/" . strtolower($class) . "/class.php");
	
	if(is_file($pluginFile))
	{
		require_once($pluginFile);
		
		// The plugin may require an initialization file:
		$initFile = realpath("./plugins/" . strtolower($class) . "/initialize.php");
		
		if(is_file($initFile))
		{
			require_once($initFile);
		}
		
		return true;
	}
	
	// All checks failed. Return false.
	return false;
}

// Register our custom Auto-Loader
spl_autoload_register('autoLoader');


/****** Session Preparation ******/
session_start();

// Provide Full Clearance if the session identifies you as the webmaster it appropriate
define("WEBMASTER", (isset($_SESSION['webmaster']) ? true : false));


/****** Set Error Handling ******/
if(DEVELOPMENT or (TESTING && WEBMASTER))
{
	// Report all errors
	error_reporting(E_ALL);
	
	// Display errors directly on the screen (verbose)
	ini_set("display_errors", 1);
}
else
{
	// Don't allow errors on production sites
	error_reporting(0);
	ini_set("display_errors", 0);
}