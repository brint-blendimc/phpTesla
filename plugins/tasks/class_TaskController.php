<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }
		
/****** Task Controller Class ******
* This class allows you to process common forms.
* 
****** Methods Available ******
* $plugin->tasks->
*	controller->simpleValidate()				// Simple validation of values that may be used in these forms.
* 	controller->createTask($postData)			// The form processer for creating a new task.
*/

class TaskController {


/****** Create a Task *******/
	public static function createTask
	(
		$postData				/* <int> The POST data that's sent to the form */
	)							/* RETURNS <bool> : TRUE on success, FALSE if something went wrong. */
	
	// $plugin->tasks->controller->createTask($_POST);
	{
		// Form Submission for Creating a New Task
		if(isset($postData['submit']))
		{
			// If the form was valid, create the task
			Task::create($postData['groupID'], $postData['assignTo'], $postData['summary']);
		}
	}
	
	
/****** Create a Project *******/
	public static function createProject
	(
		$data					/* <class> The POST data that's sent to the form. */,
		$successRedirect = ""	/* <str> The location to redirect to if you successfully create a project. */
	)							/* RETURNS <bool> or <redirect> : TRUE or REDIRECT on success, FALSE otherwise. */
	
	// $plugin->tasks->controller->createProject($data);
	{
		// Form Submission for Creating a Project
		if(isset($data->submit))
		{
			// Validate Title
			$data->title = Sanitize::variable($data->title, " ");
			
			if(!isSanitized::length($data->title, 22))
			{
				Note::error("Title", "Title can only be up to 22 characters.");
			}
			
			else if(strlen($data->title) < 3)
			{
				Note::error("Title", "Title cannot be less than 3 characters.");
			}
			
			else if(is_numeric($data->title))
			{
				Note::error("Title", "Title cannot be just a number.");
			}
			
			// If the form was valid, create the project
			if(!Note::hasErrors())
			{
				TaskProject::create($data->title);
				
				// If login was successful and you're redirecting to a specific page:
				if($successRedirect !== "")
				{
					header("Location: " . $successRedirect); exit;
				}
				
				return true;
			}
			
			return false;
		}
	}
}

