<?php if(!defined("IS_SAFE")) { die("No direct script access allowed."); }

/****** Testing Class ******
* This is used as the base class for all testing modules. Each individual testing module is used to test whether the
* other classes and scripts within the system are working properly. This base class offers important functionality
* to run those tests.
* 
****** Methods Available ******
* Testing::word($userInput, $extraChars = "");			// Allows letters
*/

class Testing
{
	public $testList = array();
	
	public function addTest($class)
	{
		// Make sure the class exists
		if(class_exists($class))
		{
			$this->testList[$class] = array();
			
			// Add specific methods to the testing check if there are arguments provided
			$args = func_get_args();
			
			for($i = 1;$i < count($args);$i++)
			{
				if(method_exists($class, $args[$i]))
				{
					array_push($this->testList[$args[$i]]);
				}
			}
			
			return true;
		}
		
		return false;
	}
	
	protected function validate($class, $method, $summary, $expectedReturn, $actualReturn, $parameters = array())
	{
		return array(
			"class" => $class,
			"method" => $method,
			"summary" => $summary,
			"parameters" => $parameters,
			"success" => $expectedReturn === $actualReturn
		);
	}
}
