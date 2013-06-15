<?php

/****** Script Preparation ******/
require_once("./config.php");


/****** Prepare Navigation ******/

// If the default string is empty, we will use "index" as our main page.
if(!isset($url[0]))
{
	$url[0] = 'index';
}

// Load the appropriate directory
if(is_file(SITE_DIR . '/' . $url[0] . '.php'))
{
	require_once(SITE_DIR . '/' . $url[0] . '.php');
}