<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Circles Class ******
* This class allows users to set up and manage friend circles.
* 
****** Methods Available ******
* FriendCircles::show($user, $circleID = 0)					// Shows your friends (can also filter by circle)
* FriendCircles::getID($user, $circleName)					// Get a Circle ID by Name.
* FriendCircles::getAll($user)								// Return an array of all the circles a user has.
* FriendCircles::create($user, $circleName)					// Creates a customized friend circle.
* FriendCircles::rename($circleID, $circleName)				// Rename a circle.
* FriendCircles::addFriend($user, $friend, $circleName)		// Adds a friend to a friend circle.
* FriendCircles::removeFriend($circleID, $friend_id)		// Removes a friend from a circle.
* 
* FriendCircles::delete($circleID)					// Deletes a circle and all friends in it.
* FriendCircles::delete($user, $circleName)			// Deletes a circle and all friends in it.
* 
* FriendCircles::count($userID)						// Returns the number of custom friend circles a user has created.
* 
****** Database ******

CREATE TABLE IF NOT EXISTS `friend_circles`
(
	`id`					int(11)			unsigned	NOT NULL	AUTO_INCREMENT,
	`user_id`				int(11)			unsigned	NOT NULL	DEFAULT '0',
	`circle_name`			varchar(20)					NOT NULL	DEFAULT '',
	
	PRIMARY KEY (`id`),
	UNIQUE (`user_id`, `circle_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `friend_circles_list`
(
	`circle_id`			int(11)			unsigned	NOT NULL	DEFAULT '0',
	`friend_id`			int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	UNIQUE (`circle_id`, `friend_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

*/

abstract class FriendCircles {
	
	
/****** Retrieve a Circle ID with User and Circle Name ******/
	public static function getID
	(
		$user			/* <str> The ID, username, or email of the user to test. */,
		$circleName		/* <str> The name of the circle to retrieve. */
	)					/* RETURNS <int> The ID of the circle. 0 if none. */
	
	// $circleID = FriendCircles::getID("joe", "Business Contacts");
	{
		// Get User
		$userID = User::toID($user);
		
		// Run the update
		$data = Database::selectOne("SELECT id FROM `friend_circles` WHERE `user_id`=? AND `circle_name`=? LIMIT 1", array($userID, $circleName));
		
		if(isset($data['id'])) { return $data['id']; }
		
		return 0;
	}
	
	
/****** Retrieve All of a User's Circles ******/
	public static function getAll
	(
		$user			/* <str> The ID, username, or email of the user to test. */
	)					/* RETURNS <array> A complete list of the user's circles. */
	
	// $circles = FriendCircles::getAll("joe");
	{
		// Get User
		$userID = User::toID($user);
		
		// Run the update
		return Database::selectMultiple("SELECT id, circle_name FROM `friend_circles` WHERE `user_id`=?", array($userID));
	}
	
	
/****** Add Friend Circle ******/
	public static function create
	(
		$user			/* <str> The ID, username, or email of the account to add a friend to. */,
		$circleName		/* <str> The circle name to create. */
	)					/* RETURNS <void> */
	
	// FriendCircles::create("joe", "Business Contacts")
	{
		// Prepare Circle Name
		$circleName = Sanitize::variable($circleName, " ");
		
		if(is_numeric($circleName) == true) { $circleName = "Circle #" . $circleName; }
		if($circleName == "") { return false; }
		
		if(strlen($circleName) > 20)
		{
			Note::error("Friend Circle", "Your circle name can only be 20 characters long."); return false;
		}
		
		// Get the User ID
		$getUser = User::getData($user, "id");
		
		if(!isset($getUser['id'])) { return false; }
		
		// Make sure the user doesn't have too many 
		if(self::count($getUser['id']) > 6)
		{
			Note::error("Friend Circle", "You already have six friend circles."); return false;
		}
		
		// If the circle already exists, end here
		if(self::getID($getUser['id'], $circleName) !== 0)
		{
			Note::error("Friend Circle", "That circle already exists."); return false;
		}
		
		// Add the friend circle
		return Database::query("INSERT INTO `friend_circles` (`user_id`, `circle_name`) VALUES (?, ?)", array($getUser['id'], $circleName));
	}
	
	
/****** Rename Friend Circle ******/
	public static function rename
	(
		$circleID		/* <str> The ID of the circle you want to rename. */,
		$circleName		/* <str> The new circle name to use. */
	)					/* RETURNS <void> */
	
	// FriendCircles::rename(215, "Personal")
	{
		// Prepare Circle Name
		$circleName = Sanitize::variable($circleName, " ");
		
		if(is_numeric($circleName) == true) { $circleName = "Circle #" . $circleName; }
		if($circleName == "") { return false; }
		
		if(strlen($circleName) > 20)
		{
			Note::error("Friend Circle", "Your circle name can only be 20 characters long."); return false;
		}
		
		// Run the update
		return Database::query("UPDATE `friend_circles` SET `circle_name`=? WHERE id=?", array($circleName, $circleID));
	}
	
	
/****** Delete Friend Circle ******/
	public static function delete (/* 
		
		Method #1:		delete($circleID)
		Method #2:		delete($user, $circleName)
		
	*/)					/* RETURNS <void> */
	
	// FriendCircles::delete(215)
	// FriendCircles::delete("joe", "Business Contacts");
	{
		$args = func_get_args();
		
		// Method #1: Delete by ID
		if(count($args) == 1 && is_numeric($args[0]))
		{
			return self::deleteByID($args[0] + 0);
		}
		
		// Method #2: Delete by User and Circle Name
		else if(count($args) == 2)
		{
			$userID = User::toID($args[0]);
			
			// Confirm that the circle exists
			$circleID = self::getID($userID, $args[1]);
			if($circleID == 0) { return false; }
			
			// Delete all friends in the circle
			Database::query("DELETE FROM friends_list WHERE `circle_id`=?", array($circleID));
			
			// Delete the circle
			return Database::query("DELETE FROM `friend_circles` WHERE id=?", array($circleID));
		}
		
		return false;
	}
	
	
/****** Add Friend ******/
	public static function addFriend
	(
		$user				/* <str> The ID, username, or email of the user to add a friend to. */,
		$friend				/* <str> The ID, username, or email of the friend account. */,
		$circleName = ""	/* <str> The name of the circle to add the friend to. */
	)						/* RETURNS <bool> */
	
	// FriendCircles::addFriend("joe", "bob", "Business Contacts")
	{
		// Retrieve User & Friend's Data
		$userData = User::getData($user, "id");
		$friendData = User::getData($friend, "id");
		
		// Make sure the user & friend exist
		if(!isset($userData['id'])) { return false; }
		if(!isset($friendData['id'])) { return false; }
		if($userData['id'] == $friendData['id']) { return false; }
		
		// Check Circle
		$circleID = self::getID($userData['id'], $circleName);
		if($circleID == 0) { return false; }
		
		// Add the friend to the circle
		return Database::query("INSERT INTO `friend_circles_list` (`circle_id`, `friend_id`) VALUES (?, ?)", array($circleID, $friendData['id']));
	}
	
	
/****** Remove a Friend from a Circle ******/
	public static function removeFriend
	(
		$circleID			/* <int> The ID of the circle to remove the friend from. */,
		$friendID			/* <int> The ID of the friend to remove. */
	)						/* RETURNS <bool> */
	
	// FriendCircles::removeFriend($circle['id'], $friend['id'])
	{
		return Database::query("DELETE FROM `friend_circles_list` WHERE circle_id=? AND friend_id=? LIMIT 1", array($circleID, $friendID));
	}
	
	
/****** Check number of custom Friend Circle created by User ******/
	private static function count
	(
		$userID			/* <str> The ID of the user to check the friend circle count. */
	)					/* RETURNS <int> The number of the custom circles the user has. */
	
	// self::count(15)
	{
		$circles = Database::selectOne("SELECT COUNT(id) as totalNum FROM `friend_circles` WHERE user_id=?", array($userID));
		
		if(isset($circles['totalNum']))
		{
			return $circles['totalNum'];
		}
		
		return 0;
	}
	
	
/****** Delete Friend Circle (by ID) ******/
	private static function deleteByID
	(
		$circleID		/* <str> The ID of the circle you want to delete. */
	)					/* RETURNS <void> */
	
	// FriendCircles::deleteByID(215)
	{
		// Confirm that the circle exists
		$circleData = Database::selectOne("SELECT id FROM `friend_circles` WHERE `id`=? LIMIT 1", array($circleID));
		
		if(!isset($circleData['id'])) { return false; }
		
		// Delete all friends in the circle
		Database::query("DELETE FROM friends_list WHERE `circle_id`=?", array($circleData['id']));
		
		// Delete the circle
		return Database::query("DELETE FROM `friend_circles` WHERE id=?", array($circleData['id']));
	}
}
