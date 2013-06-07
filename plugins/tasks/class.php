<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Task Class ******
* This class allows you to create, delete, edit, and assign tasks.
* 
****** Dependencies ******
* - "User" plugin
* - "Database" class
* 
****** Methods Available ******
* Task::createTables()				// Creates the Task tables.
* 
* Task::getList($tag, $userID, $startPos, $numToLoad, $sortType = "ASC")	// Returns task list by tag and user.
* Task::getListByTag($tag, $startPos, $numToLoad, $sortType = "ASC")		// Returns list of tasks by tag name.
* Task::getListByUser($userID, $startPos, $numToLoad, $sortType = "ASC")	// Returns list of tasks by user ID.
* 
* Task::getAssignedUsers($taskID)				// Retrieve the list of users assigned to the task.
* Task::orderTasksByImportance($taskList)		// Sorts a list of tasks by their relevance vs. priority & time due.
* 
* Task::create($tag, $userID, $task)			// Attaches task to the referenced tag.
* 
* Task::setSummary($taskID, $summary)			// Sets (or edits) the task's summary.
* Task::setDescription($taskID, $description)	// Sets (or edits) the task's description.
* Task::setPriority($taskID, $description)		// Sets (or edits) the task's priority.
* Task::setDueDate($taskID, $dueDate)			// Sets (or edits) the task's due date.
* 
* Task::complete($taskID)						// Sets a task to complete.
* Task::incomplete($taskID)						// Sets a task to incomplete.
* 
* Task::delete($TaskID)				// Deletes a single task, and any replies.
* Task::deleteByTag($tag)			// Deletes all Tasks that belong to a particular tag.
*/


abstract class Task {

/****** Create Task Table ******/
	public static function createTable(
	)					/* RETURNS <bool> : TRUE upon completion. */
	
	// Task::createTable();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `tasks` (
			`id`					int(11)			UNSIGNED	NOT NULL	AUTO_INCREMENT,
			`parentID`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			`tag`					varchar(22)					NOT NULL	DEFAULT '',
			`assignedByID`			int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			`summary`				varchar(128)				NOT NULL	DEFAULT '',
			`description`			text						NOT NULL	DEFAULT '',
			`priorityLevel`			tinyint(1)		UNSIGNED	NOT NULL	DEFAULT '0',
			`completed`				tinyint(1)		UNSIGNED	NOT NULL	DEFAULT '0',
			`timestamp`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			`timestamp_due`			int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			PRIMARY KEY (`id`),
			INDEX (`tag`, `timestamp`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `tasks_assigned` (
			`taskID`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			`userID`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			UNIQUE (`taskID`, `userID`)
			INDEX (`userID`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
		");
		
		return true;
	}
	
	
/****** Get Task List (By Tag Name & User) *******/
	public static function getList
	(
		$tag					/* <str> The unique tag name or ID of the tasks to retrieve. */,
		$userID					/* <int> The user ID of the task to retrieve. */,
		$startPos				/* <int> The starting position of the tasks to retrieve. */,
		$numToLoad				/* <int> The number of tasks to load. */,
		$sortType = "ASC"		/* <str> Set to "ASC" for ascending lists, or "DESC" for descending lists. */
	)							/* RETURNS <array> : Returns array of tasks (empty if none available). */
	
	// Task::getList("main-project", "Joe", 0, 20);
	{
		// 
	}
	
/****** Get Task List (By Tag Name) *******/
	public static function getListByTag
	(
		$tag					/* <str> The unique tag name or ID of the tasks to retrieve. */,
		$startPos				/* <int> The starting position of the tasks to retrieve. */,
		$numToLoad				/* <int> The number of tasks to load. */,
		$sortType = "ASC"		/* <str> Set to "ASC" for ascending lists, or "DESC" for descending lists. */
	)							/* RETURNS <array> : Returns array of tasks (empty if none available). */
	
	// Task::getListByTag("main-project", 0, 20);
	{
		$taskData = Database::selectMultiple("SELECT id, tag, assignedByID, summary, description, priority, timestamp, timestamp_due FROM tasks WHERE tag=? ORDER BY " . ($sortType == "DESC" ?  "DESC" : "ASC") . " LIMIT " . ($startPos + 0) . ", " . ($numToLoad + 0), array($tag));
		
		return $taskData;
	}
	
/****** Get Task List (By User ID) ******/
	public static function getListByUser
	(
		$userID					/* <int> The user ID of the task to retrieve. */,
		$startPos				/* <int> The starting position of the tasks to retrieve. */,
		$numToLoad				/* <int> The number of tasks to load. */,
		$sortType = "ASC"		/* <str> Set to "ASC" for ascending lists, or "DESC" for descending lists. */
	)							/* RETURNS <array> : Returns array of tasks (empty if none available). */
	
	// Task::getListByUser(1402, 0, 20);		// Retrieves information based on the user with an ID of 1402
	{
		// Gather list of tasks specific to the user
		
		// return Database::selectMultiple("SELECT id, tag, assignedByID, summary, description, priority, timestamp, timestamp_due FROM tasks WHERE userID=? ORDER BY " . ($sortType == "DESC" ?  "DESC" : "ASC") . " LIMIT " . ($startPos + 0) . ", " . ($numToLoad + 0), array($userID));
	}
	
	
/****** Get Assigned Users *******/
	public static function getAssignedUsers
	(
		$taskID				/* <int> The ID of the task to retrieve the users from. */
	)						/* RETURNS <array> : List of users if successful, empty array if something went wrong. */
	
	// Task::getAssignedUsers(140);	// Returns users that were assigned to the task #140
	{
		return Database::select("SELECT userID FROM tasks_assigned WHERE taskID=?", array($taskID);
	}
	
	
/****** Order a Task List By Importance *******/
	public static function orderTasksByImportance
	(
		$taskList			/* <array> The array of tasks that you're reviewing. */
	)						/* RETURNS <array> : List of tasks (sorted) on success, empty array if it went wrong. */
	
	// Task::getAssignedUsers(140);	// Returns users that were assigned to the task #140
	{
		return array();
	}
	
	
/****** Create a Task *******/
	public static function create
	(
		$tag				/* <str> The unique tag name or ID to connect the task to (such as a project). */,
		$assignedByID		/* <int> The user ID of the person assigning the task. */,
		$summary			/* <str> The summary of the task. */,
		$description = ""	/* <str> The full description of the task, if necessary. */
	)						/* RETURNS <bool> : TRUE if created properly, FALSE if something went wrong. */
	
	// Task::create("myProject", "Joe", "Do that one thing.", "Here are some details about that one thing.");
	{
		return Database::query("INSERT INTO `tasks` (`tag`, `assignedByID`, `summary`, `description`, `timestamp`) VALUES (?, ?, ?, ?, ?)", array($tag, $assignedByID, $summary, $description, time()));
	}
	
	
/****** Edit a Task's Summary *******/
	public static function setSummary
	(
		$taskID			/* <str> The ID of the task to edit. */,
		$summary		/* <str> The task summary that you'd like to update. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// Task::setSummary(140, "This is my updated summary!");
	{
		return Database::query("UPDATE tasks SET summary=? WHERE id=? LIMIT 1", array($summary, $taskID);
	}
		
/****** Edit a Task's Description *******/
	public static function setDescription
	(
		$taskID			/* <str> The ID of the task to edit. */,
		$description	/* <str> The task description that you'd like to update. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// Task::setDescription(140, "This is my updated description, which helps clarify what the task is!");
	{
		return Database::query("UPDATE tasks SET description=? WHERE id=? LIMIT 1", array($description, $taskID);
	}
	
	
/****** Edit a Task's Priority Level *******/
	public static function setPriority
	(
		$taskID			/* <str> The ID of the task to edit. */,
		$priorityLevel	/* <int> The level of prioritization that the task should receive. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// Task::setPriority(140, 4);
	{
		/*
			Levels of Priority:
			-------------------
			0.		Not a priority. Do this if nothing else is pressing.
			1 - 2.	Casual / Relaxed Prioritization
			3 - 4.	Relative importance. Needs a soft deadline.
			5 - 6	Important. Needs a hard deadline.
			7 - 8.	Urgent prioritization.
			9.		Emergency priority - drop all other tasks and focus on this.
		*/
		
		return Database::query("UPDATE tasks SET priorityLevel=? WHERE id=? LIMIT 1", array($priorityLevel, $taskID);
	}
	
/****** Edit a Task's Due Date *******/
	public static function setDueDate
	(
		$taskID			/* <str> The ID of the task to edit. */,
		$dueDate		/* <int> The timestamp of when the task is due. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// Task::setDueDate(140, 154203123);
	{
		/*
			Levels of Priority:
			-------------------
			0.		Not a priority. Do this if nothing else is pressing.
			1 - 2.	Casual / Relaxed Prioritization
			3 - 4.	Relative importance. Needs a soft deadline.
			5 - 6	Important. Needs a hard deadline.
			7 - 8.	Urgent prioritization.
			9.		Emergency priority - drop all other tasks and focus on this.
		*/
		
		return Database::query("UPDATE tasks SET timestamp_due=? WHERE id=? LIMIT 1", array($dueDate, $taskID);
	}
	
	
/****** Set a Task to Complete *******/
	public static function complete
	(
		$taskID			/* <str> The ID of the task to set to complete. */
	)					/* RETURNS <bool> : TRUE on success, FALSE if something went wrong. */
	
	// Task::complete(140);
	{
		return Database::query("UPDATE tasks SET completed=? WHERE id=? LIMIT 1", array(1, $taskID);
	}
	
	
/****** Set a Task to Incomplete *******/
	public static function incomplete
	(
		$taskID			/* <str> The ID of the task to set to incomplete. */
	)					/* RETURNS <bool> : TRUE on success, FALSE if something went wrong. */
	
	// Task::incomplete(140);
	{
		return Database::query("UPDATE tasks SET completed=? WHERE id=? LIMIT 1", array(0, $taskID);
	}
	
	
/****** Delete a Task *******/
	public static function delete
	(
		$taskID			/* <str> The ID of the task to delete. */
	)					/* RETURNS <bool> : TRUE if deleted properly, FALSE if something went wrong. */
	
	// Task::delete(140);
	{
		// Delete any children of this Task (loop recursively through all children layers)
		$children = Database::selectMultiple("SELECT id FROM tasks WHERE parentID=?", array($taskID));
		
		foreach($children as $child)
		{
			Task::delete($child['id']);
		}
		
		return Database::query("DELETE FROM tasks WHERE id=? LIMIT 1", array($taskID);
	}
	
	
/****** Delete all Tasks connected to said Tag *******/
	public static function deleteByTag
	(
		$tag					/* <str> The unique tag name or ID that we want to delete all tasks from. */,
		$completedOnly = false	/* <bool> If TRUE, it only deletes completed tasks. */
	)							/* RETURNS <bool> : TRUE if tasks deleted successfully, FALSE otherwise. */
	
	// Task::deleteByTag('myProject');
	{
		// If you're only deleting tasks that have been completed:
		if($completedOnly == true)
		{
			$taskList = Database::selectMultiple("SELECT id FROM tasks WHERE tag=? AND complete = ?", array($tag, 1));
		}
		else
		{
			$taskList = Database::selectMultiple("SELECT id FROM tasks WHERE tag=?", array($tag));
		}
		
		if($taskList == array())
		{
			return false;
		}
		
		foreach($taskList as $task)
		{
			Task::delete($task['id']);
		}
		
		return true;
	}
}

