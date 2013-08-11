<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Number Class ******
* This allows you to manipulate numbers in useful ways, such as to translate integers to human words. This code is
* a modified and optimized version of code originally provided by Coder4web and wolfe on Stack Overflow.
* 
****** Methods Available ******
* Number::toWord($number)		// Changes an integer to a human word.
*/

abstract class Number {

/****** Change Number to Word ******/
	public static function toWord
	(
		$number		/* <int> The number that you'd like to translate to a word. */
	)				/* RETURNS <str> : Word (or phrase) of the number you translated. */
	
	// echo Number::toWord(1022);	// Outputs "one thousand twenty two"
	{
		// Make sure the number is a positive value
		$number = max(0, $number);
		
		// Strip any 0's from the front of the integer
		$number = ltrim($number, '0');
		
		// Get the length of the number
		$numberLength = strlen($number);
		
		if($numberLength == 1)
		{
			return self::oneDigitToWord($number);
		}
		else if($numberLength == 2)
		{
			return self::twoDigitsToWord($number);
		}
		else
		{
			// Prepare Return String
			$word = "";
			
			switch($numberLength)
			{
				case 5:
				case 8:
				case 11:
					
					// If the number is higher than zero, we append words as appropriate
					if($number[0] > 0)
					{
						$word = self::twoDigitsToWord($number[0] . $number[1]) . " " . self::getUnitDigit($numberLength, $number[0]) . " ";
						
						return $word . " " . self::toWord(substr($number, 2));
					}
					
					// If the number is a zero, we skip any additions
					else
					{
						return $word . " " . self::toWord(substr($number, 1));
					}
			}
			
			if($number[0] > 0)
			{
				$word = self::oneDigitToWord($number[0], " ") . " " . self::getUnitDigit($numberLength, $number[0]) . " ";
			}
			
			return $word . " " . self::toWord(substr($number, 1));
		}
	}
	
	
/****** Private Helper ******/
	private static function oneDigitToWord($number)
	{
		switch($number)
		{
			case 0:	return "";
			case 1:	return "one";
			case 2:	return "two";
			case 3:	return "three";
			case 4:	return "four";
			case 5:	return "five";
			case 6:	return "six";
			case 7:	return "seven";
			case 8:	return "eight";
			case 9:	return "nine";
		}
		
		return "";
	}
	
/****** Private Helper ******/
	private static function twoDigitsToWord($number)
	{
		// If the two digit number starts with "1"
		if($number[0] == 1)
		{
			switch($number[1])
			{
				case 0:	return "ten";
				case 1:	return "eleven";
				case 2:	return "twelve";
				case 3:	return "thirteen";
				case 4:	return "fourteen";
				case 5:	return "fifteen";
				case 6:	return "sixteen";
				case 7:	return "seventeen";
				case 8:	return "eighteen";
				case 9:	return "nineteen";
			}
		}
		
		// Prepare Variables
		$extraSpace = ($number[1] == 0 ? "" : " ");
		
		switch($number[0])
		{
			case 2:	return "twenty" . $extraSpace . self::oneDigitToWord($number[1]);                
			case 3:	return "thirty" . $extraSpace . self::oneDigitToWord($number[1]);
			case 4:	return "forty" . $extraSpace . self::oneDigitToWord($number[1]);
			case 5:	return "fifty" . $extraSpace . self::oneDigitToWord($number[1]);
			case 6:	return "sixty" . $extraSpace . self::oneDigitToWord($number[1]);
			case 7:	return "seventy" . $extraSpace . self::oneDigitToWord($number[1]);
			case 8:	return "eighty" . $extraSpace . self::oneDigitToWord($number[1]);
			case 9:	return "ninety" . $extraSpace . self::oneDigitToWord($number[1]);
		}
		
		return "";
	}
	
/****** Private Helper ******/
	private static function getUnitDigit($numberlen, $number)
	{
		switch($numberlen)
		{
			case 3:
			case 6:
			case 9:
			case 12:
				return "hundred";
			
			case 4:
			case 5:
				return "thousand";
			
			case 7:
			case 8:
				return "million";
			
			case 10:
			case 11:
				return "billion";
		}
		
		return "";
	}
}
