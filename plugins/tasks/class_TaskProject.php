<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Task Project Class ******
* This class allows you to create, delete, and modify task projects.
* 
****** Methods Available ******
* $plugin->tasks->
* 	project->exists($projectID)			// Checks if the task project actually exists.
*	project->getData($projectID)		// Retrieves the data of the project.
*	project->getList()					// Retrieves the project list.
* 	project->create($title)				// Creates a task project.
* 	project->setTitle($title)			// Sets the title of the task project.
* 	project->delete($projectID)			// Deletes a task project and all task groups and tasks involved.
*/

class TaskProject {


/****** Confirm that Task Project Exists *******/
	public static function exists
	(
		$projectID				/* <int> or <str> The project ID (or title) of the task project to verify exists. */
	)							/* RETURNS <bool> : TRUE on success, FALSE if something went wrong. */
	
	// $plugin->tasks->project->exists(10);
	{
		$project = Database::selectOne("SELECT id FROM `task_projects` WHERE " . (is_numeric($projectID) ? 'id' : "title") . "=? LIMIT 1", array($projectID));
		
		if(isset($project['id']))
		{
			return true;
		}
		
		return false;
	}
	
	
/****** Retrieve the Project Data *******/
	public static function getData
	(
		$projectID				/* <int> or <str> The project ID (or title) of the task project to retrieve data from. */
	)							/* RETURNS <array> : Content of the project. */
	
	// $projectData = $plugin->tasks->project->getData(10);
	{
		return Database::selectOne("SELECT * FROM `task_projects` WHERE " . (is_numeric($projectID) ? 'id' : "title") . "=? LIMIT 1", array($projectID));
	}
	
	
/****** Retrieve List of Projects *******/
	public static function getList (
	)			/* RETURNS <array> : A list of projects. */
	
	// $projectList = $plugin->tasks->project->getList();
	{
		return Database::selectMultiple("SELECT * FROM `task_projects`", array());
	}
	
	
/****** Create Task Project *******/
	public static function create
	(
		$title					/* <str> The name of the task project. */
	)							/* RETURNS <bool> : TRUE on success, FALSE if something went wrong. */
	
	// $plugin->tasks->project->create("My Project");
	{
		return Database::query("INSERT INTO `task_projects` (`title`) VALUES (?)", array($title));
	}
	
	
/****** Delete Task Project *******/
	public static function delete
	(
		$projectID				/* <int> The project ID that you're going to delete. */
	)							/* RETURNS <bool> : TRUE on success, FALSE if something went wrong. */
	
	// $plugin->tasks->project->delete(3);
	{
		// Find all of the task groups associated with the project, and delete them:
		$taskGroups = Database::selectMultiple("SELECT id FROM task_groups WHERE projectID=?", array($projectID));
		
		foreach($taskGroups as $task)
		{
			TaskGroup::delete($task['id']); // Deletes the task group properly, along with tasks associated.
		}
		
		return Database::query("DELETE FROM `task_projects` WHERE id=?", array($title));
	}
}
