<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); } ?>
<!DOCTYPE html>

<head>
	<title>Pet Site</title>
	<base href="<?=BASE_URL;?>">
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
	<script src='./libraries/spectrum/spectrum.js'></script>
	<link rel='stylesheet' href='./resources/reset.css' />
	<link rel='stylesheet' href='./libraries/spectrum/spectrum.css' />
	
	<style>
		#main-navigation dl dt { display:inline-block; }
	</style>
</head>
<body>

<nav id="main-navigation">
	<dl>
		<dt><a href="./">Index</a></dt>
		<dt><a href="./starter-pet">Starter Pet</a></dt>
		<dt><a href="./register">Register</a></dt>
		<dt><a href="./login">Login</a></dt>
		<dt><a href="./logout">Logout</a></dt>
	</dl>
</nav>