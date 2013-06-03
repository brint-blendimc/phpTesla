<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** User Plugin Class ******
* This plugin sets up and handles a user table, including registration, login, passwords, etc.
* 
****** Dependencies ******
* - "Database" class (for interacting with the user table)
* - "Security" class (for password storage and retrieval)
* 
****** How to use the Plugin ******

User::register("Joe", "myPassword", "joe@hotmail.com");		// Registers the user "Joe"
User::login("Joe", "myPassword");							// Logs "Joe" in (if password is correct)

****** Methods Available ******
* User::createUserTable()							// Creates the user table (this runs if it doesn't exist yet).
* User::exists($username)							// Checks if the user exists.
* User::register($username, $password, $email = "")	// Registers a user (email can be optional).
* User::login($username, $password)					// Login as a user (sets session variable).
* User::logout()									// Logs the current user out.
* User::setPassword($username, $password)			// Sets a new password for the user.
* User::setEmail($username, $email)					// Sets the user's email.
* User::getData($username)							// Retrieves the information from the user (email, join date, etc).
*/

abstract class User {

/****** Prepare Variables ******/
	public static $sql = null;

/****** Initialize the User Plugin ******/
	public static function initialize(
	)	/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// User::initialize()
	{
		// Prepare the global database connection for use within this plugin:
		global $sql;
		self::$sql = $sql;
	}

/****** Create the User Table in the Database ******/
	public static function createUserTable(
	)	/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// User::createUserTable()
	{
		self::$sql->exec("
		CREATE TABLE IF NOT EXISTS `users` (
			`id`					smallint(5)		UNSIGNED	NOT NULL	AUTO_INCREMENT,
			`username`				varchar(22)					NOT NULL	DEFAULT '',
			`email`					varchar(48)					NOT NULL	DEFAULT '',
			`password`				varchar(60)					NOT NULL	DEFAULT '',
			`date_joined`			int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			`date_lastLogin`		int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			PRIMARY KEY (`id`),
			UNIQUE (`username`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
		");
	}

/****** Check if a User Exists ******/
	public static function exists
	(
		$username	/* <str> The username that you want to check exists. */
	)				/* RETURNS <bool> : TRUE if the user exists, FALSE if not. */
	
	// User::exists("Joe")
	{
		$getUser = self::$sql->selectOne("SELECT id FROM users WHERE username=? LIMIT 1", array($username));
		
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
		
		return self::$sql->query("INSERT INTO `users` (`username`, `email`, `password`, `date_joined`) VALUES (?, ?, ?, ?)", array($username, $email, $hash, $dateJoined));
	}

/****** Log In as desired User ******/
	public static function login
	(
		$username			/* <str> The username of the account. */,
		$password			/* <str> The password used to login as the user. */
	)						/* RETURNS <bool> : TRUE if login validation was successful, FALSE if not. */
	
	// User::login("Joe", "myPassword")
	{
		$userData = self::$sql->selectOne("SELECT id, username, password, date_joined FROM users WHERE username=? LIMIT 1", array($username));
		
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
				self::$sql->query("UPDATE `users` SET date_lastLogin=? WHERE id=? LIMIT 1", array(time(), $userData['id']));
				
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
		$username			/* <str> The username of the account. */,
		$password			/* <str> The password to set (will overwrite the existing password). */
	)						/* RETURNS <bool> : TRUE if password was set, FALSE if something went wrong. */
	
	// User::setPassword("Joe", "myNewPassword")
	{
		$userData = self::$sql->selectOne("SELECT id, date_joined FROM users WHERE username=? LIMIT 1", array($username));
		
		if(isset($userData['id']))
		{
			$hash = Security::setPassword($password, $userData['date_joined']);
			
			return self::$sql->query("UPDATE `users` SET `password`=? WHERE id=? LIMIT 1", array($hash, $userData['id']));
		}
		
		return false;
	}
	
/****** Set User Email ******/
	public static function setEmail
	(
		$username			/* <str> The username of the account. */,
		$email				/* <str> The email to set (will overwrite the existing email). */
	)						/* RETURNS <bool> : TRUE if email was set, FALSE if something went wrong. */
	
	// User::setEmail("Joe", "myNewEmail@gmail.com")
	{
		$userData = self::$sql->selectOne("SELECT id FROM users WHERE username=? LIMIT 1", array($username));
		
		if(isset($userData['id']))
		{
			return self::$sql->query("UPDATE `users` SET `email`=? WHERE id=? LIMIT 1", array($email, $userData['id']));
		}
		
		return false;
	}
	
/****** Get User Data ******/
	public static function getData
	(
		$username			/* <str> The username of the account to retrieve. */
	)						/* RETURNS <array> : User Data array if retrieve was successful, Empty array if not. */
	
	// User::getData("Joe")
	{
		$userData = self::$sql->selectOne("SELECT id, username, email, date_joined, date_lastLogin FROM users WHERE username=? LIMIT 1", array($username));
		
		if(isset($userData['id']))
		{
			return $userData;
		}
		
		return array();
	}
}

