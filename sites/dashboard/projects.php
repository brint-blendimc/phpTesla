<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); } 

// Task Navigation Handler
if(isset($url[1]) && File::exists(SITE_DIR . "/projects_" . $url[1] . ".php"))
{
	// Load the secondary page (if applicable)
	require_once(SITE_DIR . "/projects_" . $url[1] . ".php"); exit;
}

// Make sure the user has clearance to access this page.
if(!$plugin->users->clearance->hasClearance("Task Management"))
{
	header("Location: ./"); exit;
}

// Display Header
require_once(SITE_DIR . "/includes/header.php");

// List Projects
$projectList = $plugin->tasks->project->getList();

foreach($projectList as $project)
{
	echo '<br /><a href="' . BASE_URL . '/projects/display/' . str_replace(" ", "-", strtolower($project['title'])) . '">' . $project['title'] . '</a>';
}

// Display Footer
require_once(SITE_DIR . "/includes/footer.php");