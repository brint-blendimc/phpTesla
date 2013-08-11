<?php

/************************
****** AJAX Loader ******
*************************
* This script is used to load our AJAX applications quickly (through GET). AJAX must NOT be loaded any other way.
* Only through this script. This is to ensure we have properly secured every AJAX app being processed.
* 
* This script expects the following variables to be sent to it:
* 
* $_POST['load']			// This means we will load ./assets/ajax/$_POST['load'].php
* 
* $_POST[$values] contains the rest of the data that is sent to the AJAX processor.
*		- Each of these $values is determined through the loadAjax() script
* 
* As an example of how this loader works:
* 
* If $_POST['load'] equals "allowTimeChange", then the script being loaded is:
* 		./assets/ajax/allowTimeChange.php
*/

// Make sure that a proper function was chosen to be loaded
if(!isset($_POST['load']))
{
	die("No function is being loaded.");
}

// Load the System Configurations
define("SITE_DIR", str_replace('\\', '/', __DIR__));

require_once("./config/config.php");
require_once("../../index.php");

// Now lets load the AJAX script that was being called:
$_POST['load'] = Sanitize::variable($_POST['load']);

if(file_exists(SITE_DIR . "/assets/ajax/" . $_POST['load'] . ".php"))
{
	// Set an AJAX constant so that we can supress unnecessary headers and files
	define("AJAX_LOADED", true);
	
	require_once(SITE_DIR . "/assets/ajax/" . $_POST['load'] . ".php");
}
else
{
	die("Script doesn't exist or has an invalid name.");
}
