<?php if(!defined("IS_SAFE")) { die("No direct script access allowed."); }

/****** Prepare the Auto-Loader ******/
spl_autoload_register(null, false);
spl_autoload_extensions('.php');

/****** Create our custom Auto-Loader Function ******/
function autoLoader($class)
{
	// Reject class names that aren't valid
	if(!ctype_alnum($class))
	{
		return false;
	}
	
	// Cycle through all relevant directories and load the class (if found)
	$directories = array("classes", "testing");
	
	foreach($directories as $dir)
	{
		$classFile = realpath("./$dir/$class.php");
		
		if(file_exists($classFile))
		{
			require_once($classFile);
			return true;
		}
	}
	
	return false;
}

/****** Register our custom Auto-Loader ******/
spl_autoload_register('autoLoader');