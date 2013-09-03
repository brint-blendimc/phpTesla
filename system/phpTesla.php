<?php

/***********************************************
******* DO NOT EDIT ANYTHING IN THIS FILE ******
***********************************************/

/****** Important Settings ******/
define("ALLOW_SCRIPT", true);					// Allows other files to be accessed once this script has run.

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
	
	// Cycle through site-specific classes and load the class if located
	$classFile = realpath(APP_PATH . "/classes/$class.php");
	
	if(is_file($classFile))
	{
		require_once($classFile);
		return true;
	}
	
	// Cycle through the system-wide classes if the application classes were not detected
	$classFile = realpath(SYS_PATH . "/classes/$class.php");
	
	if(is_file($classFile))
	{
		require_once($classFile);
		return true;
	}
	
	// All checks failed. Return false.
	return false;
}

// Register our custom Auto-Loader
spl_autoload_register('autoLoader');

/****** Important Settings ******/
$config = new Config();

define("USER_SESSION", SITE_CODE . '_user');	// Allows $_SESSION[USER_SESSION] to track each user.

/****** Prepare the Environment Type ******/

// Make sure LOCAL, DEVELOPMENT, PRODUCTION, and TESTING are either set to true or false
if(!defined("LOCAL")) { define("LOCAL", false); }
if(!defined("DEVELOPMENT")) { define("DEVELOPMENT", false); }
if(!defined("PRODUCTION")) { define("PRODUCTION", false); }
if(!defined("TESTING")) { define("TESTING", false); }

// If you are running on a production environment, load the production configurations
if(PRODUCTION)
{
	require_once(APP_PATH . "/config/environment-production.php");
}

// If you are using a localhost environment, try to protect against human error:
else if(LOCAL)
{
	// If "config/environment-local.php" doesn't exist, we're probably in a live server:
	if(!is_file(APP_PATH . "/config/environment-local.php"))
	{
		die("Conflict with local environment. \"environment-local.php\" doesn't exist.");
	}
	
	// If a programmer accidentally allowed the "LOCAL" environment on a live server, end the script.
	if(!in_array($_SERVER["SERVER_ADDR"], array("127.0.0.1", "::1")))
	{
		die("Conflict with localhost address. Local environment only accessible locally.");
	}
	
	// Load our local configuration settings
	require_once(APP_PATH . "/config/environment-local.php");
}

// If you are using a development environment, try to protect against human error:
else if(DEVELOPMENT)
{
	// If "config/environment-development.php" doesn't exist, we're probably in a live server:
	if(!is_file(APP_PATH . "/config/environment-development.php"))
	{
		die("Conflict with development environment. \"environment-development.php\" doesn't exist.");
	}
	
	// Load our development configuration settings
	require_once(APP_PATH . "/config/environment-development.php");
}
else
{
	die("You do not have an environment set.");
}

/****** Session Preparation ******/
session_start();

/****** Set Error Handling ******/
if(DEVELOPMENT or LOCAL OR TESTING)
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


/****** Process Security Functions ******/
Security::fingerprint();


/****** Set up System Configurations & Data ******/

// This will automatically set up $data->url[]
// Arguments passed from $_POST will be applied, such as $_POST['hello'] becoming $data->hello
$data = new Data();

// Get URL Segments
$url = Data::getURLSegments();

/****** Prepare the Database Connection ******/
Database::initialize(
	$config->database['name'],
	$config->database['user'],
	$config->database['pass'],
	$config->database['host'],
	$config->database['type']
);
