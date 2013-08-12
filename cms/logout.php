<?php

// If user was already logged out, return to home page
if(!isset($_SESSION[USER_SESSION]))
{
	header("Location: ./"); exit;
}

// Log the User Out
Me::logout();

// Run Headers
require_once("./includes/metaheader.php");
require_once("./includes/header.php");

?>

<div class="two-thirds column offset-by-two">
	<h2>You have logged out.</h2>
</div>

<?php

require_once("./includes/footer.php");

?>