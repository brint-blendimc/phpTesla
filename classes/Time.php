<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Time Class ******
* This allows you to handle certain time functionality, such as using fuzzy time.
* 
****** Methods Available ******
* Time::fuzzy($timestamp)				// Shows fuzzy time.
*/

abstract class Time {

/****** Fuzzy Time - Change a Timestamp to Simple Time Reference (i.e. "last week") ******/
	public static function fuzzy
	(
		$timestamp		/* <int> The timestamp of the date to transfer to human-readable time. */
	)					/* RETURNS <str> : A simple, fuzzy time reference. */
	
	// echo Time::fuzzy(time() - 3600);	 // Outputs "one hour ago"
	{
		// Determine Fuzzy Time
		$timeDiff = $timestamp - time();
		
		// If the time difference is in the past, run the pastFuzzy() function
		if($timeDiff <= 0)
		{
			return self::pastFuzzy(abs($timeDiff), $timestamp);
		}
		
		return self::futureFuzzy($timeDiff, $timestamp);
	}
	

/****** Fuzzy Time (Future) - Private Helper ******/
	private static function futureFuzzy
	(
		$secondsUntil		/* <int> The duration (in seconds) until the due date. */
		$timestamp			/* <int> The time that the event is occuring. */
	)						/* RETURNS <str> : Returns the fuzzy time (or a standard, formatted time). */
	
	{
		// If the timestamp is within the next hour
		if($secondsUntil < 3600)
		{
			// If the timestamp was within the last half hour
			if($secondsUntil < 1200)
			{
				if($secondsUntil < 45)
				{
					if($secondsUntil < 10)
					{
						return "in a few seconds";
					}
					
					return "in less than a minute";
				}
				
				return "in " . Number::toWord(ceil($secondsUntil / 60)) . " minutes";
			}
			
			return "in " . Number::toWord(round($secondsUntil / 60, -1)) . " minutes";
		}
		
		// If the timestamp is within the next month
		else if($secondsUntil < 86400 * 30)
		{
			// If the timestamp is within the day
			if($secondsUntil < 72000)
			{
				$hoursUntil = round($secondsUntil / 3600);
				
				if($hoursUntil == 1)
				{
					return "in an hour";
				}
				else if($hoursUntil > 12 && date('d', time()) != date('d', $timestamp))
				{
					return "tomorrow";
				}
				
				return "in " . Number::toWord($hoursUntil) . " hours";
			}
			
			// If the timestamp is within a week
			else if($secondsUntil < 86400 * 7)
			{
				$daysUntil = round($secondsUntil / 86400);
				
				if($daysUntil == 1)
				{
					return "in a day";
				}
				
				return "in " . Number::toWord($daysUntil) . " days";
			}
			
			// If the time is listed sometime next week
			if(date('W', $timestamp) - date('W', time()) == 1)
			{
				return "next week";
			}
			
			$weeksUntil = round($secondsUntil / (86400 * 7));
			
			if($weeksUntil == 1)
			{
				return "in a week";
			}
			
			return "in " . Number::toWord($weeksUntil) . " weeks";
		}
		
		// If the timestamp was listed in the next year
		else if($secondsUntil < 86400 * 365)
		{
			$monthsUntil = round($secondsUntil / (86400 * 30));
			
			if($monthsUntil == 1)
			{
				if(date('m', $timestamp) - date('m', time()) == 1)
				{
					return "next month";
				}
				
				return "in a month";
			}
			
			return "in " . Number::toWord($monthsUntil) . " months";
		}
		
		// Return the timestamp as a "Month Year" style
		return date("F Y", $timestamp);
	}
	

/****** Fuzzy Time (Past) - Private Helper ******/
	private static function pastFuzzy
	(
		$secondsAgo			/* <int> The duration (in seconds) after the due date. */
		$timestamp			/* <int> The time that the event occurred. */
	)						/* RETURNS <str> : Returns the fuzzy time (or a standard, formatted time). */
	
	{
		// If the timestamp was within the last hour
		if($secondsAgo < 3600)
		{
			// If the timestamp was within a minute or so
			if($secondsAgo <= 90)
			{
				if($secondsAgo < 50)
				{
					if($secondsAgo < 15)
					{
						return "just now";
					}
					
					return "seconds ago";
				}
				
				return "a minute ago";
			}
			else if($secondsAgo < 1200)
			{
				return Number::toWord(round($secondsAgo / 60)) . " minutes ago";
			}
			
			return floor($secondsAgo / 60) . " minutes ago";
		}
		
		// If the timestamp was within the last week
		elseif($secondsAgo < 86400 * 7)
		{
			// Several Hours Ago
			if($secondsAgo < 36000)
			{
				if($secondsAgo < 7200)
				{
					return "an hour ago";
				}
				
				return Number::toWord(floor($secondsAgo / 3600)) . " hours ago";
			}
			
			// Yesterday (or, several hours ago as a fallback)
			else if($secondsAgo < 72000)
			{
				if(date('d', time()) != date('d', $timestamp))
				{
					return "yesterday";
				}
				
				return Number::toWord(floor($secondsAgo / 3600)) . " hours ago";
			}
			
			// Days Ago
			if($secondsAgo < 86400 * 1.5)
			{
				return "a day ago";
			}
			
			return Number::toWord(round($secondsAgo / 86400)) . " days ago";
		}
		
		// If the timestamp was within the last month
		else if($secondsAgo < 86400 * 30)
		{
			// If the time was listed sometime last week
			if(date('W', time()) - date('W', $timestamp) == 1)
			{
				return "last week";
			}
			
			$weeksAgo = $secondsAgo / (86400 * 7);
			
			if($weeksAgo == 1)
			{
				echo "a week ago";
			}
			
			return Number::toWord(round($weeksAgo)) . " weeks ago";
		}
		
		// Any other timestamp time
		else
		{
			// If it's the same year:
			if(date('y', time()) === date('y', $timestamp))
			{
				$monthsAgo = date('m', time()) - date('m', time() - $secondsAgo);
				
				if($monthsAgo == 0)
				{
					return "early this month";
				}
				else if($monthsAgo == 1)
				{
					return "last month";
				}
				else if($monthsAgo <= 3)
				{
					return Number::toWord($monthsAgo) . " months ago";
				}
				
				return "this " . date('F', $timestamp);
			}
			
			// If it wasn't the same year
			$yearsAgo = date('Y', time()) - date('Y', $timestamp);
			
			if($yearsAgo == 1)
			{
				return "last " . date('F', $timestamp);
			}
		}
		
		// Return the timestamp as a "Month Year" style
		return date("F Y", $timestamp);
	}
}
