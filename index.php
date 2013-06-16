<?php

/****** Script Preparation ******/
require_once("./config.php");


/****** Prepare Navigation ******/

if(!is_array($url))
{
	die("A critical error has occured in our site navigation (main index page). Please consult the webmaster.");
}

// If the default string is empty, we will use "index" as our main page.
if(!isset($url[0]) or $url[0] == "")
{
	$url[0] = 'index';
}

// Load the appropriate directory
if(File::exists(SITE_DIR . '/' . $url[0] . '.php'))
{
	require_once(SITE_DIR . '/' . $url[0] . '.php');
}
