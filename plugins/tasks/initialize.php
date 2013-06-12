<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Task Plugin ******
* This class allows you to create, delete, edit, and assign tasks.
* 
****** Dependencies ******
* - "User" plugin
* - "Database" class
* 
****** Methods Available ******
* $plugin->
*	task->createTables()				// Creates the Task tables.
* 
*	task->getListByGroup($groupID, $startPos, $numToLoad, $sortType = "ASC")	// Returns list of tasks by group.
* 	task->getListByUser($userID, $startPos, $numToLoad, $sortType = "ASC")		// Returns list of tasks by user ID.
* 
* 	task->orderTasksByImportance($taskList)		// Sorts a list of tasks by their relevance vs. priority & time due.
* 
* 	task->exists($taskID)								// Checks if a task actually exists.
* 	task->create($groupID, $assignedByID, $summary)		// Creates a task and sets the appropriate project and group.
* 
* 	task->setUser($taskID, $userID)					// Sets the task's assigned user.
* 	task->setSummary($taskID, $summary)				// Sets the task's summary.
* 	task->setDescription($taskID, $description)		// Sets the task's description.
* 	task->setPriority($taskID, $description)		// Sets the task's priority.
* 	task->setTimeEstimate($taskID, $timeEstimate)	// Sets the task's estimated time cost.
* 	task->setTimeActual($taskID, $timeActual)		// Sets the task's actual time cost.
* 	task->setTimeDue($taskID, $timeDue)				// Sets the task's due date.
* 	task->setTimeFinished($taskID, $timeFinished)	// Sets the task's actual completion date.
* 
* 	task->setComplete($taskID)						// Sets a task to complete.
* 	task->setIncomplete($taskID)					// Sets a task to incomplete.
* 
* 	task->delete($TaskID)				// Deletes a single task.
*/

/*
	Considerations:
	1. Make it so that clients can see the boards if they have permission.
*/

class TasksPlugin {

/****** Important Values ******/
	public $group = null;
	public $project = null;

/****** Initializer ******/
	function __construct()
	{
		// Extend all of the Task Classes
		require_once(BASE_DIR . "/plugins/tasks/class_TaskGroup.php");
		require_once(BASE_DIR . "/plugins/tasks/class_TaskProject.php");
		
		$this->group = new TaskGroup();
		$this->project = new TaskProject();
	}
	
/****** Create Task Table ******/
	public static function createTables(
	)					/* RETURNS <bool> : TRUE upon completion. */
	
	// $plugin->task->createTable();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `task_projects` (
			`id`					int(11)			UNSIGNED	NOT NULL	AUTO_INCREMENT,
			`title`					varchar(32)					NOT NULL	DEFAULT '',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `task_groups` (
			`id`					int(11)			UNSIGNED	NOT NULL	AUTO_INCREMENT,
			
			`projectID`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			
			`title`					varchar(32)					NOT NULL	DEFAULT '',
			`description`			text						NOT NULL	DEFAULT '',
			
			`priorityLevel`			tinyint(1)		UNSIGNED	NOT NULL	DEFAULT '0',
			`completePercent`		float(5,2)		UNSIGNED	NOT NULL	DEFAULT '0.00',
			
			`timeCreated`			int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			`timeDue`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`),
			INDEX (`projectID`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `tasks` (
			`id`					int(11)			UNSIGNED	NOT NULL	AUTO_INCREMENT,
			
			`projectID`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			`groupID`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			
			`assignedByID`			int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			`assignedToID`			int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			
			`summary`				varchar(128)				NOT NULL	DEFAULT '',
			`description`			text						NOT NULL	DEFAULT '',
			
			`priorityLevel`			tinyint(1)		UNSIGNED	NOT NULL	DEFAULT '0',
			`completed`				tinyint(1)		UNSIGNED	NOT NULL	DEFAULT '0',
			
			`timeEstimate`			mediumint(6)	UNSIGNED	NOT NULL	DEFAULT '0',
			`timeActual`			mediumint(6)	UNSIGNED	NOT NULL	DEFAULT '0',
			
			`timeCreated`			int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			`timeDue`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			`timeFinished`			int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`),
			INDEX (`groupID`, `timeDue`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
		");
		
		return true;
	}
	
	
/****** Confirm that Task Exists *******/
	public static function exists
	(
		$taskID				/* <int> The task ID of the task to verify exists. */
	)						/* RETURNS <bool> : TRUE on success, FALSE if something went wrong. */
	
	// $plugin->task->exists(10);
	{
		$task = Database::selectOne("SELECT id FROM `tasks` WHERE id=? LIMIT 1", array($taskID));
		
		if(isset($task['id']))
		{
			return true;
		}
		
		return false;
	}

	
/****** Get Task List (By Project) *******/
	public static function getListByProject
	(
		$projectID				/* <int> The project ID of the project to retrieve tasks from. */,
		$startPos				/* <int> The starting position of the tasks to retrieve. */,
		$numToLoad				/* <int> The number of tasks to load. */,
		$sortType = "ASC"		/* <str> Set to "ASC" for ascending lists, or "DESC" for descending lists. */
	)							/* RETURNS <array> : Returns array of tasks (empty if none available). */
	
	// $tasks = $plugin->task->getListByProject(3, 0, 20);		// Retrieves tasks based on the task project with ID of 3
	{
		$taskData = Database::selectMultiple("SELECT * FROM tasks WHERE projectID=? ORDER BY " . ($sortType == "DESC" ?  "DESC" : "ASC") . " LIMIT " . ($startPos + 0) . ", " . ($numToLoad + 0), array($projectID));
		
		return $taskData;
	}
	
/****** Get Task List (By Tag Name) *******/
	public static function getListByGroup
	(
		$groupID				/* <int> The group ID of the group to retrieve tasks from. */,
		$startPos				/* <int> The starting position of the tasks to retrieve. */,
		$numToLoad				/* <int> The number of tasks to load. */,
		$sortType = "ASC"		/* <str> Set to "ASC" for ascending lists, or "DESC" for descending lists. */
	)							/* RETURNS <array> : Returns array of tasks (empty if none available). */
	
	// $tasks = $plugin->task->getListByGroup(5, 0, 20);		// Retrieves tasks based on the task group with ID of 5
	{
		$taskData = Database::selectMultiple("SELECT * FROM tasks WHERE groupID=? ORDER BY " . ($sortType == "DESC" ?  "DESC" : "ASC") . " LIMIT " . ($startPos + 0) . ", " . ($numToLoad + 0), array($groupID));
		
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
	
	// $tasks = $plugin->task->getListByUser(1402, 0, 20);		// Retrieves tasks based on the user with an ID of 1402
	{
		$taskData = Database::selectMultiple("SELECT * FROM tasks WHERE assignedToID=? ORDER BY " . ($sortType == "DESC" ?  "DESC" : "ASC") . " LIMIT " . ($startPos + 0) . ", " . ($numToLoad + 0), array($userID));
		
		return $taskData;
	}
	
	
/****** Order a Task List By Importance *******/
	public static function orderTasksByImportance
	(
		$taskList			/* <array> The array of tasks that you're reviewing. */
	)						/* RETURNS <array> : List of tasks (sorted) on success, empty array if it went wrong. */
	
	// $listOfTasks = $plugin->task->orderTasksByImportance($listOfTasks);
	{
		$importance = array();
		
		foreach($taskList as $task)
		{
			// If there is a deadline
			if($task['timeDue'] > 0)
			{
				// If you're closing in on your deadline, increase importance
				$urgencyTimestamp = $task['timeDue'] - (3600 * 24 * 7);
				
				if(time() > $urgencyTimestamp)
				{
					$urgencyDuration = time() - $urgencyTimestamp;
					
					$task['priorityLevel'] += min(ceil($urgencyDuration / 3600) * 0.015, $task['priorityLevel'] + 1);
					
					// Emergency Timestamp
					$emergencyTimestamp = $task['timeDue'] - (3600 * 24);
					
					if(time() > $emergencyTimestamp)
					{
						$emergencyDuration = time() - $emergencyTimestamp;
						
						$task['priorityLevel'] += min(ceil($emergencyDuration / 3600) * 0.025, $task['priorityLevel'] + 1);
					}
				}
			}
			
			$importance[$task['priorityLevel']] = $task;
		}
		
		// Make sure the array is sorted by its weighted values
		krsort($importance);
		
		return $importance;
	}
	
	
/****** Create a Task *******/
	public static function create
	(
		$groupID			/* <int> The unique group ID to connect the task to. */,
		$assignedByID		/* <int> The user ID of the person assigning the task. */,
		$summary			/* <str> The summary of the task. */,
		$description = ""	/* <str> The full description of the task, if necessary. */
	)						/* RETURNS <bool> : TRUE if created properly, FALSE if something went wrong. */
	
	// $plugin->task->create("myProject", "Joe", "Do that one thing.", "Here are some details about that one thing.");
	{
		return Database::query("INSERT INTO `tasks` (`groupID`, `assignedByID`, `summary`, `description`, `timestamp`) VALUES (?, ?, ?, ?, ?)", array($groupID, $assignedByID, $summary, $description, time()));
	}
	
	
/****** Edit a Task's Assigned User *******/
	public static function setUser
	(
		$taskID			/* <int> The ID of the task to edit. */,
		$userID			/* <str> The task summary that you'd like to update. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// $plugin->task->setSummary(140, 5);
	{
		return Database::query("UPDATE tasks SET assignedToID=? WHERE id=? LIMIT 1", array($userID, $taskID));
	}
	
	
/****** Edit a Task's Summary *******/
	public static function setSummary
	(
		$taskID			/* <int> The ID of the task to edit. */,
		$summary		/* <str> The task summary that you'd like to update. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// $plugin->task->setSummary(140, "This is my updated summary!");
	{
		return Database::query("UPDATE tasks SET summary=? WHERE id=? LIMIT 1", array($summary, $taskID));
	}
		
/****** Edit a Task's Description *******/
	public static function setDescription
	(
		$taskID			/* <int> The ID of the task to edit. */,
		$description	/* <str> The task description that you'd like to update. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// $plugin->task->setDescription(140, "This is my updated description, which helps clarify what the task is!");
	{
		return Database::query("UPDATE tasks SET description=? WHERE id=? LIMIT 1", array($description, $taskID));
	}
	
	
/****** Edit a Task's Priority Level *******/
	public static function setPriority
	(
		$taskID			/* <int> The ID of the task to edit. */,
		$priorityLevel	/* <int> The level of prioritization that the task should receive. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// $plugin->task->setPriority(140, 4);
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
		
		return Database::query("UPDATE tasks SET priorityLevel=? WHERE id=? LIMIT 1", array($priorityLevel, $taskID));
	}
	
	
/****** Edit a Task's Time Estimate *******/
	public static function setTimeEstimate
	(
		$taskID			/* <int> The ID of the task to edit. */,
		$timeEstimate	/* <int> The timestamp of how long the task is estimated to take. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// $plugin->task->setTimeEstimate(140, 3600);
	{
		return Database::query("UPDATE tasks SET timeEstimate=? WHERE id=? LIMIT 1", array($timeEstimate, $taskID));
	}
	
	
/****** Edit a Task's Actual Time Cost *******/
	public static function setTimeActual
	(
		$taskID			/* <int> The ID of the task to edit. */,
		$timeActual		/* <int> The timestamp of how long the task actually took. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// $plugin->task->setTimeActual(140, 7200);
	{
		return Database::query("UPDATE tasks SET timeActual=? WHERE id=? LIMIT 1", array($timeActual, $taskID));
	}
	
	
/****** Edit a Task's Due Date *******/
	public static function setTimeDue
	(
		$taskID			/* <int> The ID of the task to edit. */,
		$timeDue		/* <int> The timestamp of when the task is due. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// $plugin->task->setTimeDue(140, 154203123);
	{
		return Database::query("UPDATE tasks SET timeDue=? WHERE id=? LIMIT 1", array($timeDue, $taskID));
	}
	
	
/****** Edit a Task's Due Date *******/
	public static function setTimeFinished
	(
		$taskID			/* <int> The ID of the task to edit. */,
		$timeFinished	/* <int> The timestamp of when the task is actually finished. */
	)					/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// $plugin->task->setTimeFinished(140, 154203123);
	{
		return Database::query("UPDATE tasks SET timeFinished=? WHERE id=? LIMIT 1", array($timeFinished, $taskID));
	}
	
	
/****** Set a Task to Complete *******/
	public static function setComplete
	(
		$taskID			/* <str> The ID of the task to set to complete. */
	)					/* RETURNS <bool> : TRUE on success, FALSE if something went wrong. */
	
	// $plugin->task->complete(140);
	{
		return Database::query("UPDATE tasks SET completed=? WHERE id=? LIMIT 1", array(1, $taskID));
	}
	
	
/****** Set a Task to Incomplete *******/
	public static function setIncomplete
	(
		$taskID			/* <str> The ID of the task to set to incomplete. */
	)					/* RETURNS <bool> : TRUE on success, FALSE if something went wrong. */
	
	// $plugin->task->incomplete(140);
	{
		return Database::query("UPDATE tasks SET completed=? WHERE id=? LIMIT 1", array(0, $taskID));
	}
	
	
/****** Delete a Task *******/
	public static function delete
	(
		$taskID			/* <str> The ID of the task to delete. */
	)					/* RETURNS <bool> : TRUE if deleted properly, FALSE if something went wrong. */
	
	// $plugin->task->delete(140);
	{
		return Database::query("DELETE FROM tasks WHERE id=? LIMIT 1", array($taskID));
	}
	
}