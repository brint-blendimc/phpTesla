<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** User Plugin Class ******
* This plugin sets up and handles a user table, including registration, login, passwords, etc.
* 
****** Methods Available ******
* $plugin->
* 	users->createTables()								// Creates the user table (this runs if it doesn't exist yet).
* 	users->exists($username)							// Checks if the user exists.
* 	users->register($username, $password, $email = "")	// Registers a user (email can be optional).
* 	users->login($username, $password)					// Login as a user (sets session variable).
* 	users->logout()										// Logs the current user out.
* 	users->setPassword($username, $password)			// Sets a new password for the user.
* 	users->setEmail($username, $email)					// Sets the user's email.
* 	users->getData($user)								// Retrieves the info from the user (email, join date, etc).
*/

class UsersPlugin {


/****** Important Values ******/
	public $clearance = null;
	public $controller = null;

	
/****** Initializer ******/
	function __construct()
	{
		// Extend all of the Task Classes
		require_once(BASE_DIR . "/plugins/users/class_UsersClearance.php");
		require_once(BASE_DIR . "/plugins/users/class_UsersController.php");
		
		$this->clearance = new UsersClearance();
		$this->controller = new UsersController();
	}
	
	
/****** Create the User Table in the Database ******/
	public static function createTables(
	)	/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// $plugin->users->createTables()
	{
		Database::exec("
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
	
	// $plugin->users->exists("Joe")
	{
		$getUser = Database::selectOne("SELECT id FROM users WHERE username=? LIMIT 1", array($username));
		
		if(isset($getUser['id']))
		{
			return true;
		}
		
		return false;
	}

	
/****** Check if Email was Taken ******/
	public static function emailExists
	(
		$email		/* <str> The email that you want to check exists. */
	)				/* RETURNS <bool> : TRUE if the email is taken, FALSE if not. */
	
	// $plugin->users->emailExists("joe@hotmail.com")
	{
		$getEmail = Database::selectOne("SELECT id FROM users WHERE email=? LIMIT 1", array($email));
		
		if(isset($getEmail['id']))
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
	
	// $plugin->users->register("Joe", "myPassword", "joe@hotmail.com")
	{
		$dateJoined = time();
		$hash = Security::setPassword($password, $dateJoined);
		
		return Database::query("INSERT INTO `users` (`username`, `email`, `password`, `date_joined`) VALUES (?, ?, ?, ?)", array($username, $email, $hash, $dateJoined));
	}

/****** Log In as desired User ******/
	public static function login
	(
		$username			/* <str> The username of the account. */,
		$password			/* <str> The password used to login as the user. */
	)						/* RETURNS <bool> : TRUE if login validation was successful, FALSE if not. */
	
	// $plugin->users->login("Joe", "myPassword")
	{
		$userData = Database::selectOne("SELECT id, username, password, date_joined FROM users WHERE username=? LIMIT 1", array($username));
		
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
	
	// $plugin->users->logout()
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
	
	// $plugin->users->setPassword("Joe", "myNewPassword")
	{
		$userData = Database::selectOne("SELECT id, date_joined FROM users WHERE username=? LIMIT 1", array($username));
		
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
		$username			/* <str> The username of the account. */,
		$email				/* <str> The email to set (will overwrite the existing email). */
	)						/* RETURNS <bool> : TRUE if email was set, FALSE if something went wrong. */
	
	// $plugin->users->setEmail("Joe", "myNewEmail@gmail.com")
	{
		$userData = Database::selectOne("SELECT id FROM users WHERE username=? LIMIT 1", array($username));
		
		if(isset($userData['id']))
		{
			return Database::query("UPDATE `users` SET `email`=? WHERE id=? LIMIT 1", array($email, $userData['id']));
		}
		
		return false;
	}
	
/****** Get User Data ******/
	public static function getData
	(
		$user			/* <str> The username or User ID of the account to retrieve. */
	)					/* RETURNS <array> : User Data array if retrieve was successful, Empty array if not. */
	
	// $plugin->users->getData("Joe")
	{
		$userData = Database::selectOne("SELECT id, username, email, date_joined, date_lastLogin FROM users WHERE " . (is_numeric($user) ? 'id' : 'username') . "=? LIMIT 1", array($user));
		
		if(isset($userData['id']))
		{
			return $userData;
		}
		
		return array();
	}
}

