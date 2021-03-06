<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** User Class ******
* This class sets up and handles the users, including registration, login, passwords, etc.
* 
****** Methods Available ******
* User::toID($user)									// Translates ID, username, or email to ID (optimized)
* User::getData($user, $columns = "*")				// Retrieves & verifies user info based on ID, username, or email.
* User::register($username, $password, $email = "")	// Registers a user (email can be optional).
* 
****** Database ******

CREATE TABLE IF NOT EXISTS `users`
(
	`id`					int(11)			unsigned	NOT NULL	AUTO_INCREMENT,
	
	`role`					varchar(12)					NOT NULL	DEFAULT '',
	`username`				varchar(22)					NOT NULL	DEFAULT '',
	`email`					varchar(48)					NOT NULL	DEFAULT '',
	`password`				varchar(90)					NOT NULL	DEFAULT '',
	
	`is_confirmed`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
	
	`email_newsletter`		tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
	`email_goodies`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
	
	`date_joined`			int(11)			unsigned	NOT NULL	DEFAULT '0',
	`date_lastLogin`		int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	`auth_token`			varchar(22)					NOT NULl	DEFAULT '',
	
	PRIMARY KEY (`id`),
	UNIQUE (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

*/

abstract class User {
	
	
/****** Get User Data ******
This is a faster, more optimized way of returning a user ID, though it will not verify the ID if you send an integer.
If you need to validate the user ID (confirm it exists), you should use User::getData($user, "id") instead. */
	public static function toID
	(
		$user					/* <str> The ID, username, or email of the account to get the ID of. */
	)							/* RETURNS <int> : ID of the user (may be unsuccessful). */
	
	// User::toID(125)					// Returns 125 (optimized)
	// User::toID("Joe")				// Returns 125
	// User::toID("joe@hotmail.com")	// Returns 125
	{
		// Return the value normally if it's an integer
		if(is_numeric($user))
		{
			return ($user + 0);
		}
		
		// Get User Data
		$getUser = self::getData($user, "id");
		
		if(isset($getUser['id'])) { return $getUser['id']; }
		
		return 0;
	}
	
	
/****** Get User Data ******/
	public static function getData
	(
		$user			/* <str> The ID, username, or email the account to retrieve. */,
		$columns = "*"	/* <str> The columns you want to retrieve from the users database. */
	)					/* RETURNS <array> : User Data array if retrieve was successful, empty array if not. */
	
	// User::getData(125)
	// User::getData("Joe")
	// User::getData("joe@hotmail.com")
	{
		// Gather User Data through the Database
		$userData = Database::selectOne("SELECT " . Sanitize::variable($columns, " ,-*`") . " FROM users WHERE " . (is_numeric($user) ? 'id' : (strpos($user, "@") > - 1 ? 'email' : 'username')) . "=? LIMIT 1", array($user));
		
		if(isset($userData['id']))
		{
			return $userData;
		}
		
		return array();
	}
	
	
/****** Register a User ******/
	public static function register
	(
		$username			/* <str> The username of the account. */,
		$password			/* <str> The password you'd like to associate with your account. */,
		$email = ""			/* <str> The email you want to register with. Leave empty for no email required. */
	)						/* RETURNS <bool> : TRUE if user created, FALSE if not. */
	
	// User::register("Joe", "myPassword", "joe@hotmail.com")
	{
		$dateJoined = time();
		$hash = Security::setPassword($password, $dateJoined);
		
		return Database::query("INSERT INTO `users` (`username`, `role`, `email`, `password`, `date_joined`) VALUES (?, ?, ?, ?, ?)", array($username, "user", $email, $hash, $dateJoined));
	}
}
