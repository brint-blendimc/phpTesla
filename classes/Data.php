<?php if(!defined("HIAB_SAFE")) { die("No direct script access allowed."); }

/****** Data Class ******
* This class stores important information through the lifetime of the page. This includes the equivalent of the query
* string (e.g. "site.com/urlSegment/2ndUrlSegment/etc") and the $_GET and $_POST values that are sent (though we will
* try to avoid $_GET at all costs for SEO purposes).
* 
****** Common Uses of the Data Class ******
* $data->{value}		// The $data class contains all of the $_POST values sent by the browser.
* $data->url[] 			// Contains all of the URL segments that were used.
* 
****** Methods Available ******
* $data->getClientData()			// Puts all of the $_POST values retrieved into $data
* $data->getURLSegments()			// Retrieves all of the URL segments and puts them in $data->url[]
* 
*/

class Data {

/****** Important Values ******/
	public $storage = array();
	
/****** Initializer ******
When this class is instantiated, gather the client data ($_GET and $_POST) and set it. */
	function __construct()
	{
		$this->getClientData();
		$this->getURLSegments();
	}
	
/****** Set Data Values Dynamically ******/
	public function __set($name, $value)
	{
		$this->storage[$name] = $value;
	}

/****** Retrieve Data Values Dynamically ******/
	public function __get($name)
	{
		if(isset($this->storage[$name]))
		{
			return $this->storage[$name];
		}
		
		return false;
	}

/****** Check if Data Values Are Set ******/
	public function __isset($name)
	{
		return isset($this->data[$name]);
	}

/****** Retrieve User Arguments ($_POST) ******/
	private function getClientData()
	{
		// Scan through $_POST for values
		if(isset($_POST))
		{
			foreach($_POST as $key => $value)
			{
				if($key == "url") { continue; }
				
				$this->storage[$key] = $value;
			}
		}
		
		return true;
	}
	
/****** Set the URL Segments for this Page Load ******/
	private function getURLSegments()
	{
		$segments = explode("/", ltrim(rtrim($_SERVER['REQUEST_URI'], "/"), "/"));
		
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
		
		$this->storage['url'] = $segments;
		
		return true;
	}
}

