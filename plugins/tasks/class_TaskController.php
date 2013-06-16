<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }
		
/****** Task Controller Class ******
* This class allows you to process common forms.
* 
****** Methods Available ******
* $plugin->tasks->
*	controller->simpleValidate()					// Simple validation of values that may be used in these forms.
* 	controller->createNewTask($postData)			// The form processer for creating a new task.
*/

class TaskController {


/****** Run a test on the Form Values passed ******/
	public static function simpleValidate
	(
		$postData		/* <array> The POST or GET data that you're passing to validate. */,
						/* <???> Additional arguments that you're passing. */
	)					/* RETURNS <bool> : TRUE after completion, or FALSE if returning early. */
	
	// $postData = TaskController::simpleValidate($postData, 'groupID', 'assignToID', 'summary', 'description');
	{
		// Get all of the arguments passed (e.g. "userID", "timeDue", etc);
		$args = func_get_args();
		
		// Cycle through the arguments
		for($i = 1;$i < count($args);$i++)
		{
			$key = $args[$i];
			
			// Make sure the parameter is set
			if(!isset($postData[$key]))
			{
				Note::error("Missing " . $key, "Some of the form's input values are missing.");
				
				return false;
			}
			
			switch($key)
			{
				// Task Groups
				case "groupID":
					
					// Make sure the Task ID is a number
					if(!isSanitized::number($postData[$key]))
					{
						Note::error("Task Group Invalid", "Task Group is using an invalid syntax.");
					}
					
					// Confirm that the Group Exists
					elseif(TaskGroup::exists($postData[$key]) === false)
					{
						Note::error("Task Group Doesn't Exist", "Task Group does not exist.");
					}
					
				break;
				
				// User Assigned The Task
				case "assignToID":
					
					// Make sure that the User ID is a number
					if(!isSanitized::number($postData[$key]))
					{
						Note::error("User Invalid", "User provided is using an invalid syntax.");
					}
					
					// Confirm that the User Exists
					if(UsersPlugin::exists($postData[$key]) === false)
					{
						Note::error("User Doesn't Exist", "User doesn't exist.");
					}
					
				break;
				
				// Summary of the Task
				case "summary":
					
					// Make sure the Summary is Sanitized
					$postData[$key] = Sanitize::safeword($postData[$key]);
					
					// Create an Error if the length is too short
					if(strlen($postData[$key]) < 8)
					{
						Note::error("Summary Too Short", "Task Summary is too short.");
					}
					
				break;
				
				// Description of the Task
				case "description":
					
					// Make sure the Description is Sanitized
					$postData[$key] = Sanitize::text($postData[$key]);
					
				break;
				
				// Priority Level
				case "priorityLevel":
					
					// Make sure that the Priority Level is a number and within range
					if(!isSanitized::number($postData[$key], 9))
					{
						Note::error("Invalid Priority Level", "Priority Level is invalid.");
					}
					
				break;
				
				// Time Estimate
				case "timeEstimate":
				
					// Make sure the Time Estimate is a number (and less than five years in seconds)
					if(!isSanitized::number($postData[$key], (60 * 60 * 24 * 365 * 5)))
					{
						Note::error("Invalid Time Estimate", "Time Estimate is invalid.");
					}
					
				break;
				
				// Time Due
				case "timeEstimate":
				
					// Make sure the Due Date is a number
					if(!isSanitized::number($postData[$key]))
					{
						Note::error("Invalid Due Date", "Due Date is invalid.");
					}
					
					// Make sure the Due Date is within five years of the current date.
					elseif($postData[$key] < time() - (60 * 60 * 24 * 365 * 5) || $postData[$key] > time() + (60 * 60 * 24 * 365 * 5))
					{
						Note::error("Bad Due Date", "Due Date is too distant in time to be valid.");
					}
					
				break;
			}
		}
		
		return true;
	}
	

/****** Create New Task *******/
	public static function createNewTask
	(
		$postData				/* <int> The POST data that's sent to the form */
	)							/* RETURNS <bool> : TRUE on success, FALSE if something went wrong. */
	
	// $plugin->tasks->controller->createNewTask($_POST);
	{
		// Form Submission for Creating a New Task
		if(isset($postData['submit']))
		{
			// Run Simple Validations
			$postData = self::simpleValidate($postData, 'groupID', 'assignToID', 'summary', 'description', 'priorityLevel', 'timeEstimate', 'timeDue');
			
			// If the form was valid, create the task
			Task::create($postData['groupID'], $postData['assignTo'], $postData['summary']);
		}
	}
	
}

