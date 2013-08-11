<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Users Controller Class ******
* This class allows you to process common forms that relate to the 'users' data.
* 
****** Methods Available ******
* UserController::registerUser()				// Register a User
* UserController::login()						// Login
*/

class UserController {
	
	
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
			
			else if(User::emailExists($data->email))
			{
				Note::error("Email", "That email has already been taken.");
			}
			
			// Validate Password
			if(strlen($data->password) < 8)
			{
				Note::error("Password", "Your password must be eight characters or more.");
			}
			
			else if(isset($data->confirm) && $data->password !== $data->confirm)
			{
				Note::error("Password", "The password and confirmation don't match.");
			}
			
			// Validate TOS
			if(!isset($data->tos))
			{
				Note::error("Terms of Service", "You must agree to the Terms of Service");
			}
			
			// If the form was valid, register the user
			if(!Note::hasErrors())
			{
				// Now to actually attempt registering the user
				if(User::register($data->username, $data->password, $data->email))
				{
					// Get the User ID for details
					$userID = Database::getLastID();
					
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
	}
	
	
/****** Login *******/
	public static function login
	(
		$data					/* <class> The POST data that's sent to the form. */,
		$successRedirect = ""	/* <str> The location to redirect to if you successfully login. */
	)							/* RETURNS <bool> or <redirect> : TRUE or REDIRECT on success, FALSE otherwise. */
	
	// UserController::login($data);
	{
		// Form Submission for Logging In
		if(isset($data->submit) && isset($data->login) && isset($data->password))
		{
			$userLogin = $data->login;
			
			// If logging in with an email (rather than a username)
			if(strpos($userLogin, "@") > -1)
			{
				// User is logging in with an email, let's change it to ID
				$userLogin = User::getData($userLogin, 'id');
				
				// If the user is invalid, let's stop here.
				if($userLogin === false)
				{
					return false;
				}
				
				// If we're still going, it means $userLogin is the array of user info. Change it to an ID.
				$userLogin = $userLogin['id'];
			}
			
			// Attempt to log in
			if(User::login($userLogin, $data->password))
			{
				// If login was successful & you're redirecting to a specific page
				if($successRedirect !== "")
				{
					header("Location: " . $successRedirect); exit;
				}
				
				return true;
			}
			
			// If login failed, provide an error message
			Note::error("Login", "Login was unsuccessful.");
			
			return false;
		}
	}
	
}

