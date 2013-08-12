<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Users Controller Class ******
* This class allows you to process common forms that relate to the 'users' data.
* 
****** Methods Available ******
* UserController::registerUser($data, $successRedirect, $autoLogin)		// Handles User Registration
* UserController::login($data, $successRedirect)						// Handles Logging In
* UserController::updateUser($data)										// Updates User Information
* UserController::resetPassword($data)									// Handles Password Reset
* UserController::updatePassword($user, $data)							// Handles Password Update
*/

abstract class UserController {


/****** Register a New User *******/
	public static function registerUser
	(
		$data					/* <class> The POST data that's sent to the form. */,
		$successRedirect = ""	/* <str> A page to redirect to on a successful registration. */,
		$autoLogin = true		/* <bool> If TRUE, automatically logs you in on registration success. */
	)							/* RETURNS <bool> or <redirect> : TRUE or REDIRECT on success, FALSE otherwise. */
	
	// UserController::registerUser($data);
	{
		// Form Submission for Registering a User
		if(isset($data->submit) && isset($data->username) && isset($data->email) && isset($data->password))
		{
			// Validate Username
			if(!isSanitized::variable($data->username, "-."))
			{
				Note::error("Username", "Username has invalid characters.");
			}
			
			else if(!isSanitized::length($data->username, 22))
			{
				Note::error("Username", "Username can only be up to 22 characters.");
			}
			
			else if(strlen($data->username) < 3)
			{
				Note::error("Username", "Username must be at least 3 characters long.");
			}
			
			else if(is_numeric($data->username))
			{
				Note::error("Username", "Username cannot be just a number.");
			}
			
			else if(User::exists($data->username))
			{
				Note::error("Username", "Someone has already taken that username.");
			}
			
			// Validate Email
			if(!isSanitized::email($data->email))
			{
				Note::error("Email", "Please provide a valid email.");
			}
			
			else if(User::getData($data->email, "id") !== array())
			{
				Note::error("Email", "That email has already been taken.");
			}
			
			// Validate Password
			if(strlen($data->password) < 8)
			{
				Note::error("Password", "Your password must be eight characters or more.");
			}
			
			else if(isset($data->confirmation) && $data->password !== $data->confirmation)
			{
				Note::error("Password Confirm", "The password and confirmation don't match.");
			}
			
			// Validate TOS
			if(!isset($data->tos))
			{
				Note::error("Terms of Service", "You must agree to the Terms of Service");
			}
			
			// If the form was valid, register the user
			if(!Note::hasErrors())
			{
				// If user Registration was successful
				if(User::register($data->username, $data->password, $data->email))
				{
					// Get the User ID for details
					$userID = Database::getLastID();
					
					// Send the Email to Reset Password Link
					$link = Confirm::create("confirm-email", $data->username, 24);
					
					$message = 'Hello,

Welcome to Project Starborn! You just registered at our site, so to confirm your email, please visit this link:

' . $link . '

Thanks,
The Project Starborn Team';
					
					// Send an Email
					Email::send($data->email, "Welcome to Project Starborn", $message, "no-reply@projectstarborn.com");
					
					// Add the Standard Newsletter Option (if applicable)
					if(isset($data->newsletter))
					{
						Database::query("UPDATE users SET email_newsletter=? WHERE id=?", array(1, $userID));
					}
					
					// Add the Goodies Newsletter Option (if applicable)
					if(isset($data->goodies))
					{
						Database::query("UPDATE users SET email_goodies=? WHERE id=?", array(1, $userID));
					}
					
					// If you are automatically logging in after registration
					if($autoLogin == true)
					{
						Me::login($data->username, $data->password);
					}
					
					// If you are redirecting to a specific page on a successful registration
					if($successRedirect !== "")
					{
						header("Location: " . $successRedirect); exit;
					}
					
					return true;
				}
			}
		}
			
		return false;
	}
	
	
/****** Login *******/
	public static function login
	(
		$data					/* <class> The POST data that's sent to the form. */,
		$successRedirect = ""	/* <str> The location to redirect to if you successfully login. */
	)							/* RETURNS <bool> or <redirect> : TRUE or REDIRECT on success, FALSE otherwise. */
	
	// UserController::login($data, "./");
	{
		// Form Submission for Logging In
		if(!isset($data->submit)) { return false; }
		
		// Get User Data
		$userData = User::getData(Sanitize::variable($data->login, "@.- +"), "id, password, date_joined");
		
		// Attempt to confirm the username / password
		if(!isset($userData['id'])) { return false; }
		
		if($userData['password'] == Security::getPassword($data->password, $userData['password'], $userData['date_joined']))
		{
			// Login
			Me::login($userData['id'], (isset($data->remember_me) ? true : false));
			
			// Redirect, if auto-login is set up
			if($successRedirect !== "")
			{
				header("Location: " . $successRedirect); exit;
			}
			
			return true;
		}
		
		Note::error("Login", "That user / password combo was unsuccessful.");
		
		return false;
	}
	
	
/****** Update User *******/
	public static function updateUser
	(
		$data					/* <class> The POST data that's sent to the form. */
	)							/* RETURNS <bool> : TRUE on success, FALSE otherwise. */
	
	// UserController::updateUser($data);
	{
		// Form Submission for Updating a User
		if(isset($data->submit) && isset($data->username) && isset($data->email) && isset($data->password) && isset($data->display_name))
		{
			// Still need to adapt this form so that it updates users properly.
			
			// The content below needs to be removed.
			
			
			
			// Validate Username
			if(!isSanitized::variable($data->username, "-."))
			{
				Note::error("Username", "Username has invalid characters.");
			}
			
			else if(!isSanitized::length($data->username, 22))
			{
				Note::error("Username", "Username can only be up to 22 characters.");
			}
			
			else if(strlen($data->username) < 3)
			{
				Note::error("Username", "Username must be at least 3 characters long.");
			}
			
			else if(is_numeric($data->username))
			{
				Note::error("Username", "Username cannot be just a number.");
			}
			
			else if(User::exists($data->username))
			{
				Note::error("Username", "Someone has already taken that username.");
			}
			
			// Validate Email
			if(!isSanitized::email($data->email))
			{
				Note::error("Email", "Please provide a valid email.");
			}
			
			else if(User::emailExists($data->email))
			{
				Note::error("Email", "That email has already been taken.");
			}
			
			// Validate Display Name
			if(!isSanitized::variable($data->display_name, " "))
			{
				Note::error("Display Name", "Please use letters, numbers, spaces and _ -");
			}
			
			else if(trim($data->display_name) != $data->display_name)
			{
				Note::error("Display Name", "Please trim the whitespace from around your display name.");
			}
			
			// Validate Password
			if(strlen($data->password) < 8)
			{
				Note::error("Password", "Your password must be eight characters or more.");
			}
			
			else if($data->password !== $data->confirmation)
			{
				Note::error("Password Confirm", "The password and confirmation don't match.");
			}
			
			// If the form was valid, register the user
			if(!Note::hasErrors())
			{
				User::register($data->username, $data->password, $data->email, $data->display_name, $data->time_zone);
				
				// If you are automatically logging in after registration
				if($autoLogin == true)
				{
					User::login($data->username, $data->password);
				}
				
				// If you are redirecting to a specific page on a successful registration
				if($successRedirect !== "")
				{
					header("Location: " . $successRedirect); exit;
				}
				
				return true;
			}
		}
		
		return false;
	}
	
	
/****** Reset User Password *******/
	public static function resetPassword
	(
		$data			/* <object>
			->login		The username or email of the person whose password we will reset.
			->submit	Checks to make sure you submitted the form. */
			
	)				/* RETURNS <int> or <bool> : Returns User ID on success, FALSE otherwise. */
	
	// UserController::resetPassword("Joe");
	// UserController::resetPassword("joebruiser@hotmail.com");
	{
		// Form Submission for Resetting Password
		if(isset($data->submit) && isset($data->login))
		{
			// Check if User Exists
			$userData = User::getData($data->login, "id, username, email");
			
			if(!isset($userData['id']))
			{
				Note::error("Login", "That username / email does not exist in our records.");
			}
			
			// If the form was valid, register the user
			if(!Note::hasErrors())
			{
				// Send the Email to Reset Password Link
				$expires = 24;
				$link = Confirm::create("reset-password", $userData['username'], $expires, "no-reply@projectstarborn.com");
				
				Email::send($userData['email'], "Password Reset", 'Hello!<br /><br />You are receiving this email because someone has just attempted to reset your password at Project Starborn. You can reset your password by following this link: <a href="' . $link . '">' . $link . '</a><br /><br />Note: this link will expire in ' . $expires . ' hours.<br /><br />Thanks!<br />The Project Starborn Team');
				
				return $userData['id'];
			}
		}
		
		return false;
	}
	
	
/****** Update User Password *******/
	public static function updatePassword
	(
		$user				/* <str> ID, username, or email of the account to update. */,
		$data				/* <object>
			->password		The new password to use.
			->confirm		The password confirmation (if applicable, some forms may not use it)
			->submit		Checks to make sure you submitted the form. */
			
	)				/* RETURNS <bool> : TRUE on success, FALSE otherwise. */
	
	// UserController::updatePassword($username, $data);
	{
		// Form Submission for Updating a Password
		if(isset($data->submit) && isset($data->password))
		{
			// Check if User Exists
			$userData = User::getData($user, "id, username, email");
			
			if(!isset($userData['id']))
			{
				Note::error("Update Password", "That user does not exist in our records.");
			}
			
			// Check if we need a confirmation
			if(isset($data->confirm) && $data->password != $data->confirm)
			{
				Note::error("Update Password", "Your password confirmations don't match.");
			}
			
			// Validate Password
			if(strlen($data->password) < 8)
			{
				Note::error("Update Password", "Your password must be eight characters or more.");
			}
			
			// If the form was valid, register the user
			if(!Note::hasErrors())
			{
				return User::setPassword($userData['username'], $data->password);
			}
		}
		
		return false;
	}
}
