<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Forum Class ******
* This class sets up and handles the forum, but must have the User class active, etc.
* 
****** Methods Available ******
* Forum::exists($forum)							// Checks if the forum exists (use ID or title)
* Forum::create($forumName)						// Creates the forum.
* Forum::delete($forum)							// Deletes a forum and all threads & posts inside.
* Forum::rename($forum, $newForumName)			// Renames a forum.
* Forum::getData($forum)						// Retrieves data from a forum.
* 
****** Database ******

CREATE TABLE IF NOT EXISTS `forum` (
	`id`					smallint(5)		UNSIGNED	NOT NULL	AUTO_INCREMENT,
	`title`					varchar(32)					NOT NULL	DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE (`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

*/

abstract class Forum {
	
	
/****** Check if a Forum Exists ******/
	public static function exists
	(
		$forum		/* <str> or <int> The forum ID or name to check if it exists. */
	)				/* RETURNS <bool> : TRUE if the forum exists, FALSE if not. */
	
	// Forum::exists("General Discussion")
	{
		$checkForum = Database::selectOne("SELECT id FROM forum WHERE `" . (is_numeric($forum) ? "id" : "title") . "`=? LIMIT 1", array($forum));
		
		if(isset($checkForum['id']))
		{
			return true;
		}
		
		return false;
	}
	
	
/****** Create a Forum ******/
	public static function create
	(
		$forumName			/* <str> The forum name to create. */
	)						/* RETURNS <bool> : TRUE after success, FALSE if something failed. */
	
	// Forum::create("General Discussion")
	{
		return Database::query("INSERT INTO `forum` (`title`) VALUES (?)", array($forumName));
	}
	
	
/****** Delete a Forum ******/
	public static function delete
	(
		$forum				/* <int> or <str> The forum ID (or title) to delete. */
	)						/* RETURNS <bool> : TRUE after success, FALSE if something failed. */
	
	// Forum::delete(7)
	// Forum::delete("General Discussion")
	{
		// Delete the Threads
		
		// Delete the Forum
		return Database::query("DELETE FROM forum WHERE `" . (is_numeric($forum) ? "id" : "title") . "`=?", array($forum));
	}
	
	
/****** Rename a Forum ******/
	public static function rename
	(
		$forum				/* <int> or <str> The forum ID or Name to edit. */,
		$newForumName		/* <str> The title to rename the forum. */
	)						/* RETURNS <bool> : TRUE after success, FALSE if something failed. */
	
	// Forum::rename(7, "General Discussion")
	{
		return Database::query("UPDATE `forum` SET `title`=? WHERE `" . (is_numeric($forum) ? "id" : "title") . "`=?", array($forum, $newForumName));
	}
	
	
/****** Get Forum Data ******/
	public static function getData
	(
		$forum			/* <int> or <str> The Forum ID or Name to retrieve. */
	)					/* RETURNS <array> : Forum Data array if retrieve was successful, empty array if not. */
	
	// Forum::getData("General DIscussion")
	{
		$forumData = Database::selectOne("SELECT * FROM forum WHERE " . (is_numeric($forum) ? 'id' : 'title') . "=? LIMIT 1", array($forum));
		
		if(isset($forumData['id']))
		{
			return $forumData;
		}
		
		return array();
	}
	
	
}
