<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); } 

// Check if a redirect is needed:
if(!isset($_SESSION[USER_SESSION]))
{
	header("Location: " . BASE_URL); exit;
}

unset($_SESSION[USER_SESSION]);

// Display the Logout Page
require_once(SITE_DIR . "/includes/header.php");

echo "You have successfully logged out.";

require_once(SITE_DIR . "/includes/footer.php");
