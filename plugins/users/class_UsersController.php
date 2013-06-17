<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }
		
/****** Users Controller Class ******
* This class allows you to process common forms that relate to the 'users' data.
* 
****** Methods Available ******
* $plugin->users->
*	controller->simpleValidate()					// Simple validation of values that may be used in these forms.
*/

class UsersController {
	

/****** Register a New User *******/
	public static function registerUser
	(
		$data					/* <class> The POST data that's sent to the form. */,
		$successRedirect = ""	/* <str> A page to redirect to on a successful registration. */,
		$autoLogin = true		/* <bool> If TRUE, automatically logs you in on registration success. */
	)							/* RETURNS <bool> or <redirect> : TRUE or REDIRECT on success, FALSE otherwise. */
	
	// $plugin->users->controller->registerUser($data);
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
			
			else if(UsersPlugin::exists($data->username))
			{
				Note::error("Username", "Someone has already taken that username.");
			}
			
			// Validate Email
			if(!isSanitized::email($data->email))
			{
				Note::error("Email", "Please provide a valid email.");
			}
			
			else if(UsersPlugin::emailExists($data->email))
			{
				Note::error("Email", "That email has already been taken.");
			}
			
			// Validate Password
			if(strlen($data->password) < 8)
			{
				Note::error("Password", "Your password must be eight characters or more.");
			}
			
			else if($data->password !== $data->confirm)
			{
				Note::error("Password", "The password and confirmation don't match.");
			}
			
			// If the form was valid, register the user
			if(!Note::hasErrors())
			{
				UsersPlugin::register($data->username, $data->password, $data->email);
				
				// If you are automatically logging in after registration
				if($autoLogin == true)
				{
					UsersPlugin::login($data->username, $data->password);
				}
				
				// If you are redirecting to a specific page on a successful registration
				if($successRedirect !== "")
				{
					header("Location: " . $successRedirect); exit;
				}
				
				return true;
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
	
	// $plugin->users->controller->login($data);
	{
		// Form Submission for Logging In
		if(isset($data->submit))
		{
			// Validate Username
			$data->username = Sanitize::variable($data->username, "-.");
			
			if(!isSanitized::length($data->username, 22))
			{
				Note::error("Username", "Username can only be up to 22 characters.");
			}
			
			else if(is_numeric($data->username))
			{
				Note::error("Username", "Username cannot be just a number.");
			}
			
			// If the form was valid, attempt to log in
			if(!Note::hasErrors())
			{
				if(!UsersPlugin::login($data->username, $data->password))
				{
					Note::error("Login", "Login was unsuccessful.");
				}
				
				// If login was successful and you're redirecting to a specific page:
				if(!Note::hasErrors())
				{
					if($successRedirect !== "")
					{
						header("Location: " . $successRedirect); exit;
					}
				}
				
				return true;
			}
			
			return false;
		}
	}
}

