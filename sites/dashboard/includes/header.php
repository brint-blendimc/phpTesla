<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); } ?>
<!DOCTYPE html>

<head>
	<title>Uni-Dashboard</title>
	
	<style>
		#main-navigation dl dt { display:inline-block; }
	</style>
</head>
<body>

<nav id="main-navigation">
	<dl>
		<dt><a href="<?=BASE_URL;?>/">Index</a></dt>
		<dt><a href="<?=BASE_URL;?>/projects">Project List</a></dt>
		<dt><a href="<?=BASE_URL;?>/projects/create">Create New Project</a></dt>
		<dt><a href="<?=BASE_URL;?>/layouts">Layouts</a></dt>
		<dt><a href="<?=BASE_URL;?>/register">Register</a></dt>
		<dt><a href="<?=BASE_URL;?>/login">Login</a></dt>
		<dt><a href="<?=BASE_URL;?>/logout">Logout</a></dt>
	</dl>
</nav>