<?php

/****** Script Preparation ******/
define("ALLOW_SCRIPT", true);
define("BASE_DIR", __DIR__);

require_once(BASE_DIR . "/config.php");


/****** Prepare Navigation ******/

if(!is_array($url))
{
	die("A critical error has occured in our site navigation (main index page). Please consult the webmaster.");
}

// If the default string is empty, we will use "home" as our main page.
if(!isset($url[0]) or $url[0] == "")
{
	$url[0] = 'home';
}

// Load the appropriate directory
// Note: The value is sanitized in the File::exists method
if(File::exists(SITE_DIR . '/' . $url[0] . '.php'))
{
	require_once(SITE_DIR . '/' . $url[0] . '.php');
}