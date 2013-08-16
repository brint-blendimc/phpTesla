<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Text Class ******
* This allows you to manipulate text in useful ways, such as to remove broken characters.
* 
****** Methods Available ******
* Text::handleBrokenChars($text)		// Fixes any broken characters in $text (may be forced to delete them)
*/

abstract class Text {

/****** Change Smart Quotes to Regular Quotes ******/
	public static function handleBrokenChars
	(
		$text		/* <str> The text that contains broken characters. */
	)				/* RETURNS <str> : The original text with broken characters removed. */
	
	// echo Text::handleBrokenChars("“Hello“ he said.");		// Prevents the broken ? character issue.
	{
		return iconv("ISO-8859-1", "UTF-8//IGNORE", $text);
	}
	
}
