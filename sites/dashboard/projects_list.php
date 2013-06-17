<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); } 

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
	echo '<br /><a href="' . BASE_URL . '/tasks/projects/' . ($project['id'] + 0) . '">' . $project['title'] . '</a>';
}

// Display Footer
require_once(SITE_DIR . "/includes/footer.php");