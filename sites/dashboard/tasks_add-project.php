<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); } 

if($plugin->users->clearance->hasClearance("Task Management")) { echo "You have clearance."; }

// Display the Registration Success Page
require_once(SITE_DIR . "/includes/header.php");

echo "You can now add a project";

require_once(SITE_DIR . "/includes/footer.php");
