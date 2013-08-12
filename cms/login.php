<?php

// Redirect to home if already logged in
if(isset($_SESSION[USER_SESSION]))
{
	header("Location: ./"); exit;
}

// Run Login
UserController::login($data, "./login-success");

// Run Headers
require_once("./includes/metaheader.php");
require_once("./includes/header.php");

?>

<div class="one-third column offset-by-five">
	<h1>Log In</h1>
	<form action="./login" method="post" />
		
		<h5>Username or Email</h5>
		<input type="text" name="login" value="<?=(isset($data->login) ? $data->login : "");?>" />
		<?=Note::getError("Login", "<p style='color:red;'>", "</p>");?>
		
		<h5>Password</h5>
		<input type="password" name="password" />
		
		<input class="button big" type="submit" name="submit" value="Login" />
	</form>
	
	<br /><br />
	<a href="./register" class="button big full-width">New Users, Join Here</a>
	<a href="./forgot-password" class="button big full-width">Forgot Your Password?</a>
</div>

<?php

require_once("./includes/footer.php");

?>