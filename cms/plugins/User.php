<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** User Class ******
* This class sets up and handles the users, including registration, login, passwords, etc.
* 
****** Methods Available ******
* User::getData($user, $columns = "*")				// Retrieves user info based on ID, username, or email.
* User::exists($user)								// Checks if the user exists (by ID, username, or email).
* User::register($username, $password, $email = "")	// Registers a user (email can be optional).
* User::setPassword($username, $password)			// Sets a new password for the user.
* User::setEmail($username, $email)					// Sets the user's email.
* 
****** Database ******

CREATE TABLE IF NOT EXISTS `users`
(
	`id`					smallint(5)		unsigned	NOT NULL	AUTO_INCREMENT,
	
	`role`					varchar(12)					NOT NULL	DEFAULT '',
	`username`				varchar(22)					NOT NULL	DEFAULT '',
	`email`					varchar(48)					NOT NULL	DEFAULT '',
	`password`				varchar(60)					NOT NULL	DEFAULT '',
	
	`is_confirmed`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
	
	`email_newsletter`		tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
	`email_goodies`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
	
	`date_joined`			int(11)			unsigned	NOT NULL	DEFAULT '0',
	`date_lastLogin`		int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	`auth_token`			varchar(22)					NOT NULl	DEFAULT '',
	
	PRIMARY KEY (`id`),
	UNIQUE (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

*/

abstract class User {
	
	
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
	
	
/****** Check if a User Exists ******/
	public static function exists
	(
		$user		/* <str> The ID, username, or email of the account to check exists. */
	)				/* RETURNS <bool> : TRUE if the user exists, FALSE if not. */
	
	// User::exists("Joe")
	// User::exists("joe@unifaction.com")
	{
		$getUser = self::getData($user, "id");
		
		if(isset($getUser['id']))
		{
			return true;
		}
		
		return false;
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
	
	
/****** Set User Password ******/
	public static function setPassword
	(
		$user			/* <str> The ID, username, or email of the account. */,
		$password		/* <str> The password to set (will overwrite the existing password). */
	)					/* RETURNS <bool> : TRUE if password was set, FALSE if something went wrong. */
	
	// User::setPassword("Joe", "myNewPassword")
	{
		$userData = self::getData($user, "id, date_joined");
		
		if(isset($userData['id']))
		{
			$hash = Security::setPassword($password, $userData['date_joined']);
			
			return Database::query("UPDATE `users` SET `password`=? WHERE id=? LIMIT 1", array($hash, $userData['id']));
		}
		
		return false;
	}
	
	
/****** Set User Email ******/
	public static function setEmail
	(
		$user			/* <str> The ID, username, or email of the account. */,
		$email			/* <str> The email to set (will overwrite the existing email). */
	)					/* RETURNS <bool> : TRUE if email was set, FALSE if something went wrong. */
	
	// User::setEmail("Joe", "myNewEmail@gmail.com")
	{
		$userData = self::getData($user, "id");
		
		if(isset($userData['id']))
		{
			return Database::query("UPDATE `users` SET `email`=? WHERE id=? LIMIT 1", array($email, $userData['id']));
		}
		
		return false;
	}
}
