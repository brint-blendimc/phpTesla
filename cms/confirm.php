<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

// Display Header
require_once("./includes/header.php");

?>

<div class='page'>
	<h2>Confirmation</h2>
	
	<p>
	<?php
	
	// Check if the confirmation link is valid
	if(Confirm::validate($url[1], $url[2], $url[3]))
	{
		switch($url[1])
		{
			case "reset-password":
				
				// Update Password on Submission
				$formReturn = UserController::updatePassword($url[2], $data);
				
				if($formReturn !== false)
				{
					echo "You have successfully updated your password!";
				}
				else
				{
					echo '
					Please set your password now:<br /><br />
					<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
						<input type="password" name="password" value="" /> 
						<input type="submit" name="submit" value="Update Password" />
					</form>';
				}
				
				echo Note::display();
				
			break;
			
			case "email-confirmation":
				echo "successful email confirmation";
				$user = Database::selectOne("SELECT id, username FROM users WHERE username=? LIMIT 1", array($url[1]));
				
				if(isset($user['id']))
				{
					Database::query("UPDATE users SET is_confirmed=? WHERE id=? LIMIT 1", array(1, $user['id']));
					echo 'further success!';
				}
			break;
		}
	}
	
	?>
	</p>
	
	<div class='clear'></div>
</div>


<?php require_once("./includes/footer.php"); ?>