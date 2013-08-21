<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Job Class ******
* This class sets up and handles administrative tasks. If set up with cron, this can also used to automate tasks at
* specific times.
* 
****** Methods Available ******
* Job::create($title, $function, $args, $runEveryXSeconds = 0, $schedule = "")		// Creates a routine task
* Job::once($title, $function, $args, $runTime = 0)									// Creates a task to be run once
* 
* Job::delete($jobID)				// Deletes a job.
* Job::runQueue()					// Run the job queue.
* 
****** Database *****

CREATE TABLE IF NOT EXISTS `jobs` (
	`id`					smallint(11)	UNSIGNED	NOT NULL	AUTO_INCREMENT,
	`title`					varchar(32)					NOT NULL	DEFAULT '',
	
	`class`					varchar(18)					NOT NULL	DEFAULT '',
	`method`				varchar(24)					NOT NULL	DEFAULT '',
	`parameters`			text						NOT NULL	DEFAULT '',
	
	`runEveryXSeconds`		mediumint(8)	UNSIGNED	NOT NULL	DEFAULT '0',
	`schedule`				varchar(250)				NOT NULL	DEFAULT '',
	`startAt`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
	`endAt`					int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
	`nextActivation`		int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
	
	PRIMARY KEY (`id`),
	INDEX (`nextActivation`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

*/

abstract class Job {
	
	
/****** Create Job ******/
	public static function create
	(
		$title					/* <str> The title of the job, useful only to humans. */,
		$function				/* <str> The class::method to run, write with :: between class and method. */,
		$args = array()			/* <array> The array of arguments to pass to the job's function. */,
		$runEveryXSeconds = 0	/* <int> Seconds until you repeat the task. If 0 seconds, use the schedule instead. */,
		$startAt = 0			/* <int> Timestamp of when to start the task. If 0, start now. */,
		$endAt = 0				/* <int> Timestamp of when to end the task. If 0, don't end. If < now, run once. */,
		$schedule = ""			/* <str> If set, run the task only at the times that follow these instructions. */
	)							/* RETURNS <bool> : TRUE if login validation was successful, FALSE if not. */
	
	// Job::create("Mailing List", "Email::sendToList", array("subscribed", "confirmed_email"), 10)
	{
		// Prepare job functionality
		$function = Sanitize::variable($function, ":");
		
		$jobFunction = explode("::", $function);
		
		if(count($jobFunction) != 2)
		{
			Note::error("Job Function", "Job creation failed due to improper function: " . $function);
		}
		else if(!class_exists($jobFunction[0]))
		{
			Note::error("Job Function", "The class you tried to create, " . $jobFunction[0] . ", doesn't exist.");
		}
		else if(!method_exists($jobFunction[0], $jobFunction[1]))
		{
			Note::error("Job Function", "The method you tried to use, " . $jobFunction[0] . "::" . $jobFunction[1] . "(), doesn't exist.");
		}
		
		// Return FALSE if we've had any errors so far
		if(Note::hasErrors()) { return false; }
		
		// Quick Sanitizing & Preparation
		$title = Sanitize::safeword($title);
		$argsJSON = json_encode($args);
		
		// Prepare the activation time
		$startAt = $startAt < time() ? $startAt = time() : $startAt;
		
		$nextActivation = $startAt + $runEveryXSeconds;
		
		// Create the Job
		return Database::query("INSERT INTO `jobs` (`title`, `class`, `method`, `parameters`, `runEveryXSeconds`, `schedule`, `startAt`, `endAt`, `nextActivation`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", array($title, $jobFunction[0], $jobFunction[1], $argsJSON, $runEveryXSeconds, $schedule, $startAt, $endAt, $nextActivation));
	}
	
	
/****** Delete Job ******/
	public static function delete
	(
		$id		/* <int> The Job ID to delete. */
	)			/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Job::delete(153)
	{
		return Database::query("DELETE FROM jobs WHERE id=?", array($id));
	}
	
	
/****** Run all jobs in the Job Queue ******/
	public static function runQueue (
	)			/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Job::runQueue()
	{
		// Retrieve the next jobs in the list
		$jobList = Database::selectMultiple("SELECT * FROM jobs WHERE nextActivation < ?", array(time()));
		
		// Cycle through the jobs provided
		foreach($jobList as $job)
		{
			// Run the job
			Job::process($job);
			
			// Reset or remove jobs
			self::reset($job['id'], $job['nextActivation'], $job['runEveryXSeconds'], $job['startAt'], $job['endAt']);
		}
		
		return true;
	}
	
	
/****** Run Job (from ID) ******/
	private static function run
	(
		$id				/* <int> The ID of the job you want to run. */,
		$reset = true	/* <bool> TRUE to reset the job activation afterward, FALSE to not reset. */
	)					/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Job::run($id)
	{
		// Retrieve the job
		$job = Database::selectOne("SELECT * FROM jobs WHERE id=? LIMIT 1", array($id));
		
		// Activate the job
		Job::process($job);
		
		// Reset or remove jobs
		if($reset == true)
		{
			self::reset($job['id'], $job['nextActivation'], $job['runEveryXSeconds'], $job['startAt'], $job['endAt']);
		}
		
		return true;
	}
	
	
/****** Process Job (from Data) ******/
	private static function process
	(
		$jobData		/* <array> The job array. */
	)					/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Job::process($jobData)
	{
		// Process the Job
		if(isset($jobData['class']) && isset($jobData['method']))
		{
			if(class_exists($jobData['class']) && method_exists($jobData['class'], $jobData['method']))
			{
				echo "Job Processed";
				var_dump($jobData);
			}
		}
		
		return true;
	}
	
	
/****** Reset Job ******
Run this function when you need to set the next activation timer for the job. If the job is old and shouldn't be used
again, this function will remove it from the job list. */
	private static function reset
	(
		$id						/* <int> The ID of the job that you want to reset. */,
		$nextActivation			/* <int> The next activation timer for the job. */,
		$runEveryXSeconds		/* <int> The number of seconds to pass until you reset. */,
		$startAt				/* <int> The timestamp to start at. */,
		$endAt					/* <int> The timestamp to end at. */,
		$schedule = ""			/* <str> The scheduling for the job. */
	)							/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Job::reset(12)
	{
		$currentTime = time();
		
		// Delete the job if it's old
		if($endAt > 0 && $currentTime > $endAt)
		{
			Job::delete($id);
			
			return true;
		}
		
		// Make sure that next activation time isn't in the future
		if($nextActivation > $currentTime) { return false; }
		
		// Prepare minimum activation timer
		$nextActivation = max($startAt, $nextActivation, $currentTime) + $runEveryXSeconds;
		
		// Update the job
		Database::query("UPDATE jobs SET nextActivation=? WHERE id=? LIMIT 1", array($nextActivation, $id));
	}
}