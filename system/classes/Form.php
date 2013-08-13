<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Form Class ******
* This class provides helping functions for form validation and security.
* 
****** How To Use This Class ******

// Validate that the Form was Submitted Successful
if(Form::submit($data)) { echo "This form validated successfully."; }

// Prepare the Form
<form action="./thispage" method="post">
	<?=Form::prepare();?>
	<input type="submit" name="submit" value="submit" />
</form>

****** Methods Available ******
* Form::prepare($uniqueIdentifier = "", $expiresInminutes = 300)	// Prepares hidden tags to protect a form.
* Form::submit($data, $uniqueIdentifier = "")						// Validates if the form submission was successful.
*/

abstract class Form {


/****** Prepare Special Tags for a Form ******/
	public static function prepare
	(
		$uniqueIdentifier = ""		/* <str> You can pass test a unique identifier for form validation. */,
		$expiresInMinutes = 300		/* <int> Duration until the form is no longer valid. (default is 5 hours) */
	)								/* RETURNS <html> : HTML to insert into the form. */
	
	// Form::prepare();
	{
		// Prepare Tags
		$salt = md5(rand(0, 99999999). time() . "fahubaqwads");
		$currentTime = time();
		
		// Test the identifier that makes forms unique to each user
		$uniqueIdentifier .= (defined(SITE_SALT) ? SITE_SALT : "");
		
		if(isset($_SESSION))
		{
			// Add User Agent
			$uniqueIdentifier .= (isset($_SESSION['USER_AGENT']) ? md5($_SESSION['USER_AGENT']) : "");
			
			// Add CSRF Token
			$uniqueIdentifier .= (isset($_SESSION['csrfToken']) ? $_SESSION['csrfToken'] : "");
		}
		
		$hash = Security::quickHash($uniqueIdentifier, $salt, $currentTime, $expiresInMinutes);
		
		// Return the HTML to insert into Form
		return '
		<input type="text" name="tos_soimportant" value="" style="display:none;" />
		<input type="hidden" name="formguard_salt" value="' . $salt . '" />
		<input type="hidden" name="formguard_key" value="' . $currentTime . "-" . $expiresInMinutes . "-" . $hash . '" />
		<input type="text" name="human_answer" value="" style="display:none;" />
		';
	}
	
	
/****** Validate a Form Submission using Special Protection ******/
	public static function submit
	(
		$data					/* <object>
			-> formguard_salt		The random salt used when the form was created.
			-> formguard_key		The resulting hash from preparation.
			-> tos_soimportant		A honeypot. If anything is written here, it's a spam bot. Form fails.
			-> human_answer			A honeypot. If anything is added here, it's a spam bot. Form fails. */,
		$uniqueIdentifier = ""	/* <str> You can specify a unique identifier that the form validation requires. */
	)							/* RETURNS <html> : HTML to insert into the form. */
	
	// Form::submit($data);
	{
		// Make sure all of the right data was sent
		if(isset($data->formguard_key) && isset($data->formguard_salt) && isset($data->tos_soimportant) && isset($data->human_answer))
		{
			// Make sure the honeypots weren't tripped
			if($data->tos_soimportant != "") { return false; }
			if($data->human_answer != "") { return false; }
			
			// Get Important Data
			$keys = explode("-", $data->formguard_key);
			
			// Prepare identifier that will make forms unique to each user
			$uniqueIdentifier .= (defined(SITE_SALT) ? SITE_SALT : "");
			
			if(isset($_SESSION))
			{
				// Add User Agent
				$uniqueIdentifier .= (isset($_SESSION['USER_AGENT']) ? md5($_SESSION['USER_AGENT']) : "");
				
				// Add CSRF Token
				$uniqueIdentifier .= (isset($_SESSION['csrfToken']) ? $_SESSION['csrfToken'] : "");
			}
			
			// Generate the Hash
			$hash = Security::quickHash($uniqueIdentifier, $data->formguard_salt, $keys[0], $keys[1]);
			
			// Make sure the hash was valid
			if($keys[2] == $hash)
			{
				return true;
			}
		}
		
		return false;
	}
}

