<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }
		
/****** Task Group Class ******
* This class allows you to create, delete, and modify task groups.
* 
****** Methods Available ******
* $plugin->tasks->
* 	group->exists($groupID)							// Checks if the task group actually exists.
* 	group->create($projectID, $title)				// Creates a task group.
* 	
* 	group->getTimeEstimate($groupID)				// Retrieves the estimated time based on tasks involved.
* 	group->getTimeActual($groupID)					// Retrieves the actual time that has been done on project.
* 	
* 	group->setTitle($groupID, $title)				// Sets the title of the task group.
* 	group->setDescription($groupID, $description)	// Sets the description of the task group.
* 	group->setPriority($groupID, $priorityLevel)	// Sets the priority level of the task group.
* 	group->setTimeDue($groupID, $timeDue)			// Sets the time the task group is due.
* 	
* 	group->delete($groupID)							// Deletes the task group and all tasks connected to it.
*/

class TaskGroup {


/****** Confirm that Task Group Exists *******/
	public static function exists
	(
		$groupID				/* <int> The group ID of the task group to verify exists. */
	)							/* RETURNS <bool> : TRUE on success, FALSE if something went wrong. */
	
	// $plugin->tasks->group->exists(10);
	{
		$group = Database::selectOne("SELECT id FROM `task_groups` WHERE id=? LIMIT 1", array($groupID));
		
		if(isset($group['id']))
		{
			return true;
		}
		
		return false;
	}

	
/****** Create Task Group *******/
	public static function create
	(
		$projectID				/* <int> The project ID that the task group is being assigned to. */,
		$title					/* <str> The name of the task group. */
	)							/* RETURNS <bool> : TRUE on success, FALSE if something went wrong. */
	
	// $plugin->tasks->group->create(10, "Footer Redo");
	{
		return Database::query("INSERT INTO `task_groups` (`projectID`, `title`, `timeCreated`) VALUES (?, ?, ?)", array($projectID, $title, time()));
	}

	
/****** Get Time Estimate of Task Group *******/
	public static function getTimeEstimate
	(
		$groupID				/* <int> The group ID to identify the time estimated. */
	)							/* RETURNS <int> : The amount of time estimated that it will take. */
	
	// $plugin->tasks->group->getTimeEstimate(5);
	{
		$tasks = Database::selectOne("SELECT SUM(timeEstimate) as totalEstimate FROM tasks WHERE groupID=?", array($groupID));
		
		return $tasks['totalEstimate'];
	}

	
/****** Get Time Cost of Task Group *******/
	public static function getTimeActual
	(
		$groupID				/* <int> The group ID to identify the time actually spent on the group. */
	)							/* RETURNS <int> : The amount of time actually worked on the task. */
	
	// $plugin->tasks->group->getTimeActual(5);
	{
		$tasks = Database::selectOne("SELECT SUM(timeActual) as totalActual FROM tasks WHERE groupID=?", array($groupID));
		
		return $tasks['totalActual'];
	}
	
	
/****** Edit a Task Group's Title *******/
	public static function setTitle
	(
		$groupID		/* <int> The ID of the task group to edit. */,
		$title			/* <str> The group title that you'd like to update. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// $plugin->tasks->group->setTitle(140, "New Title!");
	{
		return Database::query("UPDATE task_groups SET title=? WHERE id=? LIMIT 1", array($title, $groupID));
	}
	
	
/****** Edit a Task Group's Description *******/
	public static function setDescription
	(
		$groupID		/* <int> The ID of the task group to edit. */,
		$description	/* <str> The group description that you'd like to update. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// $plugin->tasks->group->setDescription(140, "This is the description of the task group, for further details.");
	{
		return Database::query("UPDATE task_groups SET description=? WHERE id=? LIMIT 1", array($description, $groupID));
	}
	
	
/****** Edit a Task Group's Priority Level *******/
	public static function setPriority
	(
		$groupID		/* <int> The ID of the task group to edit. */,
		$priorityLevel	/* <int> The priority level of the task group. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// $plugin->tasks->group->setPriority(140, 6);
	{
		return Database::query("UPDATE task_groups SET priorityLevel=? WHERE id=? LIMIT 1", array($priorityLevel, $groupID));
	}
	
	
/****** Edit a Task Group's Time Due *******/
	public static function setTimeDue
	(
		$groupID		/* <int> The ID of the task group to edit. */,
		$timeDue		/* <int> The timestamp of when the task group is due. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// $plugin->tasks->group->setTimeDue(140, 155238249);
	{
		return Database::query("UPDATE task_groups SET timeDue=? WHERE id=? LIMIT 1", array($timeDue, $groupID));
	}
	
	
/****** Delete Task Group (and all tasks inside) *******/
	public static function delete
	(
		$groupID				/* <str> The unique group ID that we want to delete all tasks from. */,
		$completedOnly = false	/* <bool> If TRUE, it only deletes completed tasks. */
	)							/* RETURNS <bool> : TRUE if tasks deleted successfully, FALSE otherwise. */
	
	// $plugin->tasks->group->delete(5);
	{
		// If you're only deleting tasks that have been completed
		if($completedOnly == true)
		{
			$taskList = Database::selectMultiple("SELECT id FROM tasks WHERE groupID=? AND complete=?", array($groupID, 1));
			
			foreach($taskList as $task)
			{
				TasksPlugin::delete($task['id']);
			}
		}
		
		// Delete all tasks in the task group
		else
		{
			Database::query("DELETE FROM tasks WHERE groupID=?", array($groupID));
		}
		
		return Database::query("DELETE FROM task_groups WHERE id=?", array($groupID));
	}
	
}

