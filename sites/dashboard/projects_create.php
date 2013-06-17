<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); } 

// Make sure the user has clearance to access this page.
if(!$plugin->users->clearance->hasClearance("Task Management"))
{
	header("Location: ./"); exit;
}

/****** Prepare Values ******/
if(!isset($data->title)) { $data->title = ""; }

/****** Process New Project ******/
if($plugin->tasks->controller->createProject($data) === true)
{
	// Get the new project data
	$projectID = Database::getLastID();
	$projectData = TaskProject::getData($projectID);
	
	if(isset($projectData['id']))
	{
		// Redirect to the new Project's page:
		$title = str_replace(" ", "-", strtolower($projectData['title']));
		header("Location: " . BASE_URL . "/projects/display/" . $title); exit;
	}
}

// Display the Add Project Page
require_once(SITE_DIR . "/includes/header.php");

?>

<!-- Add Project Form -->

<?=Note::display();?>
<form class="form" action="<?=$_SERVER['REQUEST_URI'];?>" method="post">
	
	<div class="form-input title">
		<label for="title">Project Title</label>
		<input id="title" type="text" name="title" value="<?=$data->title;?>" maxlength="22" />
	</div>
	
	<div class="form-input submit">
		<label for="submit">Submit</label>
		<input type="submit" name="submit" value="Submit" />
	</div>
	
</form>

<?php require_once(SITE_DIR . "/includes/footer.php");
