<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); } 

// Check if a redirect is needed:
if(isset($_SESSION[USER_SESSION]))
{
	header("Location: ./"); exit;
}

/****** Prepare Values ******/
if(!isset($data->username)) { $data->username = ""; }
if(!isset($data->password)) { $data->password = ""; }

/****** Process Registration ******/
$plugin->users->controller->login($data, "./login_success");

// Display the Login Page
require_once(SITE_DIR . "/includes/header.php");

?>

<!-- Login Form (by Username) -->

<?=Note::display();?>
<form class="form" action="<?=$_SERVER['REQUEST_URI'];?>" method="post">
	
	<div class="form-input username">
		<label for="username">Username</label>
		<input id="username" type="text" name="username" value="<?=$data->username;?>" maxlength="22" />
	</div>
	
	<div class="form-input password">
		<label for="password">Password</label>
		<input id="password" type="password" name="password" value="<?=$data->password;?>" maxlength="64" />
	</div>
	
	<div class="form-input submit">
		<label for="submit">Submit</label>
		<input type="submit" name="submit" value="Submit" />
	</div>
	
</form>

<?php require_once(SITE_DIR . "/includes/footer.php");
