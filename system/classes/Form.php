<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Form Generation Class ******
* This class provides form generation and validation.
* 
****** How To Use This Class ******

// Step #1 - Initialize the Form
$form = new Form(
	
	// Name of the form
	"register"
	
	// The URL that the form submits to.
,	"/register"
	
	// The database table to associate with the form.
	// Your form only needs this if you're going to auto-process the form with ::process()
,	"users"
	
	// Allow Editing Mode (if you set this to false, the options below don't matter)
,	true
	
	// The table column that identifies the key to edit with.
	// This is usually set to "id" - if not, then generally the PRIMARY KEY of the table.
,	"id"
	
	// The key (column ID) of the row you are editing.
	// So if the above value is set to "id", then this value would be the ID of what number row to edit.
,	15
	
	// For admin pages you could set the key to:
	// (isset($data->editVal) ? $data->editVal : $data->edit) // Edit mode is off if neither are set.
	
);


// Step #2 - Construct the Form Inputs
// This builds all of the visual components, as well as structures the columns for interpretation
$form->text("username", "Username");
$form->password("password", "Password");
$form->select("password_hint", "Password Hint")
	->selectOption("mothers_name", "Mother's Maiden Name", true)
	->selectOption("favorite_color", "Your Favorite Color")
	->selectOption("highschool_mascot", "Your High School Mascot");
$form->radio("gender", "Gender")
	->radioOption("male", "Male", true)
	->radioOption("female", "Female");
$form->textarea("description", "Description");
$form->checkbox("tos", "Terms of Service");
$form->submit("Submit", "Submit");

// Step #3 - Form Validation
// This only activates if the security checks were passed.
if($form->success())
{
	echo "You have successfully processed the user " . $data->username . "!";
	
	// Custom Form Interpretations
	// Modify how the data you send is interpreted. (e.g. delimiters, string parsing, sanitization, ...)
	// $data->title = ucwords($data->title);	// Capitalizes each word in the title
	// $data->timeUpdated = time();				// Sets the current time field (not provided by the form itself)
	
	// This inserts (or edits) the appropriate row in the database
	$form->process();
	
	// After Process Handling
	// header("Location: /complete.php"); exit;
}

// Step #4 - Display the Form
// This can be handled separately, but this will auto-build the form.
$form->generate();

****** Methods Available ******
* $form = new Form($formName);				// Creates the Form object
* 
* $form->text($key, $value, $default = "", $args = array())			// Generates a "text" input
* $form->password($key, $value, $args = array())					// Generates a "password" input
* $form->checkbox($key, $value, $checked = false)					// Generates a "checkbox" input
* $form->select($key, $value)										// Generates a "select" input
* $form->selectOption($key, $value, $selected = false)				// Option for the most recent "select" input
* $form->radio($key, $value)										// Generates a "radio" input
* $form->radioOption($key, $value, $checked = false)				// Option for the most recent "radio" input
* $form->textarea($key, $value, $default = "", $args = array())		// Generates a "text" input
* $form->submit($key, $value)										// Generates the "submit" button for the form
* 
* $form->editKey($value)				// Sets the editing key at $value
* $form->editMode()						// Gets edit mode
* 
* $form->prepare($uniqueIdentifier = "", $expiresInminutes = 300)	// Prepares hidden tags to protect a form.
* $form->generate()													// Generates the form based on data supplied.
* $form->success($uniqueIdentifier = "")							// Validates if the form submission was successful.
* 
* $form->process()				// Processes the form (inserts or updates, depending on the current mode)
* $form->insert()				// Inserts a table row based on the form data sent.
* $form->update()				// Updates a table row based on mode and data.
*/

class Form {
	
	
/****** Variables ******/
	public $formName = "";
	public $urlAction = "";
	
	public $allowEditing = false;
	
	public $input = array();
	public $options = array();
	public $lastInputName = "";
	
	public $tableName = "";			// The name of the database table to associate with the form.
	public $tableColumn = null;		// The column by which to edit (if in edit mode). Generally "id"
	public $tableValue = null;		// The row to edit (if in edit mode). (e.g. "WHERE $tableColumn = $tableValue")
	
	public $data = null;			// Store submitted data
	public $rowData = array();		// Store the row data (that you retrieve from the database)
	
	
/****** Initialize the Form ******/
	function __construct
	(
		$name					/* <str> Name of the form. */,
		$urlAction				/* <str> The URL that the form submits to. */,
		$tableName = ""			/* <str> The database table to associate with the form. */,
		
		$allowEditing = false	/* <bool> Sets Edit Mode */,
		$tableColumn = "id"		/* <str> The table column that identifies the key to edit with. */,
		$tableValue = ""		/* <int> or <str> The row key (column ID) of the row you're editing. */
	)							/* RETURNS <void> */
	
	// $form = new GenerateForm("register");
	{
		global $data;
		
		$this->formName = strtolower(Sanitize::variable($name));
		$this->urlAction = $urlAction;
		
		// If this form is connected to a database table:
		if($tableName != "")
		{
			$this->tableName = Sanitize::variable($tableName, "-");
			
			// If the form allows editing:
			if($allowEditing != false)
			{
				$this->allowEditing = true;
				$this->tableColumn = Sanitize::variable($tableColumn, '-');
				$this->editKey($tableValue);
			}
		}
		
		$this->data = $data;
	}
	
	
/****** Set Form to Edit Mode & Set Key ******
This function sets the form to edit an existing row rather than creating a new form. */
	public function editKey
	(
		$keyValue		/* <int> or <str> The key of the row to edit. (e.g. WHERE $col = $keyValue) */
	)					/* RETURNS <void> */
	
	// $form->editKey(25);		// Edit the ID column where ID = 25
	{
		// Check Database for Editing Potential
		$rowData = Database::selectOne("SELECT * FROM `" . $this->tableName . "` WHERE `" . $this->tableColumn . "`=?", array($keyValue));
		
		if(isset($rowData[$this->tableColumn]))
		{
			// Store the Editing Results
			$this->tableValue = $keyValue;
			$this->rowData = $rowData;
			
			// Prepare a hidden edit input for editing purposes
			$this->hidden("editVal", $keyValue);
		}
	}
	
	
/****** Check if you're in Edit Mode ******/
	public function editMode (
	)					/* RETURNS <bool> : TRUE if edit mode is active, FALSE if not. */
	
	// if($form->editMode()) { echo "You're in edit mode."; }
	{
		return (isset($this->allowEditing) && isset($this->tableValue));
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
		$salt = md5(rand(0, 99999999) . time() . "fahubaqwads");
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
	public function generate (
	)						/* RETURNS <html> : HTML form. */
	
	// $form->generate();
	{
		echo '
		<form action="' . $this->urlAction . '" method="post">';
		
		// Display each of the inputs
		foreach($this->input as $input)
		{
			// Unique Behavior for Hidden Fields
			if($input['type'] == "hidden")
			{
				echo '
					<input class="form-input-hidden" type="hidden" name="' . $input['name'] . '" value="' . $input['value'] . '" />';
				
				continue;
			}
			
			// All other input fields get ouput normally
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
				
				case "textarea":
					echo '
					<textarea id="' . $input['name'] . '" name="' . $input['name'] . '">' . $input['value'] . '</textarea>';
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
	
	
/****** Processes the Form ******/
	public function process (
	)						/* RETURNS <void>. */
	
	// $form->process();
	{
		$mode = ($this->editMode() ? "update" : "insert");
		
		$this->$mode($this->tableName);
	}
	
	
/****** Insert a Database Row based on Form Submission Data ******/
	public function insert (
	)						/* RETURNS <void>. */
	
	// $form->insert();
	{
		// Prepare Values
		global $data;
		
		$columns = Database::getColumns($this->tableName);
		$sqlCols = "";
		$sqlValues = "";
		$sqlArray = array();
		
		// SQL Query Preparation
		foreach($columns as $column)
		{
			if(isset($data->$column) && $data->$column != "")
			{
				$sqlCols .= ($sqlCols != "" ? ", " : "") . "`" . $column . "`";
				$sqlValues .= ($sqlValues != "" ? ", " : "") . "?";
				$sqlArray[] = $data->$column;
			}
		}
		
		// Run the Insert
		Database::query("INSERT INTO `" . $this->tableName . "` (" . $sqlCols . ") VALUES (" . $sqlValues . ")", $sqlArray);
	}
	
	
/****** Update a Database Row based on Form Mode & Data ******/
	public function update (
	)						/* RETURNS <void>. */
	
	// $form->update();
	{
		// End prematurely if the edit mode is broken
		if(!$this->editMode()) { return false; }
		
		// Prepare Values
		global $data;
		
		$columns = Database::getColumns($this->tableName);
		$sqlSet = "";
		$sqlWhere = "";
		$sqlArray = array();
		
		// SQL Query Preparation
		foreach($columns as $column)
		{
			// Prepare each column for editing
			if(isset($data->$column) && $data->$column != "")
			{
				$sqlSet .= ($sqlSet != "" ? ", " : "") . "`" . $column . "`=?";
				$sqlArray[] = $data->$column;
			}
			
			// Find the editable column
			if($column == $this->tableColumn)
			{
				$sqlWhere = $this->tableColumn . "=?";
			}
		}
		
		// Provide the Where String
		if($sqlWhere != "")
		{
			$sqlArray[] = $this->tableValue;
			
			// Run the Update
			Database::query("UPDATE `" . $this->tableName . "` SET " . $sqlSet . " WHERE " . $sqlWhere . " LIMIT 1", $sqlArray);
		}
	}
	
	
/****** Create a Hidden Input ******/
	public function hidden
	(
		$name					/* <str> Name of the input. */,
		$value = ""				/* <str> Default value */,
		$parameters = array()	/* <array> Other parameters to include. */
	)							/* RETURNS <void> */
	
	// $form->hidden("editVal", "true");
	{
		array_push($this->input, array(
			"type" => 'hidden',
			"name" => $name,
			"value" => htmlspecialchars(isset($this->data->$name) ? $this->data->$name : (isset($this->rowData[$name]) ? $this->rowData[$name] : $value))
		));
	}
	
	
/****** Create a Text Input ******/
	public function text
	(
		$name					/* Name of the input. */,
		$label					/* Label */,
		$value = ""				/* Default value */,
		$parameters = array()	/* Other parameters to include. */
	)							/* RETURNS <void> */
	
	// $form->text("username", "Username");
	{
		array_push($this->input, array(
			"type" => 'text',
			"name" => $name,
			"label" => $label,
			"value" => htmlspecialchars(isset($this->data->$name) ? $this->data->$name : (isset($this->rowData[$name]) ? $this->rowData[$name] : $value)),
			"maxLength" => (isset($parameters['maxLength']) ? $parameters['maxLength'] : ""),
			"size" => (isset($parameters['size']) ? $parameters['size'] : "")
		));
	}
	
	
/****** Create a Textarea Input ******/
	public function textarea
	(
		$name					/* Name of the input. */,
		$label					/* Label */,
		$value = ""				/* Default value */,
		$parameters = array()	/* Other parameters to include. */
	)							/* RETURNS <void> */
	
	// $form->textarea("description", "Description", "Please enter a description");
	{
		array_push($this->input, array(
			"type" => 'textarea',
			"name" => $name,
			"label" => $label,
			"value" => (isset($this->data->$name) ? $this->data->$name : (isset($this->rowData[$name]) ? $this->rowData[$name] : $value))
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
		// Try to retrieve checkbox status (based on edit mode)
		if(isset($this->rowData[$name]) && $this->rowData[$name] != 0 && $this->rowData[$name] != "")
		{
			$checked = true;
		}
		else
		{
			// Save last post action if applicable
			$checked = (isset($this->data->$name) ? true : $checked);
		}
		
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
		
		// Try to retrieve checkbox status (based on edit mode)
		if(isset($this->rowData[$trackName]) && $this->rowData[$trackName] != 0 && $this->rowData[$trackName] != "")
		{
			$checked = true;
		}
		else
		{
			// Save last post action if applicable
			$checked = (isset($this->data->$trackName) && $this->data->$trackName == $value ? true : $checked);
		}
		
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
		
		// Determine Selected Status from DB (for Edit Mode)
		if(isset($this->rowData[$trackName]) && $this->rowData[$trackName] == $value)
		{
			$selected = true;
		}
		
		// Determine Selected Status from $data (if submitted)
		else
		{
			// Save last post action if applicable
			$selected = (isset($this->data->$trackName) && $this->data->$trackName == $value ? $this->data->$trackName : $selected);
		}
		
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