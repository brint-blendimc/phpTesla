<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Comment Class ******
* This class allows you to post, edit, delete, retrieve, and interact with comments. These comments can be associated
* with generic tags, enabling them to serve a multi-purpose function. By connecting with tags, they can work with
* forums, discussion boards, blogs, or any other comment-based system.
* 
****** Dependencies ******
* - "User" plugin
* - "Database" class (for interacting with the users and clearance tables)
* 
****** Methods Available ******
* Comment::createTable()				// Creates the comment table.
* 
* Comment::getList($tag, $startPos, $numToLoad, $sortType = "ASC")		// Returns the list of comments.
* 
* Comment::create($tag, $username or $userID, $comment)			// Attaches comment to the referenced tag.
* Comment::reply($commentID, $username or $userID, $comment)	// Attaches comment to parent comment.
* Comment::edit($commentID, $comment)							// Edits the comment.
* Comment::getOwner($commentID)									// Returns the username of the comment's owner.
* 
* Comment::delete($commentID)				// Deletes a single comment, and any replies.
* Comment::deleteByTag($tag)				// Deletes all comments that belong to a particular tag.
*/


abstract class Comment {

/****** Create Comment Table ******/
	public static function createTable(
	)					/* RETURNS <bool> : TRUE upon completion. */
	
	// Comment::createTable();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `comments` (
			`id`					int(11)			UNSIGNED	NOT NULL	AUTO_INCREMENT,
			`parentID`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			`tag`					varchar(22)					NOT NULL	DEFAULT '',
			`userID`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			`comment`				text						NOT NULL	DEFAULT '',
			`timestamp`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			PRIMARY KEY (`id`),
			INDEX (`tag`, `timestamp`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
		");
		
		return true;
	}
	
	
/****** Get Comment List *******/
	public static function getList
	(
		$tag					/* <str> The unique tag name or ID of the comments to retrieve. */,
		$startPos				/* <int> The starting position of the comments to retrieve. */,
		$numToLoad				/* <int> The number of comments to load. */,
		$sortType = "ASC"		/* <str> Set to "ASC" for ascending lists, or "DESC" for descending lists. */,
		$includeReplies = true	/* <bool> Set to true if you want comment replies to be included with your result. */
	)							/* RETURNS <array> : Returns array of comments (empty if none available). */
	
	// Comment::getList("blog-about-puppies", 0, 20);
	{
		$commentData = Database::selectMultiple("SELECT id, userID, comment, timestamp FROM comments WHERE tag=? ORDER BY " . ($sortType == "DESC" ?  "DESC" : "ASC") . " LIMIT " . ($startPos + 0) . ", " . ($numToLoad + 0), array($tag));
		
		return $commentData;
	}
	
	
/****** Create a Comment *******/
	public static function create
	(
		$tag			/* <str> The unique tag name or ID to connect the comment to. */,
		$user			/* <str> or <int> : The $username or $userID of the user that you're posting as. */,
		$comment		/* <str> The comment to post. */
	)					/* RETURNS <bool> : TRUE if created properly, FALSE if something went wrong. */
	
	// Comment::create("blog-about-puppies", "Joe", "This is my comment! Huzzah!");
	{
		// Make sure the user exists and recover the user ID
		$userData = Database::selectOne("SELECT id FROM users WHERE " . (is_int($user) ? "id" : "username") . "=? LIMIT 1", array($user);
		
		if(!isset($userData['id']))
		{
			return false;
		}
		
		// Insert the Comment
		return Database::query("INSERT INTO `comments` (`tag`, `userID`, `comment`, `timestamp`) VALUES (?, ?, ?, ?)", array($tag, $userData['id'], $comment, time()));
	}
	
	
/****** Reply to a Comment *******/
	public static function reply
	(
		$commentID			/* <str> The comment ID that is being responded to. */,
		$user				/* <str> or <int> : The $username or $userID of the user that you're posting as. */,
		$comment			/* <str> The comment to post. */
	)						/* RETURNS <bool> : TRUE if it replies properly, FALSE if something went wrong. */
	
	// Comment::reply(115, "Joe", "I am responding to your comment.");
	{
		// Make sure the user exists and recover the user ID
		$userData = Database::selectOne("SELECT id FROM users WHERE " . (is_int($user) ? "id" : "username") . "=? LIMIT 1", array($user);
		
		if(!isset($userData['id']))
		{
			return false;
		}
		
		// Make sure the comment parent exists
		$commentData = Database::selectOne("SELECT id FROM comments WHERE id=? LIMIT 1", array($commentID));
		
		if(!isset($commentData['id']))
		{
			return false;
		}
		
		// Insert the Comment
		return Database::query("INSERT INTO `comments` (`parentID`, `userID`, `comment`, `timestamp`) VALUES (?, ?, ?, ?)", array($commentData['id'], $userData['id'], $comment, time()));
	}
	
	
/****** Edit a Comment *******/
	public static function edit
	(
		$commentID			/* <str> The ID of the comment to edit. */
		$comment			/* <str> The comment text that you'd like to post. */
	)						/* RETURNS <bool> : TRUE if updated properly, FALSE if something went wrong. */
	
	// Comment::edit(140, "This is my updated comment!");
	{
		return Database::query("UPDATE comments SET comment=? WHERE id=? LIMIT 1", array($comment, $commentID);
	}
	
	
/****** Delete a Comment *******/
	public static function delete
	(
		$commentID			/* <str> The ID of the comment to delete. */
	)						/* RETURNS <bool> : TRUE if deleted properly, FALSE if something went wrong. */
	
	// Comment::delete(140);
	{
		// Delete any children of this comment (loop recursively through all children layers)
		$children = Database::selectMultiple("SELECT id FROM comments WHERE parentID=?", array($commentID));
		
		foreach($children as $child)
		{
			Comment::delete($child['id']);
		}
		
		return Database::query("DELETE FROM comments WHERE id=? LIMIT 1", array($commentID);
	}
	
	
/****** Delete all Comments connected to said Tag *******/
	public static function deleteByTag
	(
		$tag					/* <str> The unique tag name or ID that we want to delete all comments from. */,
		$earlierThan = "now"	/* <int> If a timestamp is provided, it will only delete comments prior to that time. */
	)							/* RETURNS <bool> : TRUE if comments deleted successfully, FALSE otherwise. */
	
	// Comment::deleteByTag('blog-about-puppies');
	{
		// Prepare the prune time
		$pruneTime = (is_int($earlierThan) ? $earlierThan : time());
		
		// Scan through comments that fit the deletion requirements and recursively delete all comment children
		$commentList = Database::selectMultiple("SELECT id FROM comments WHERE tag=? AND timestamp <= ?", array($tag, $pruneTime));
		
		if($commentList == array())
		{
			return false;
		}
		
		foreach($commentList as $comment)
		{
			Comment::delete($comment['id']);
		}
		
		return true;
	}
}

