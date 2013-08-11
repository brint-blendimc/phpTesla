<?php if(!defined("AJAX_LOADED")) { die("No direct script access allowed."); }

// Prepare Variables
if(!isset($_POST['type'])) { $_POST['type'] = ""; }
if(!isset($_POST['theme'])) { $_POST['theme'] = ""; }
if(!isset($_POST['size'])) { $_POST['size'] = ""; }

// Prepare SQL Statement
$sqlMain = "";
$sqlArray = array();

if($_POST['type'] != "")
{
	$sqlMain .= "type=?";
	array_push($sqlArray, $_POST['type']);
}

if($_POST['theme'] != "")
{
	$sqlMain .= (strlen($sqlMain) > 0 ? " AND " : "") . "theme=?";
	array_push($sqlArray, $_POST['theme']);
}

if($_POST['size'] != "")
{
	$sqlMain .= (strlen($sqlMain) > 0 ? " AND " : "") . "size=?";
	array_push($sqlArray, $_POST['size']);
}

if($sqlMain != "")
{
	$sqlMain = " WHERE " . $sqlMain;
}

// Check the Database
$getBlueprints = Database::selectMultiple("SELECT id, type, image, file FROM blueprints" . $sqlMain, $sqlArray);

foreach($getBlueprints as $blueprint)
{
	echo '
	<div id="bp-' . $blueprint['id'] . '" class="four columns">';
	
	if($blueprint['file'] != '')
	{
		echo '
		<a href="./assets/blueprints/' . $blueprint['file'] . '"><img class="scale-with-grid" src="./assets/blueprint_images/' . $blueprint['type'] . '/' . $blueprint['image'] . '" onclick=\'document.getElementById("bp-' . $blueprint['id'] . '").style.display="none"\' /></a>';
	}
	else
	{
		echo '<img class="scale-with-grid" src="./assets/blueprint_images/' . $blueprint['type'] . '/' . $blueprint['image'] . '" onclick=\'document.getElementById("bp-' . $blueprint['id'] . '").style.display="none"\' />';
	}
	
	echo '
	</div>';
}