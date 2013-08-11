<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** User Plugin Class ******
* This plugin sets up and handles users, including registration, login, passwords, etc.
* 
****** Methods Available ******
* User::createTables()								// Creates the user table (this runs if it doesn't exist yet).
* User::exists($username)							// Checks if the user exists.
* User::register($username, $password, $email = "")	// Registers a user (email can be optional).
* User::login($username, $password)					// Login as a user (sets session variable).
* User::logout()									// Logs the current user out.
* User::setPassword($username, $password)			// Sets a new password for the user.
* User::setEmail($username, $email)					// Sets the user's email.
* User::getData($user, $tables = "*")				// Retrieves the info from the user by: ID, Username, or Email
* 
****** Database ******

CREATE TABLE IF NOT EXISTS `users`
(
	`id`					smallint(5)		unsigned	NOT NULL	AUTO_INCREMENT,
	
	`role`					varchar(12)					NOT NULL	DEFAULT '',
	`username`				varchar(22)					NOT NULL	DEFAULT '',
	`email`					varchar(48)					NOT NULL	DEFAULT '',
	`password`				varchar(60)					NOT NULL	DEFAULT '',
	
	`is_confirmed`			tinyint(1)		unsigned	NOT NULL	DEFAULT '',
	
	`email_newsletter`		tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
	`email_goodies`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
	
	`date_joined`			int(11)			unsigned	NOT NULL	DEFAULT '0',
	`date_lastLogin`		int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	PRIMARY KEY (`id`),
	UNIQUE (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

*/

abstract class User {
	
	
/****** Check if User Exists ******/
	public static function exists
	(
		$user		/* <int> or <str> The ID or username of the user that you want to check exists. */
	)				/* RETURNS <bool> : TRUE if the user exists, FALSE if not. */
	
	// User::exists("Joe")
	{
		$getUser = Database::selectOne("SELECT id FROM users WHERE `" . (is_numeric($user) ? "id" : "username") . "`=? LIMIT 1", array($user));
		
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
		$email = ""			/* <str> The email you want to register with. Leave empty for no email required. */,
		$role = "member"	/* <str> The role that the user is registering as. */
	)						/* RETURNS <bool> : TRUE if user created, FALSE if not. */
	
	// User::register("Joe", "myPassword", "joe@hotmail.com")
	{
		$dateJoined = time();
		$hash = Security::setPassword($password, $dateJoined);
		
		return Database::query("INSERT INTO `users` (`username`, `email`, `password`, `role`, `date_joined`) VALUES (?, ?, ?, ?, ?)", array($username, $email, $hash, $role, $dateJoined));
	}
	
	
/****** Log In as User ******/
	public static function login
	(
		$user				/* <int> or <str> The ID or username of the account. */,
		$password			/* <str> The password used to login as the user. */
	)						/* RETURNS <bool> : TRUE if login validation was successful, FALSE if not. */
	
	// User::login("Joe", "myPassword")
	{
		$userData = Database::selectOne("SELECT id, username, password, date_joined FROM users WHERE `" . (is_numeric($user) ? "id" : "username") . "`=? LIMIT 1", array($user));
		
		// If the user exists and the data was returned properly:
		if(isset($userData['id']))
		{
			if($userData['password'] == Security::getPassword($password, $userData['password'], $userData['date_joined']))
			{
				// Prepare User Session
				$_SESSION[USER_SESSION] = array(
						"id"			=> $userData['id']
					);
				
				// Update the last login time to right now:
				Database::query("UPDATE `users` SET date_lastLogin=? WHERE id=? LIMIT 1", array(time(), $userData['id']));
				
				return true;
			}
		}
		
		return false;
	}
	
	
/****** Log Out ******/
	public static function logout()
		/* RETURNS <bool> : TRUE after removing all login sessions and cookies. */
	
	// User::logout()
	{
		unset($_SESSION[USER_SESSION]);
		
		return true;
	}
	
	
/****** Set User Password ******/
	public static function setPassword
	(
		$user				/* <int> or <str> The ID or username of the account. */,
		$password			/* <str> The password to set (will overwrite the existing password). */
	)						/* RETURNS <bool> : TRUE if password was set, FALSE if something went wrong. */
	
	// User::setPassword("Joe", "myNewPassword")
	{
		$userData = Database::selectOne("SELECT id, date_joined FROM users WHERE `" . (is_numeric($user) ? "id" : "username") . "`=? LIMIT 1", array($user));
		
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
		$user				/* <int> or <str> The ID or username of the account. */,
		$email				/* <str> The email to set (will overwrite the existing email). */
	)						/* RETURNS <bool> : TRUE if email was set, FALSE if something went wrong. */
	
	// User::setEmail("Joe", "myNewEmail@gmail.com")
	{
		$userData = Database::selectOne("SELECT id FROM users WHERE username=? LIMIT 1", array($user));
		
		if(isset($userData['id']))
		{
			return Database::query("UPDATE `users` SET `email`=? WHERE id=? LIMIT 1", array($email, $userData['id']));
		}
		
		return false;
	}
	
	
/****** Get User Data ******/
	public static function getData
	(
		$user			/* <int> or <str> The ID, username, or email of the account to retrieve. */,
		$tables = "*"	/* <str> The rows you'd like to retrieve. */
	)					/* RETURNS <array> : User data array if retrieve was successful, FALSE on failure. */
	
	// $userData = User::getData(5)
	// $userData = User::getData("Joe")
	// $userData = User::getData("joe@hotmail.com")
	{
		return Database::selectOne("SELECT " . Sanitize::variable($tables, "`,") . " FROM users WHERE `" . (is_numeric($user) ? 'id' : (strpos($user, "@") > -1 ? 'email' : 'username')) . "`=? LIMIT 1", array($user));
	}
	
	
}

