<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Post Class ******
* This class sets up and handles the forum posts, but must have the User & Forum plugins active to use.
* 
****** Methods Available ******
* Post::exists($postID)							// Checks if the post exists
* Post::create($thread, $postMessage, $userID)	// Creates the post in an associated thread.
* Post::delete($postID)							// Deletes a post.
* Post::edit($postID, $newMessage)				// Edits a post's body text.
* Post::getData($postID)						// Retrieves data from a post.
* 
****** Database ******

CREATE TABLE IF NOT EXISTS `post`
(
	`id`				smallint(5)		UNSIGNED	NOT NULL	AUTO_INCREMENT,
	
	`forum_id`			int(11)			unsigned	NOT NULL	DEFAULT '0',
	`thread_id`			int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	`user_id`			int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	`title`				varchar(32)					NOT NULL	DEFAULT '',
	`message`			text						NOT NULL	DEFAULT '',
	
	`date_posted`		int(11)			unsigned	NOT NULL	DEFAULT '0',
	`date_edited`		int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	PRIMARY KEY (`id`),
	INDEX (`thread_id`, `date_posted`)
	
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

*/

abstract class Post {
	
	
/****** Check if a Post Exists ******/
	public static function exists
	(
		$postID		/* <int> The post ID to check if it exists. */
	)				/* RETURNS <bool> : TRUE if the post exists, FALSE if not. */
	
	// if(Post::exists(125)) { echo 'The post exists'; }
	{
		$checkPost = Database::selectOne("SELECT id FROM post WHERE `id`=? LIMIT 1", array($postID));
		
		if(isset($checkPost['id']))
		{
			return true;
		}
		
		return false;
	}
	
	
/****** Create a Post ******/
	public static function create
	(
		$threadID			/* <int> The thread ID that the post will be created in. */,
		$postMessage		/* <str> The message that you'd like to post. */,
		$userID				/* <int> The ID of the user posting. */
	)						/* RETURNS <bool> : TRUE after success, FALSE if something failed. */
	
	// Post::create("This is the main post of the body.")
	{
		$forumData = Forum::getData($forum);
		
		return Database::query("INSERT INTO `thread` (`forum_id`, `title`) VALUES (?, ?)", array($forumData['id'], $threadName));
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
