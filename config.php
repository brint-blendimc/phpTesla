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
define("SITE_SALT", "Enter an appropriate salt value here.");

// Set a unique 4 to 10 character keycode (alphanumeric) to prevent code overlap on databases & shared servers
// For example, you don't want sessions to transfer between multiple sites on a server (e.g. $_SESSION['user'])
// This key will allow each value to be unique (e.g. $_SESSION['siteCode_user'] vs. $_SESSION['otherSite_user'])
define("SITE_CODE", "siteCode");

// Set the webmaster email
define("WEBMASTER_EMAIL", "webmaster@thisdomain.com");

// Set the directory to your site relative to your BASE DIRECTORY.
// Start with a "/", but do not end with one. (e.g. "/mySite" or "/sites/DogsInHats");
$siteDirectory = "/sites/dashboard";

/****************************************************
******* DO NOT CHANGE ANYTHING BELOW THIS LINE ******
****************************************************/

/****** Important Settings ******/
define("ALLOW_SCRIPT", true);			// Allows included scripts to be accessed.

define("USER_SESSION", SITE_CODE . '_user');	// Allows $_SESSION[USER_SESSION] to track each user.

/****** Prepare the Environment Type ******/

// Make sure DEVELOPMENT, TESTING, and PRODUCTION are either set to true or false
if(!defined("DEVELOPMENT")) { define("DEVELOPMENT", false); }
if(!defined("TESTING")) { define("TESTING", false); }

// Set PRODUCTION to the opposite of DEVELOPMENT
if(!defined("PRODUCTION"))
{
	define("PRODUCTION", (DEVELOPMENT ? false : true));
}

// If you are running on a production environment, load the production configurations
if(PRODUCTION)
{
	require_once("./config-production.php");
}

// If you are using a localhost environment, make sure you're sufficiently protected against human error:
else if(DEVELOPMENT)
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
	
	// Cycle through the class directory and load the class (if located)
	$classFile = realpath("./classes/$class.php");
	
	if(is_file($classFile))
	{
		require_once($classFile);
		return true;
	}
	
	// If you're in development mode, check if testing classes were loaded;
	if(DEVELOPMENT)
	{
		$classFile = realpath("./testing/$class.php");
		
		if(is_file($classFile))
		{
			require_once($classFile);
			return true;
		}
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

/****** Set up Configurations & Data ******/

// This will automatically set up $data->url[]
// Arguments passed from $_POST will be applied, such as $_POST['hello'] becoming $data->hello
$data = new Data();


/****** Additional Settings ******/
define("SITE_DIR", BASE_DIR . $siteDirectory);

$url = Data::getURLSegments();

/****** Prepare the Database Connection ******/
Database::initialize($database['name'], $database['user'], $database['password'], $database['host'], $database['type']);
