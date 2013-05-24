<?php if(!defined("IS_SAFE") || !defined("DEVELOPMENT")) { die("Script access is not allowed in production mode."); }

/****** Benchmark Class ******
* This class provides a simple set of functions for benchmarking a single function. You can design a specific function
* to run, or you can test the speed of an existing method or function in an attempt to find potential optimizations.
* 
* This benchmarking class will return the time it took to run your desired method (or function). It will output a value
* in numbers of seconds (with four decimals).
* 
****** In-Practice Code ******
	
	$benchmark = new Benchmark();
	
	// This will run 5000 times: Sanitize::whitelist("A special value to sanitize", "abcdefghijklm")
	$time = $benchmark	->setLoops(5000)
						->useMethod("Sanitize", "whitelist")	// Can also send object: ->useMethod($imgObj, "shrink")
						->useParameters("A special value to sanitize", "abcdefghijklm")
						->run();
	
	// This will run 13500 times: myFunction("arg1", "arg2", "arg3")
	$time2 = $benchmark ->setLoops(13500)
						->useFunction("myFunction")
						->useParameters("arg1", "arg2", "arg3")
						->run();
	
	echo "The total run time was " . $time . " seconds and " . $time2 . " seconds.";
	
****** Methods Available ******
* $benchmark	->setLoops($numberOfLoops)			// Sets the number of times to run your benchmark test.
* 				->useMethod($class, $method)		// Sets the class and method that you'll be testing.
* 				->useFunction($function)			// Sets the function that you'll be testing.
* 				->useParameters(<args...>)			// Sets the parameters to pass to the function you're testing.
*				->run()								// Runs the benchmark test.
*
* $benchmark->trackTime()				// Returns the time (in seconds) between now and the last ->trackTime()
*/

// $startTime = $_SERVER['REQUEST_TIME_FLOAT']; 

class Benchmark
{
	/****** Class Variables ******/
	public $loopCycles = 1000;
	public $testClass = false;
	public $testFunction = false;
	public $testParameters = array();
	
	public $storeMicrotime = 0;
	
	/****** Set Benchmark Loops ******
	* When benchmarking a particular function (to test it's speed), you generally need to run the function thousands
	* of times to get an idea of the average run time. This function allows you to determine how many times you would
	* like to run the function being benchmarked.
	*
	****** How to call the method ******
	* $benchmark->setLoops(25500);		// This will make the benchmark run the function 25500 times.
	* 
	****** Parameters ******
	* @integer	$numberOfLoops		The number of times the function should be run.
	* 
	* RETURNS <SELF>				Returns a self-instance of the class to allow method chaining.
	*/
	public function setLoops($numberOfLoops)
	{
		$this->loopCycles = $numberOfLoops;
		
		return $this;
	}
	
	/****** Set Benchmark Class (optional) ******
	* You can choose to benchmark a class method rather than a function. To do this, you must include the name of the
	* class (for abstract classes), or a object of that class (for classes that are instantiated).
	*
	****** How to call the method ******
	*
	* // For Abstract Classes, pass a string:
	* $benchmark->useMethod("Sanitize");		// One of the methods in the "Sanitize" class will now be our target.
	*
	* // For Initialized Classes, pass an object:
	* $benchmark->useMethod($imageObj);		// A method in this "Image" class object will now be our target.
	* 
	****** Parameters ******
	* @string or object		$class		The name of the class or a class object.
	* @string				$method		The name of the function being used.
	* 
	* RETURNS <SELF>					Returns a self-instance of the class to allow method chaining.
	*/
	public function useMethod($class, $method)
	{
		$this->testClass = $class;
		$this->testFunction = $method;
		
		return $this;
	}
	
	/****** Set Benchmark Function ******
	* This method allows you to set what function you are going to benchmark. If a class was set, the benchmark test
	* will look for a method in that class. Otherwise, it will just use a standard function.
	*
	****** How to call the method ******
	* $benchmark->useFunction(25500);		// This will make the benchmark run the function 25500 times.
	* 
	****** Parameters ******
	* @integer	$numberOfLoops		The number of times the functino should be run.
	* 
	* RETURNS <SELF>				Returns a self-instance of the class to allow method chaining.
	*/
	public function useFunction($function)
	{
		$this->testClass = false;
		$this->testFunction = $function;
		
		return $this;
	}
	
	/****** Set Parameters to use in the Benchmarking Function ******
	* Any parameter that is passed into this function will be passed into the benchmarking function that you're
	* testing.
	*
	****** How to call the method ******
	* $benchmark->useParameters("arg1", "arg2", "...");		// These will be used in the benchmarking function.
	* 
	****** Parameters ******
	* <Args>		<Any Type>		Add whatever arguments are appropriate to the benchmarking function.
	* 
	* RETURNS <SELF>				Returns a self-instance of the class to allow method chaining.
	*/
	public function useParameters()
	{
		$parameters = array();
		$args = func_get_args();
		
		foreach($args as $arg)
		{
			array_push($parameters, $arg);
		}
		
		$this->testParameters = $parameters;
		
		return $this;
	}
	
	/****** Run Benchmark ******
	* This method runs a benchmark on the desired method or function (using the other methods available). You must set
	* up the benchmark properly for it to operate.
	* 
	****** How to call the method ******
	* $benchmark = new Benchmark();
	* 
	* // This will run 200 times: Security::setPassword("myAwesomePassword", $userID, $dateJoined, "^^salt^^")
	* $time = $benchmark->setLoops(200)
	*					->useMethod("Security", "setPassword")
	*					->useParameters("myAwesomePassword", $userID, $dateJoined, "^^salt^^")
	*					->run();
	* 
	* echo "The total run time was " . $time . " seconds.";
	* 
	****** Parameters ******
	* RETURNS <float>			Returns the number of seconds the benchmark took to run (with four decimal places).
	*/
	public function run()
	{
		$start = microTime(true);
		
		// Test if the class used is an object or a string (for abstract classes)
		if((is_object($this->testClass) or class_exists($this->testClass)) && method_exists($this->testClass, $this->testFunction))
		{
			for($i = 0;$i < $this->loopCycles;$i++)
			{
				call_user_func_array(array($this->testClass, $this->testFunction), $this->testParameters);
			}
		}
		
		// If a class wasn't used, test the function
		elseif(function_exists($this->testFunction))
		{
			for($i = 0;$i < $this->loopCycles;$i++)
			{
				call_user_func_array($this->testFunction, $this->testParameters);
			}
		}
		
		$total = microTime(true) - $start;
		
		// Reset the benchmarking values to their defaults
		$this->loopCycles = 1000;
		$this->testClass = false;
		$this->testFunction = false;
		$this->testParameters = array();
		
		// Return the total time spent on the benchmark
		return number_format($total, 4);
	}
	
	/****** Retrieve Last Time Benchmark ******
	* This method returns the duration of time that has passed since the last ->trackTime() call. If a ->trackTime()
	* call has not been made yet, it returns the duration of time that passed since the page started loading.
	* 
	****** How to call the method ******
	* $benchmark = new Benchmark();
	*
	* echo $benchmark->trackTime();		// Returns the time (in seconds) passed since the page started loading.
	* echo $benchmark->trackTime();		// Returns the time (in seconds) since the last $benchmark->trackTime() call.
	* 
	****** Parameters ******
	* RETURNS <float>			Returns the time duration (in seconds) since the last $benchmark->trackTime() call.
	*/
	public function trackTime()
	{
		// If you haven't run ->trackTime() yet, default it to the original script time:
		$difference = ($this->storeMicrotime == 0 ? microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true) - $this->storeMicrotime);
		
		// Record our last benchmark so that it can be used later
		$this->storeMicrotime = microtime(true);
		
		return number_format($difference, 4);
	}
}