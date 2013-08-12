<?php

// Redirect to home if already logged in
if(isset($_SESSION[USER_SESSION]))
{
	header("Location: ./"); exit;
}

// Run Headers
require_once("./includes/metaheader.php");
require_once("./includes/header.php");

// Run Login
if(UserController::resetPassword($data) !== false)
{
?>

<div class="ten columns offset-by-two">
	<h1>Reset Password Sent</h1>
	<p>A password reset email has been sent to the account's email! Follow the link provided in the email to reset your password.</p>
</div>

<?php
}
else
{
	if(isset($data->login)) { Note::error("Login", "The account you entered does not exist."); }
?>

<div class="six columns offset-by-three">
	<h1>Reset Password</h1>
	<form action="./forgot-password" method="post" />
		
		<h5>Username or Email</h5>
		<input type="text" name="login" value="<?=(isset($data->login) ? $data->login : "");?>" />
		<?=Note::getError("Login", "<p style='color:red;'>", "</p>");?></p>
		
		<input class="button big" type="submit" name="submit" value="Send Password Reset" />
	</form>
</div>

<?php
}

require_once("./includes/footer.php");
