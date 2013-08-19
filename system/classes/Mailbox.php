<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Mailbox Class ******
* This class allows users to exchange messages between each other.
* 
****** Methods Available ******
* Mailbox::create($userList)						// Creates a Mailbox Thread and adds users.
* 
* Mailbox::subscribe($threadID, $user)				// Adds a subscriber to a thread.
* Mailbox::subscribeMultiple($threadID, $userList)	// Adds multiple subscribers to a thread.
* Mailbox::unsubscribe($threadID, $user)			// Removes a subscriber from a thread.
* 
* Mailbox::threadExists($threadID)					// Make sure the thread exists.
* Mailbox::delete($threadID)						// Deletes a thread and all posts / subscribers.
* Mailbox::getSubscribers($threadID)				// Retrieve a list of subscribers.
* Mailbox::getPosts($threadID)						// Retrieve a list of posts from a thread.
* Mailbox::addPost($threadID, $user, $message)		// Adds a post to a particular thread.
* + Mailbox::pruneThread($threadID, $time)			// Prune posts from thread prior that appear prior to $time
* 
****** Database ******

CREATE TABLE IF NOT EXISTS `mailbox_thread`
(
	`id`					int(11)			unsigned	NOT NULL	AUTO_INCREMENT,
	`title`					varchar(32)					NOT NULL	DEFAULT '',
	`last_update`			int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	PRIMARY KEY (`id`),
	INDEX (`last_update`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mailbox_thread_subscribers`
(
	`thread_id`				int(11)			unsigned	NOT NULL	DEFAULT '0',
	`user_id`				int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	`role`					varchar(6)					NOT NULL	DEFAULT '',
	`date_subscribed`		int(11)			unsigned	NOT NULL	DEFAULT '0',
	`date_last_view`		int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	UNIQUE (`thread_id`, `user_id`),
	INDEX (`date_last_view`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `mailbox_thread_posts`
(
	`thread_id`				int(11)			unsigned	NOT NULL	DEFAULT '0',
	`user_id`				int(11)			unsigned	NOT NULL	DEFAULT '0',
	`message`				text						NOT NULL	DEFAULT '',
	
	`post_time`				int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	INDEX (`thread_id`, `post_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
*/

abstract class Mailbox {
	
	
/****** Create a Mailbox Thread ******/
	public static function create
	(
		$users		/* <array> Array of users (ID's, usernames, and emails). */
	)				/* RETURNS <int> : ID of the thread (or 0 on failure). */
	
	// Mailbox::create(array("joe", $bob['id'], "alex", "sam@hotmail.com"))
	{
		$result = Database::query("INSERT INTO `mailbox_thread` (`title`, `last_update`) VALUES (?, ?)", array("", time()));
		
		// If the Mailbox Message Thread was successfully created:
		if($result == true)
		{
			$threadID = Database::getLastID();
			
			// Add Users to the PM Thread
			self::subscribeMultiple($threadID, $users);
			
			return $threadID;
		}
		
		return 0;
	}
	
	
/****** Add Multiple Subscribers to a Mailbox Thread ******/
	public static function subscribeMultiple
	(
		$threadID		/* <int> The ID of the thread you want to add subscribers to. */,
		$users			/* <array> The array of users (ID's, usernames, or emails) you'd like to add. */
	)					/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// Mailbox::subscribeMultiple($threadID, array("joe", $bob['id'], "alex", "sam@hotmail.com"))
	{
		// End the function if the thread doesn't exist
		if(!self::threadExists($threadID)) { return false; }
		
		// Cycle through each username and verify that they exist
		foreach($users as $user)
		{
			$getUser = User::getData($user, "id");
			
			// If the user exists, add them to the $users array
			if(isset($getUser['id']))
			{
				Database::query("INSERT INTO `mailbox_thread_subscribers` (`thread_id`, `user_id`, `role`, `date_subscribed`, `date_last_view`) VALUES (?, ?, ?, ?, ?)", array($threadID, $getUser['id'], "user", time(), 1000000));
			}
		}
		
		return true;
	}
	
	
/****** Add a Subscriber to a Mailbox Thread ******/
	public static function subscribe
	(
		$threadID		/* <int> The ID of the thread to add a subscriber to. */,
		$user			/* <str> The ID, username, or email of the user to subscribe. */
	)					/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Mailbox::subscribe($thread['id'], "joe")
	{
		// End the function if the thread doesn't exist
		if(!self::threadExists($threadID)) { return false;}
		
		$userID = User::toID($user);
		
		if($userID == 0) { return false; }
		
		return Database::query("INSERT INTO `mailbox_thread_subscribers` (`thread_id`, `user_id`, `role`, `date_subscribed`, `date_last_view`) VALUES (?, ?, ?, ?, ?)", array($threadID, $userID, "user", time(), 1000000));
	}
	
	
/****** Add a Post to a Thread ******/
	public static function addPost
	(
		$threadID		/* <int> The ID of the Mailbox Thread you want to add a post to. */,
		$user			/* <str> The ID, username, or email of the user to append the post as. */,
		$message		/* <str> The message you would like to post. */
	)					/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Mailbox::addPost($thread['id'], "joe", "Hey guys, what's up?")
	{
		// End the function if the thread doesn't exist
		if(!self::threadExists($threadID)) { return false;}
		
		// Make sure the user exists
		$getUser = User::getData($user, "id");
		
		if(!isset($getUser['id'])) { return false; }
		
		// Sanitize the Message
		$message = Sanitize::text($message);
		
		// Create the Post
		return Database::query("INSERT INTO `mailbox_thread_posts` (`thread_id`, `user_id`, `message`, `post_time`) VALUES (?, ?, ?, ?)", array($threadID, $getUser['id'], $message, time()));
	}
	
	
/****** Unsubscribe a User from a Mailbox Thread ******/
	public static function unsubscribe
	(
		$threadID		/* <int> The ID of the thread to remove a subscriber from. */,
		$user			/* <str> The ID, username, or email of the user to unsubscribe. */
	)					/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Mailbox::unsubscribe($thread['id'], "joe")
	{
		$userID = User::toID($user);
		
		if($userID == 0) { return false; }
		
		return Database::query("DELETE FROM `mailbox_thread_subscribers` WHERE `thread_id`=? AND `user_id`=? LIMIT 1", array($threadID, $userID));
	}
	
	
/****** Add Subscribers to a Mailbox Thread ******/
	public static function threadExists
	(
		$threadID		/* <int> The ID of the thread to verify exists. */
	)					/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// if(Mailbox::threadExists($thread['id'])) { return "This thread exists."; }
	{
		$threadData = Database::selectOne("SELECT id FROM `mailbox_thread` WHERE id=? LIMIT 1", array($threadID));
		
		if(isset($threadData['id']))
		{
			return true;
		}
		
		return false;
	}
	
	
/****** Delete a Mailbox Thread ******/
	public static function delete
	(
		$threadID		/* <int> The ID of the Mailbox Thread you want to delete. */
	)					/* RETURNS <bool> TRUE on successful, FALSE on failure. */
	
	// Mailbox::delete(15)
	{
		if(!self::threadExists($threadID)) { return false;}
		
		// Remove all posts from the Thread
		Database::query("DELETE FROM `mailbox_thread_posts` WHERE `thread_id`=?", array($threadID));
		
		// Remove all subscribers from the Thread
		Database::query("DELETE FROM `mailbox_thread_subscribers` WHERE `thread_id`=?", array($threadID));
		
		// Delete the Thread
		return Database::query("DELETE FROM `mailbox_thread` WHERE `id`=?", array($threadID));
	}
	
	
/****** Retrieve Subscribers List from a Thread ******/
	public static function getSubscribers
	(
		$threadID		/* <int> The ID of the Mailbox Thread you want to review. */
	)					/* RETURNS <array> List of users subscribed to the thread. */
	
	// Mailbox::getSubscribers(15)
	{
		return Database::selectMultiple("SELECT user_id, role FROM `mailbox_thread_subscribers` WHERE `thread_id`=?", array($threadID));
	}
	
	
/****** Retrieve Posts from a Thread ******/
	public static function getPosts
	(
		$threadID		/* <int> The ID of the Mailbox Thread you want to review. */,
		$count = 10		/* <int> The number of posts you'd like to retrieve. */
	)					/* RETURNS <array> List of users subscribed to the thread. */
	
	// Mailbox::getPosts(15)
	{
		return Database::selectMultiple("SELECT user_id, message, post_time FROM `mailbox_thread_posts` WHERE `thread_id`=? ORDER BY post_time DESC LIMIT 0, " . ($count + 0), array($threadID));
	}
}
