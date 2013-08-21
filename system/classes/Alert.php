<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Alert Class ******
* This class allows users to get alerts (like a news feed or status updates).
* 
****** Methods Available ******
* Alert::get($user, $count = 3)				// Retrieves the most recent alerts for a user.
* 
* Alert::create($user, $alert, $image = "", $link = "")		// Creates an Alert for a particular User
* Alert::delete($alertID)									// Delete an Alert
* Alert::deleteAll($user)									// Deletes all of a user's alerts
* 
* Alert::updateGlobalFeed($user)										// Updates user with global alerts
* Alert::createGlobal($alertType, $alert, $image = "", $link = "")		// Creates a Global Alert
* Alert::deleteGlobal($alertID)											// Delete a Global Alert
* 
****** Database ******

CREATE TABLE IF NOT EXISTS `alerts_personal`
(
	`id`					int(11)			unsigned	NOT NULL	AUTO_INCREMENT,
	`user_id`				int(11)			unsigned	NOT NULL	DEFAULT '0',
	`alert`					text						NOT NULL	DEFAULT '',
	`image`					varchar(72)					NOT NULL	DEFAULT '',
	`link`					varchar(72)					NOT NULL	DEFAULT '',
	`time_posted`			int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	PRIMARY KEY (`id`),
	INDEX (`user_id`, `time_posted`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `alerts_global`
(
	`id`					int(11)			unsigned	NOT NULL	DEFAULT '0',
	`type`					varchar(12)					NOT NULL	DEFAULT '',
	`alert`					text						NOT NULL	DEFAULT '',
	`image`					varchar(72)					NOT NULL	DEFAULT '',
	`link`					varchar(72)					NOT NULL	DEFAULT '',
	`time_posted`			int(11)			unsigned	NOT NULL	DEFAULT '0',
	`time_end`				int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	INDEX (`type`, `time_posted`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

*/

abstract class Alert {
	
	
/****** Retrieve Most Recent Alerts ******/
	public static function get
	(
		$user			/* <str> The ID, username, or email of the user to create an alert for. */,
		$count = 3		/* <int> The number of alerts to retrieve. */
	)					/* RETURNS <array> : The list of most recent alerts. */
	
	// Alert::get("bob", 5)		// Gets the last five alerts for "bob"
	{
		$userID = User::toID($user);
		
		// Retrieve the Alerts
		return Database::selectMultiple("SELECT alert, image, link, time_posted FROM alerts_personal WHERE user_id=? ORDER BY time_posted DESC LIMIT 0, " . ($count + 0), array($userID));
	}
	
	
/****** Create an Alert (Personal) ******/
	public static function create
	(
		$user			/* <str> The ID, username, or email of the user to create an alert for.
						or <array> An array of users, each of which will recieve the alert. */,
						
		$alert			/* <str> The alert message (or HTML). */,
		$image = ""		/* <str> The path to the image URL. */,
		$link = ""		/* <str> The URL to use for the alert. */
	)					/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// Alert::create("bob", "You've received a message from <a href='./joe'>Joe!</a>", "/icons/pm.png", "/messages")
	// Alert::create($userList, "You've received a message from <a href='./joe'>Joe!</a>", "/icons/pm.png", "/messages")
	{
		if(is_array($user)) { return self::createMultiple($user, $alert, $image, $link); }
		
		// Verify User
		$userData = User::getData($user, "id");
		
		if(isset($userData['id']))
		{
			// Create the Alert
			return Database::query("INSERT INTO `alerts_personal` (`user_id`, `alert`, `image`, `link`, `time_posted`) VALUES (?, ?, ?, ?, ?)", array($userData['id'], $alert, $image, $link, time()));
		}
		
		return false;
	}
	
	
/****** Delete an Alert (Personal) ******/
	public static function delete
	(
		$alertID		/* <int> The ID of the alert to delete. */
	)					/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// Alert::delete(15)
	{
		return Database::query("DELETE FROM `alerts_personal` WHERE id=? LIMIT 1", array($alertID));
	}
	
	
/****** Deletes all of a User's Alerts ******/
	public static function deleteAll
	(
		$user		/* <int> The ID, username, or email of a user. */
	)				/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// Alert::deleteAll("bob")
	{
		$userID = User::toID($user);
		
		return Database::query("DELETE FROM `alerts_personal` WHERE `user_id`=?", array($userID));
	}
	
	
/****** Create Multiple Alert (for a list of users) ******/
	private static function createMultiple
	(
		$userList		/* <array> An array of users, each of which will recieve the alert. */,
		$alert			/* <str> The alert message (or HTML). */,
		$image = ""		/* <str> The path to the image URL. */,
		$link = ""		/* <str> The URL to use for the alert. */
	)					/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// self::createMultiple($userList, "You've received a message from <a href='./joe'>Joe!</a>", "/icons/pm.png", "/messages")
	{
		// Prepare Values
		$sqlArray = array();
		$sqlValues = "";
		$time = time();
		
		foreach($userList as $user)
		{
			// Verify User
			$userData = User::getData($user, "id");
			
			if(isset($userData['id']))
			{
				// The user exists, so add in the details here:
				$sqlValues .= ($sqlValues != "" ? ", " : "") . "(?, ?, ?, ?, ?)";
				array_push($sqlArray, $userData['id'], $alert, $image, $link, $time);
			}
		}
		
		// If no users existed or SQL wasn't valid, end the function here
		if($sqlValues == "") { return false; }
		
		// Create the Alert
		return Database::query("INSERT INTO `alerts_personal` (`user_id`, `alert`, `image`, `link`, `time_posted`) VALUES " . $sqlValues, $sqlArray);
	}
}
