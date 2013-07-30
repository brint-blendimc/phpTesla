<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Thread Class ******
* This class sets up and handles the forum threads, but must have the User & Forum plugins active, etc.
* 
****** Methods Available ******
* Thread::exists($threadID)						// Checks if the thread exists (use ID or title)
* Thread::create($forum, $threadName, $userID)	// Creates the thread in an associated forum.
* Thread::delete($threadID)						// Deletes a thread and all posts contained.
* Thread::rename($threadID, $newThreadName)		// Renames a thread.
* Thread::getData($threadID)					// Retrieves data from a thread.
* 
****** Database ******

CREATE TABLE IF NOT EXISTS `thread`
(
	`id`				smallint(5)		UNSIGNED	NOT NULL	AUTO_INCREMENT,
	`forum_id`			int(11)			unsigned	NOT NULL	DEFAULT '0',
	`user_id`			int(11)			unsigned	NOT NULl	DEFAULT '0',
	`title`				varchar(32)					NOT NULL	DEFAULT '',
	`last_update`		int(11)			unsigned	NOT NULL	DEFAULT '0',
	PRIMARY KEY (`id`),
	INDEX (`forum_id`, `last_update`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

*/

abstract class Thread {
	
	
/****** Check if a Thread Exists ******/
	public static function exists
	(
		$threadID	/* <int> The thread ID to check if it exists. */
	)				/* RETURNS <bool> : TRUE if the thread exists, FALSE if not. */
	
	// if(Thread::exists(125)) { echo 'The thread exists'; }
	{
		$checkThread = Database::selectOne("SELECT id FROM thread WHERE `id`=? LIMIT 1", array($threadID));
		
		if(isset($checkThread['id']))
		{
			return true;
		}
		
		return false;
	}
	
	
/****** Create a Thread ******/
	public static function create
	(
		$forum				/* <int> or <str> The forum ID or Name that you're going to add the thread to. */,
		$threadName			/* <str> The name of the thread you're creating. */,
		$userID				/* <int> The ID of the user creating the thread. */
	)						/* RETURNS <bool> : TRUE after success, FALSE if something failed. */
	
	// Thread::create("General Discussion", "A thread about puppies", 10)
	{
		// Get the Forum Data
		$forumData = Forum::getData($forum);
		
		
		
		// Insert the Thread
		return Database::query("INSERT INTO `thread` (`forum_id`, `title`, `user_id`) VALUES (?, ?, ?)", array($forumData['id'], $threadName, $userID));
	}
	
	
/****** Delete a Thread & Posts Contained ******/
	public static function delete
	(
		$threadID			/* <int> The thread ID to delete. */
	)						/* RETURNS <bool> : TRUE after success, FALSE if something failed. */
	
	// Thread::delete(125)
	{
		// Delete all Posts
		Database::query("DELETE FROM posts WHERE thread_id=?", array($threadID);
		
		// Delete the THread
		return Database::query("DELETE FROM thread WHERE `id`=?", array($threadID));
	}
	
	
/****** Rename a Thread ******/
	public static function rename
	(
		$threadID			/* <int> The thread ID to edit. */,
		$newThreadName		/* <str> The new title of the thread. */
	)						/* RETURNS <bool> : TRUE after success, FALSE if something failed. */
	
	// Thread::rename(7, "Talk Puppies And Stuff")
	{
		return Database::query("UPDATE `thread` SET `title`=? WHERE `id`=?", array($newThreadName, $threadID));
	}
	
	
/****** Get Thread Data ******/
	public static function getData
	(
		$threadID		/* <int> The Thread ID to retrieve. */
	)					/* RETURNS <array> : Thread data array if retrieve was successful, empty array if not. */
	
	// Thread::getData(125)
	{
		$threadData = Database::selectOne("SELECT * FROM forum WHERE `id`=? LIMIT 1", array($threadID));
		
		if(isset($threadData['id']))
		{
			return $threadData;
		}
		
		return array();
	}
	
	
}
