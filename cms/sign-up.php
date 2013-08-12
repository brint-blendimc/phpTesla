<?php

// Prevent Registration if already logged in
if(isset($_SESSION[USER_SESSION]))
{
	header("Location: ./"); exit;
}

// Run Registration
UserController::registerUser($data, "./login-success", true);

// Run Headers
require_once("./includes/metaheader.php");
require_once("./includes/header.php");

?>

<div class="six-columns offset-by-five">
	<h1>Sign Up</h1>
	<form action="/sign-up" method="post" />
		
		<h5>Username</h5>
		<input type="text" name="username" value="<?=(isset($data->username) ? $data->username : "");?>" />
		<p style="color:red;"><?=Note::getError("Username");?></p>
		
		<h5>Email</h5>
		<input id="email" type="text" name="email" value="<?=(isset($data->email) ? $data->email : "");?>" />
		<p style="color:red;"><?=Note::getError("Email");?></p>
		
		<h5>Password</h5>
		<input type="password" name="password" />
		<p style="color:red;"><?=Note::getError("Password");?></p>
		
		<h5>Other</h5>
		<input id="newsletter" type="checkbox" name="newsletter" <?=(isset($data->newsletter) ? 'checked="checked"' : "");?> />
		<label for="newsletter">Get our newsletter (0 - 6 emails / year)</label><br />
		
		<input id="goodies" type="checkbox" name="goodies" <?=(isset($data->goodies) ? 'checked="checked"' : "");?> />
		<label for="goodies">Get free goodies (0 - 2 emails / year)</label><br />
		
		<input id="tos" type="checkbox" name="tos" <?=(isset($data->tos) ? 'checked="checked"' : "");?> />
		<label for="tos">I agree to the <a href="./terms-of-service" style="color:#91DFDE;">Terms of Service</a> <?=Note::getError("Terms of Service", ' &nbsp; <span style="color:red">(', ')</span>');?></label>
		
		<br /><br />
		<input class="button big" type="submit" name="submit" value="Sign Up" />
	</form>
</div>

<?php

require_once("./includes/footer.php");

?>