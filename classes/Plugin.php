<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Plugin Class ******
* This class will initialize and track any plugins with a self-built autoloader.
* 
****** In-Practice Examples ******
* $plugin = new Plugin();
* $plugin->tasks->method();
* $plugin->tasks->group->method();
* 
****** Methods Available ******
* Note::error($errorMessage);				// Adds a new error to the error list.
*/

class Plugin {

/****** Public Variables ******/
	public $pluginList = array();
	
/****** Automatically Identify Plugins ******/
	public function __get
	(
		$varName				/* <str> A reference to the dynamic variable name being called. */
	)							/* RETURNS <OBJECT> : Contains the plugin. */
	
	// $plugin->tasks->method()		// Calls it properly the first time, including autoloading the class.
	{
		// Sanitize
		$varName = strtolower(Sanitize::variable($varName));
		
		// If the Plugin has already been initialized before, use it
		if(isset($this->pluginList[$varName]))
		{
			return $this->pluginList[$varName];
		}
		
		// If the Plugin has not been initialized, attempt to initialize it and return it
		else
		{
			// Make sure the file exists
			if(is_file(BASE_DIR . "/plugins/" . $varName . "/initialize.php"))
			{
				require_once(BASE_DIR . "/plugins/" . $varName . "/initialize.php");
				
				$initClass = ucfirst($varName) . "Plugin";
				
				$this->pluginList[$varName] = new $initClass();
				
				return $this->pluginList[$varName];
			}
		}
		
		return null;
	}
	
}
