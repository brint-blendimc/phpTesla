<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); } 

// Redirect if you're already logged in
if(isset($_SESSION[USER_SESSION]))
{
	header("Location: " . BASE_URL); exit;
}

/****** Prepare Values ******/
if(!isset($data->username)) { $data->username = ""; }
if(!isset($data->email)) { $data->email = ""; }
if(!isset($data->password)) { $data->password = ""; }


/****** Process Registration ******/
if(isset($data->csrfToken) && $data->csrfToken == Security::csrf('jv*342'))
{
	UserController::registerUser($data, "./register-success");
}

// Display the Page
require_once(SITE_DIR . "/includes/header.php");
?>

<!-- Registration Form -->

<?=Note::display();?>
<form class="form" action="<?=$_SERVER['REQUEST_URI'];?>" method="post">
	
	<input type="hidden" name="csrfToken" value="<?=Security::csrf('jv*342');?>" />
	
	<div class="form-input username">
		<label for="username">Username</label>
		<input id="username" type="text" name="username" value="<?=$data->username;?>" maxlength="22" />
	</div>
	
	<div class="form-input email">
		<label for="email">Email</label>
		<input id="email" type="text" name="email" value="<?=$data->email;?>" maxlength="64" />
	</div>
	
	<div class="form-input password">
		<label for="password">Password</label>
		<input id="password" type="password" name="password" value="<?=$data->password;?>" maxlength="64" />
	</div>
	
	<div class="form-input password">
		<label for="confirm">Confirm Password</label>
		<input id="confirm" type="password" name="confirm" value="" maxlength="64" />
	</div>
	
	<div class="form-input submit">
		<label for="submit">Submit</label>
		<input type="submit" name="submit" value="Submit" />
	</div>
	
</form>

<?php require_once(SITE_DIR . "/includes/footer.php");
