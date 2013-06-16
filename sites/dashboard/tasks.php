<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); } 

// Task Navigation Handler
if(isset($url[1]) && File::exists(SITE_DIR . "/tasks_" . $url[1] . ".php"))
{
	// Load the secondary page (if applicable)
	require_once(SITE_DIR . "/tasks_" . $url[1] . ".php"); exit;
}


