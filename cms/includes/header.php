
<body>

<div class="container">
	
	<div class="sixteen columns">
		<a href="/" class="button">Home</a>
		<a href="/about-starborn" class="button">About Starborn</a>
		<a href="http://projectstarborn.tumblr.com" target="_blank" class="button">Blog</a>
		<a href="/blueprints" class="button">Blueprints</a>
		<a href="/about-team" class="button">About the Team</a>
		
		<?php
		//	<a href="/community" class="button">Forum</a>
		
		// Show ACCOUNT links in the header (user is logged in)
		if(isset($_SESSION[USER_SESSION]))
		{
			echo '
			<a href="/logout" class="button">Log Out</a>';
		}
		
		// Show GUEST links in the header (not logged in)
		else
		{
			echo '
			<a href="/login" class="button">Log In</a>
			<a href="/sign-up" class="button">Sign Up</a>';
		}
		
		?>
	</div>