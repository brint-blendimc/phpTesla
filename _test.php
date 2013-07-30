<?php

/****** Script Preparation ******/
require_once("./config.php");


$plugin = new Plugin();
//echo $plugin->tasks->project->create("Test Group");
//echo $plugin->tasks->group->create(1, "Test Group");
//echo $plugin->tasks->group->delete(1);

var_dump(isSanitized::variable("te3stsetsefsdf"));

var_dump($data);

/*
Database::initialize('test', 'root', '');


User::createUserTable();

User::register("Joe", "myPassword", "joe@hotmail.com");
User::login("Joe", "myPassword");

$val = Database::selectMultiple("SELECT * FROM users", array());
var_dump($val);
Database::tableExists("something");


$benchmark = new Benchmark();
$time = $benchmark	->setLoops(5000)
						->useMethod("Sanitize", "whitelist")	// Can also send object: ->useMethod($imgObj, "shrink")
						->useParameters("A special value to sanitize", "abcdefghijklm")
						->run();
						
echo $time;
*/
/*
$benchmark = new Benchmark();

$time = $benchmark	->setLoops(5)
					->useMethod('Database', "tableExists")	// Can also send object: ->useMethod($imgObj, "shrink")
					->useParameters("test")
					->run();
					
echo "Time: " . $time;
*/

/*
function myFunction($a, $b, $c)
{
	$blah = "";
	if($a == "b" or isset($c)) { $blah .= str_replace("-", " ", "abcdefghijkjs dofij aosdijf oaiewf oaidj foisjdf a sodifj asodifj oasdij foaisjd foaij dsfoiasj dfoiaj dsoifj aosdijf aosdfij "); }
	$blah .= str_replace("-", " ", "abcdefghijkjs dofij aosdijf oaiewf oaidj foisjdf ");
	return $blah;
}


$benchmark = new Benchmark();

echo "Time: " . $benchmark->trackTime() . "<br />";

for($i = 0;$i <= 10000;$i++)
{
	defined("ALLOW_SCRIPT_YES");
}

echo "<br />Time: " . $benchmark->trackTime() . " seconds<br />";
*/

// 0.2931 seconds, 0.3031 seconds, 0.2801 seconds
// 0.3657 seconds, 0.3675 seconds, 0.3658 seconds, 0.3403 seconds

// 0.0311 seconds, 0.0268 seconds, 0.0168 seconds
// 0.3620 seconds, 0.3799 seconds, 0.3454 seconds, 0.3759 seconds


/*
// This will run 5000 times: Sanitize::whitelist("A special value to sanitize", "abcdefghijklm")
$time = $benchmark	->setLoops(10000)
					->useMethod("Sanitize", "whitelist")	// Can also send object: ->useMethod($imgObj, "shrink")
					->useParameters("A special value to sanitize", "abcdefghijklm")
					->run();

// This will run 13500 times: myFunction("arg1", "arg2", "arg3")
$time2 = $benchmark ->setLoops(10000)
					->useFunction("myFunction")
					->useParameters("arg1", "arg2", "arg3")
					->run();

echo "The total run time was " . $time . " seconds and " . $time2 . " seconds.";

echo "<br />Time: " . $benchmark->trackTime() . "<br />";

// This will run 5000 times: Sanitize::whitelist("A special value to sanitize", "abcdefghijklm")
$time = $benchmark	->setLoops(1000)
					->useMethod("Sanitize", "whitelist")	// Can also send object: ->useMethod($imgObj, "shrink")
					->useParameters("A special value to sanitize", "abcdefghijklm")
					->run();

// This will run 13500 times: myFunction("arg1", "arg2", "arg3")
$time2 = $benchmark ->setLoops(10000)
					->useFunction("myFunction")
					->useParameters("arg1", "arg2", "arg3")
					->run();

echo "The total run time was " . $time . " seconds and " . $time2 . " seconds.";

echo "<br />Time: " . $benchmark->trackTime() . "<br />";

*/
