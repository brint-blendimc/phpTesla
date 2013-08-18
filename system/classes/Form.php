<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Form Generation Class ******
* This class provides form generation and validation.
* 
****** How To Use This Class ******

// Prepare Form Information
$form = new GenerateForm("register");
$form->text("username", "Username");
$form->password("password", "Password");
$form->select("password_hint", "Password Hint")
	->selectOption("mothers_name", "Mother's Maiden Name", true)
	->selectOption("favorite_color", "Your Favorite Color")
	->selectOption("highschool_maskot", "Your High School Maskot");
$form->radio("gender", "Gender")
	->radioOption("male", "Male", true)
	->radioOption("female", "Female");
$form->submit("Submit", "Submit");

// Form Validation
if($form->success())
{
	echo "The test has passed successfully!";
}

// Display the Form
$form->generate("./register");

****** Methods Available ******
* $form->prepare($uniqueIdentifier = "", $expiresInminutes = 300)	// Prepares hidden tags to protect a form.
* $form->success($uniqueIdentifier = "")							// Validates if the form submission was successful.
*/

class Form {
	
	
/****** Variables ******/
	public $formName = "";
	public $input = array();
	public $options = array();
	public $lastInputName = "";
	public $data = null;
	
	
/****** Create a Text Input ******/
	function __construct
	(
		$name = "main"			/* Name of the form. */
	)							/* RETURNS <void> */
	
	// $form = new GenerateForm("register");
	{
		global $data;
		
		$this->formName = strtolower(Sanitize::variable($name));
		$this->data = $data;
	}
	
	
/****** Prepare Special Tags for a Form ******/
	public function prepare
	(
		$uniqueIdentifier = ""		/* <str> You can pass test a unique identifier for form validation. */,
		$expiresInMinutes = 300		/* <int> Duration until the form is no longer valid. (default is 5 hours) */
	)								/* RETURNS <html> : HTML to insert into the form. */
	
	// $form->prepare();
	{
		// Prepare Tags
		$salt = md5(rand(0, 99999999). time() . "fahubaqwads");
		$currentTime = time();
		
		// Test the identifier that makes forms unique to each user
		$uniqueIdentifier .= (defined(SITE_SALT) ? SITE_SALT : "");
		$uniqueIdentifier .= $this->formName;
		
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
	public function success
	(
		$uniqueIdentifier = ""	/* <str> You can specify a unique identifier that the form validation requires. */
		
		/* global $data	is used, and the check includes:		
			-> formguard_salt		The random salt used when the form was created.
			-> formguard_key		The resulting hash from preparation.
			-> tos_soimportant		A honeypot. If anything is written here, it's a spam bot. Form fails.
			-> human_answer			A honeypot. If anything is added here, it's a spam bot. Form fails. */
	)							/* RETURNS <html> : HTML to insert into the form. */
	
	// if($form->success()) { echo "The form has been submitted successfully!"; }
	{
		$data = $this->data;
		
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
			$uniqueIdentifier .= $this->formName;
			
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
	
	
/****** Display the Form ******/
	public function generate
	(
		$url				/* <str> The URL to load. */,
		$method = "post"	/* <str> The method to use for your form. */
	)						/* RETURNS <html> : HTML form. */
	
	// $form->generate("./this-page");
	{
		echo '
		<form action="' . $url . '" method="' . ($method == "get" ? "get" : "post") . '">';
		
		// Display each of the inputs
		foreach($this->input as $input)
		{
			echo '
			<div class="form-input-' . $input['type'] . ' ' . $input['name'] . '">
				<label for="' . $input['name'] . '">' . $input['label'] . '</label>';
			
			// Determine the type of input being displayed and output it accordingly
			switch($input['type'])
			{
				case "text":
				case "password":
					echo '
					<input id="' . $input['name'] . '" type="' . $input['type'] . '" name="' . $input['name'] . '" value="' . $input['value'] . '" />';
				break;
				
				case "select":
					echo '
					<select id="' . $input['name'] . '" name="' . $input['name'] . '">';
					
					// Cycle through the selection options
					if(isset($this->options[$input['name']]))
					{
						foreach($this->options[$input['name']] as $option)
						{
							echo '
							<option value="' . $option['value'] . '"' . ($option['selected'] == true ? ' selected="selected"' : '') . '>' . $option['text'] . '</option>';
						}
					}
					
					echo '
					</select>';
				break;
				
				case "radio":
					// Cycle through the radio options
					if(isset($this->options[$input['name']]))
					{
						foreach($this->options[$input['name']] as $option)
						{
							echo '
							<input id="' . $input['name'] . '" type="radio" name="' . $input['name'] . '" value="' . $option['value'] . '"' . ($option['checked'] == true ? ' checked="checked"' : '') . '>' . $option['text'];
						}
					}
				break;
				
				case "submit":
					echo '
					<input id="' . $input['name'] . '" type="' . $input['type'] . '" name="' . $input['name'] . '" value="' . $input['value'] . '" />';
				break;
			}
			
			echo '
			</div>';
		}
		
		echo self::prepare();
		
		echo '
		</form>';
	}
	
	
/****** Create a Text Input ******/
	public function text
	(
		$name					/* Name of the input. */,
		$label					/* Label */,
		$value = ""				/* Default value */,
		$parameters = array()	/* Other parameters to include. */
	)							/* RETURNS <void> */
	
	// $form->text("name");
	{
		array_push($this->input, array(
			"type" => 'text',
			"name" => $name,
			"label" => $label,
			"value" => (isset($this->data->$name) ? $this->data->$name : $value),
			"maxLength" => (isset($parameters['maxLength']) ? $parameters['maxLength'] : ""),
			"size" => (isset($parameters['size']) ? $parameters['size'] : "")
		));
	}
	
	
/****** Create a Password Input ******/
	public function password
	(
		$name					/* Name of the input. */,
		$label					/* Label */,
		$parameters = array()	/* Other parameters to include. */
	)							/* RETURNS <void> */
	
	// $form->password("password", "Password");
	{
		array_push($this->input, array(
			"type" => 'password',
			"name" => $name,
			"label" => $label,
			"value" => "",
			"maxLength" => (isset($parameters['maxLength']) ? $parameters['maxLength'] : ""),
			"size" => (isset($parameters['size']) ? $parameters['size'] : "")
		));
	}
	
	
/****** Create a Checkbox Input ******/
	public function checkbox
	(
		$name					/* Name of the input. */,
		$label					/* Label */,
		$checked = false		/* Set to true if checked by default. */
	)							/* RETURNS <void> */
	
	// $form->checkbox("termsOfService", "You must agree to the TOS");
	{
		// Save last post action if applicable
		$checked = (isset($this->data->$name) ? $this->data->$name : $checked);
		
		array_push($this->input, array(
			"type" => 'checkbox',
			"name" => $name,
			"label" => $label,
			"checked" => ($checked == true ? true : false)
		));
	}
	
	
/****** Create a Radio Input ******/
	public function radio
	(
		$name					/* Name of the radio button. */,
		$label					/* Label */
	)							/* RETURNS <void> */
	
	// $form->radio("gender", "Gender");
	{
		$this->lastInputName = $name;
		
		array_push($this->input, array(
			"type" => 'radio',
			"name" => $name,
			"label" => $label
		));
		
		return $this;
	}
	
	
/****** Create Radio Options ******/
	public function radioOption
	(
		$value					/* Value of the radio option. */,
		$text					/* Text to appear next to radio option. */,
		$checked = false		/* Set to true if checked by default */
	)							/* RETURNS <void> */
	
	// $form->radioOption("gender", "male", true);
	{
		// Prepare the option list if it doesn't exist
		if(!isset($this->options[$this->lastInputName]))
		{
			$this->options[$this->lastInputName] = array();
		}
		
		// Save last post action if applicable
		$trackName = $this->lastInputName;
		$checked = (isset($this->data->$trackName) && $this->data->$trackName == $value ? $this->data->$trackName : $checked);
		
		// Add to the option list
		array_push($this->options[$this->lastInputName], array(
			"value" => $value,
			"text" => $text,
			"checked" => ($checked == false ? false : true)
		));
		
		return $this;
	}
	
	
/****** Create a Selection Input ******/
	public function select
	(
		$name					/* Name of the select dropdown. */,
		$label					/* Label */
	)							/* RETURNS <void> */
	
	// $form->select("password_hint", "Password Hint");
	{
		$this->lastInputName = $name;
		
		array_push($this->input, array(
			"type" => 'select',
			"name" => $name,
			"label" => $label
		));
		
		return $this;
	}
	
	
/****** Create Selection Options ******/
	public function selectOption
	(
		$value					/* Value of the radio option. */,
		$text					/* Text of the select option. */,
		$selected = false		/* Set to true if selected by default */
	)							/* RETURNS <void> */
	
	// $form->selectOption("mothers_name", "Mother's Maiden Name", true);
	{
		// Prepare the option list if it doesn't exist
		if(!isset($this->options[$this->lastInputName]))
		{
			$this->options[$this->lastInputName] = array();
		}
		
		// Save last post action if applicable
		$trackName = $this->lastInputName;
		$selected = (isset($this->data->$trackName) && $this->data->$trackName == $value ? $this->data->$trackName : $selected);
		
		// Add to the option list
		array_push($this->options[$this->lastInputName], array(
			"value" => $value,
			"text" => $text,
			"selected" => ($selected == false ? false : true)
		));
		
		return $this;
	}
	
	
/****** Create a Submit Button ******/
	public function submit
	(
		$label			/* Label for the submit button. */,
		$value			/* Text to display on the submit button. */
	)					/* RETURNS <void> */
	
	// $form->submit("Submit");
	{
		array_push($this->input, array(
			"type" => 'submit',
			"name" => $this->formName . '-submit',
			"label" => $label,
			"value" => $value
		));
	}
}

