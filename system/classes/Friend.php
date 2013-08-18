<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Friend Class ******
* This class allows users to manage their friends.
* 
****** Methods Available ******
* Friend::show($user)						// Returns all of your friends in an array.
* Friend::add($user, $friend)				// Adds a friend.
* Friend::remove($user, $friend)			// Removes a friend.
* 
****** Database ******

CREATE TABLE IF NOT EXISTS `friends`
(
	`user_id`				int(11)			unsigned	NOT NULL	DEFAULT '0',
	`friend_id`				int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	UNIQUE (`user_id`, `friend_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

*/

abstract class Friend {
	
	
/****** Show Friends ******/
	public static function show
	(
		$user				/* <str> The ID, username, or email of the user to show friends from. */
	)						/* RETURNS <bool> */
	
	// Friend::add("joe", "bob")
	{
		// Retrieve User ID
		$userID = User::toID($user);
		
		// Add the friend to the group
		return Database::selectMultiple("SELECT `friend_id` as id FROM friends WHERE user_id=?", array($userID));
	}
	
	
/****** Add Friend ******/
	public static function add
	(
		$user				/* <str> The ID, username, or email of the user to add a friend to. */,
		$friend				/* <str> The ID, username, or email of the friend account. */
	)						/* RETURNS <bool> */
	
	// Friend::add("joe", "bob")
	{
		// Retrieve User & Friend's Data
		$userID = User::toID($user);
		$friendID = User::toID($friend);
		
		if($userID == $friendID) { return false; }
		
		// Add the friend to your friends list
		return Database::query("INSERT INTO `friends` (`user_id`, `friend_id`) VALUES (?, ?)", array($userID, $friendID));
	}
	
	
/****** Remove Friend ******/
	public static function remove
	(
		$user				/* <str> The ID, username, or email of the user to add a friend to. */,
		$friend				/* <str> The ID, username, or email of the friend account. */
	)						/* RETURNS <bool> */
	
	// Friend::remove("joe", "bob")
	{
		// Retrieve User & Friend's Data
		$userID = User::toID($user);
		$friendID = User::toID($friend);
		
		if($userID == $friendID) { return false; }
		
		// Remove the friend from your friends list
		return Database::query("DELETE FROM `friends` WHERE `user_id`=? AND `friend_id`=? LIMIT 1", array($userID, $friendID));
	}
	
}
