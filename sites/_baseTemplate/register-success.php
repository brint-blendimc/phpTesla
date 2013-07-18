<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); } 

// Check if a redirect is needed:
$redirect = false;

if(isset($_SESSION[USER_SESSION]))
{
	$userData = User::getData($_SESSION[USER_SESSION]['id']);
	
	if($userData['date_joined'] <= time() - 120)
	{
		$redirect = true;
	}
}
else
{
	$redirect = true;
}

// Redirect if you've already visited the successful registration page
if($redirect === true)
{
	header("Location: ./"); exit;
}

// Display the Registration Success Page
require_once(SITE_DIR . "/includes/header.php");

echo "You have successfully registered!";

require_once(SITE_DIR . "/includes/footer.php");
