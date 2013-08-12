<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Data Class ******
* This class stores the user input (the $_POST values) that get processed.
* 
****** Common Uses of the Data Class ******
* $data->{value}		// The $data class contains all of the $_GET and $_POST values sent by the browser.
* 
****** Methods Available ******
* $data->getClientData()			// Puts all of the $_GET and $_POST values retrieved into $data
* $data->getURLRoutes()			// Retrieves all URL Segments of the current address and returns them.
* 
*/

class Data {


/****** Initialize ******
When this class is instantiated, gather the client data ($_GET and $_POST) and set it. */
	function __construct()
	{
		$this->getClientData();
	}

/****** Retrieve User Arguments ($_GET and $_POST) ******/
	private function getClientData()
	{
		// Scan through $_GET for values
		if(isset($_GET))
		{
			foreach($_GET as $key => $value)
			{
				$this->$key = $value;
			}
		}
		
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
	public static function getURLRoutes(
	)		/* RETURNS <array> : URL routes of the web address provided (e.g. "domain.com/{route1}/{route2}"); */
	
	// $url = Data::getURLRoutes();
	{
		// Strip out any query string data (if used)
		$urlString = explode("?", rawurldecode($_SERVER['REQUEST_URI']));
		
		// Sanitize any unsafe characters from the URL
		$urlString = str_replace(" ", "-", $urlString[0]);
		$urlString = Sanitize::variable($urlString, "_-/.+");
		
		// Section the URL into multiple segments so that each can be added to the array individually
		$segments = explode("/", ltrim(rtrim($urlString, "/"), "/"));
		
		// Strip away any unnecesasry URL Segments (such as localhost paths)
		$defSegments = explode("/", rtrim(SYS_PATH, "/"));
		$lastSegment = $defSegments[count($defSegments) - 1];
		
		if(in_array($lastSegment, $segments))
		{
			for($i = 0;$i < count($segments);$i++)
			{
				array_shift($segments);
				
				if(!isset($segments[$i]) || $segments[$i] != $lastSegment)
				{
					break;
				}
			}
		}
		
		return $segments;
	}
}

