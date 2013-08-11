<!--
	
	Forms Available:
	
	- Registration
	- Login by Username
	- Login by Email
	
-->


<!-- Registration Form -->
<?php
/****** Prepare Values ******/
if(!isset($data->username)) { $data->username = ""; }
if(!isset($data->email)) { $data->email = ""; }
if(!isset($data->password)) { $data->password = ""; }
?>

<form class="form" action="<?=$_SERVER['REQUEST_URI'];?>" method="post">
	
	<div class="form-input username">
		<label for="username">Username</label>
		<input id="username" type="text" name="username" value="<?=$data->username;?>" maxlength="22" />
	</div>
	
	<div class="form-input email">
		<label for="email">Email</label>
		<input id="email" type="text" name="email" value="<?=$data->email;?>" maxlength="64" />
	</div>
	
	<div class="form-input password">
		<label for="password">Password</label>
		<input id="password" type="password" name="password" value="<?=$data->password;?>" maxlength="64" />
	</div>
	
	<div class="form-input password">
		<label for="confirm">Confirm Password</label>
		<input id="confirm" type="password" name="confirm" value="" maxlength="64" />
	</div>
	
	<div class="form-input submit">
		<label for="submit">Submit</label>
		<input type="submit" name="submit" value="Submit" />
	</div>
	
</form>


<!-- Login Form (by Username) -->
<?php
/****** Prepare Values ******/
if(!isset($data->username)) { $data->username = ""; }
if(!isset($data->password)) { $data->password = ""; }
?>

<form class="form" action="<?=$_SERVER['REQUEST_URI'];?>" method="post">
	
	<div class="form-input username">
		<label for="username">Username</label>
		<input id="username" type="text" name="username" value="<?=$data->username;?>" maxlength="22" />
	</div>
	
	<div class="form-input password">
		<label for="password">Password</label>
		<input id="password" type="password" name="password" value="<?=$data->password;?>" maxlength="64" />
	</div>
	
	<div class="form-input submit">
		<label for="submit">Submit</label>
		<input type="submit" name="submit" value="Submit" />
	</div>
	
</form>


<!-- Login Form (by Email) -->

<form class="form" action="<?=$_SERVER['REQUEST_URI'];?>" method="post">

	<div class="form-input email">
		<label for="email">Email</label>
		<input id="email" type="text" name="email" value="<?=$data->email;?>" maxlength="64" />
	</div>
	
	<div class="form-input password">
		<label for="password">Password</label>
		<input id="password" type="password" name="password" value="<?=$data->password;?>" maxlength="64" />
	</div>
	
	<div class="form-input submit">
		<label for="submit">Submit</label>
		<input type="submit" name="submit" value="Submit" />
	</div>
	
</form>


