<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); } 

// Make sure the user has clearance to access this page.
if(!$plugin->users->clearance->hasClearance("Task Management"))
{
	header("Location: ./"); exit;
}

// Display Header
require_once(SITE_DIR . "/includes/header.php");

// If you have a third URL segment, load the specific project
if(isset($url[2]))
{
	// Sanitize the URL again (just in case)
	$projectTag = Sanitize::variable($url[2], "-");
	$projectTag = str_replace("-", " ", $projectTag);
	
	// Test to see if the project exists and can load properly
	$projectData = $plugin->tasks->project->getData($projectTag);
}

echo "You are viewing: " . $projectData['title'];

// Display Footer
require_once(SITE_DIR . "/includes/footer.php");
