<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Data Class ******
* This class stores the user input (the $_POST values) that get processed.
* 
****** Common Uses of the Data Class ******
* $data->{value}		// The $data class contains all of the $_POST values sent by the browser.
* 
****** Methods Available ******
* $data->getClientData()			// Puts all of the $_POST values retrieved into $data
* $data->getURLSegments()			// Retrieves all URL Segments of the current address and returns them.
* 
*/

class Data {


/****** Initialize ******
When this class is instantiated, gather the client data ($_GET and $_POST) and set it. */
	function __construct()
	{
		$this->getClientData();
	}

/****** Retrieve User Arguments ($_POST) ******/
	private function getClientData()
	{
		// Scan through $_POST for values
		if(isset($_POST))
		{
			foreach($_POST as $key => $value)
			{
				$this->$key = $value;
			}
		}
		
		return true;
	}
	
/****** Return the URL Segments for this Page Load ******/
	public static function getURLSegments(
	)		/* RETURNS <array> : URL Segments of the web address provided (e.g. "domain.com/{segment1}/{segment2}"); */
	
	// $url = Data::getURLSegments();
	{
		// Strip out any query string data (if used)
		$urlString = explode("?", $_SERVER['REQUEST_URI']);
		
		// Sanitize any unsafe characters from the URL
		$urlString = Sanitize::variable($urlString[0], "-/");
		
		// Section the URL into multiple segments so that each can be added to the array individually
		$segments = explode("/", ltrim(rtrim($urlString, "/"), "/"));
		
		// Strip away any unnecesasry URL Segments (such as localhost paths)
		$defSegments = explode("/", rtrim(BASE_DIR, "/"));
		$lastSegment = $defSegments[count($defSegments) - 1];
		
		if(in_array($lastSegment, $segments))
		{
			for($i = 0;$i < count($segments);$i++)
			{
				array_shift($segments);
				
				if($segments[$i] != $lastSegment)
				{
					break;
				}
			}
		}
		
		return $segments;
	}
}

