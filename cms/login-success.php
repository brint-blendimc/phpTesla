<?php

// Return user to home page if they're not logged in
if(!isset($_SESSION[USER_SESSION]))
{
	header("Location: ./"); exit;
}

// Run Headers
require_once("./includes/metaheader.php");
require_once("./includes/header.php");

?>

<div class="two-thirds column offset-by-two">
	<h2>You have successfully logged in!</h2>
</div>

<?php

require_once("./includes/footer.php");

?>