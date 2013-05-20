<?php if(!defined("IS_SAFE")) { die("No direct script access allowed."); }

/****** Testing: Sanitize Class ******
* This tests runs several validations of the Sanitize class to ensure it's reliability. These tests should be run
* after performing functions within the Sanitize class to verify that everything is still operational.
* 
****** Methods Available ******
* $testSanitize->word()
* $testSanitize->word_extraChars()
* $testSanitize->variable()
* $testSanitize->filepath()
* $testSanitize->filepath_denyExtension()
* $testSanitize->filepath_detectNullBtye()
* $testSanitize->filepath_detectParentPath()
*/

class TestSanitize extends Testing
{
	public function word()
	{
		$param = array(
			"ab]c1d>?.,ef2ghij3kl#mn}opq{4rst1uvwxy5@zAB6=+|CD#EFG[HIJ7K%LM*N8O^<PQR9STUVW0XYZ_"
		);
		
		return $this->validate(
			/* Class Being Tested */		"Sanitize"
			/* Method Being Tested */,		"word"
			/* Summary of Test */,			"Make sure that whitelist sanitization is working properly."
			
			/* Expected Return Value */,	"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
			/* Actual Return Value */,		Sanitize::word($param[0])
			
			/* Parameter List */,			$param
		);
	}
	
	public function word_extraChars()
	{
		$param = array(
			"abcDEF!@#%^&*()",
			"!@#%^&*()"
		);
		
		return $this->validate(
			/* Class Being Tested */		"Sanitize"
			/* Method Being Tested */,		"word"
			/* Summary of Test */,			'$extraChars allows custom characters to be added to the whitelist.'
			
			/* Expected Return Value */,	$param[0]
			/* Actual Return Value */,		Sanitize::word($param[0], $param[1])
			
			/* Parameter List */,			$param
		);
	}
	
	public function variable()
	{
		$param = array(
			"my_Class101_*@",
			"@*"
		);
		
		return $this->validate(
			/* Class Being Tested */		"Sanitize"
			/* Method Being Tested */,		"variable"
			/* Summary of Test */,			'Ensure the variable sanitization method is working properly.'
			
			/* Expected Return Value */,	$param[0]
			/* Actual Return Value */,		Sanitize::variable($param[0], $param[1])
			
			/* Parameter List */,			$param
		);
	}
	
	public function filepath()
	{
		$param = array(
			"./images/my-Image.png"
		);
		
		return $this->validate(
			/* Class Being Tested */		"Sanitize"
			/* Method Being Tested */,		"filepath"
			/* Summary of Test */,			'Simple file path validation with "./images/my-Image.png".'
			
			/* Expected Return Value */,	"./images/my_Image.png"
			/* Actual Return Value */,		Sanitize::filepath($param[0])
			
			/* Parameter List */,			$param
		);
	}
	
	public function filepath_denyExtension()
	{
		$param = array(
			"./images/someImage.bmp",
			array("png", "jpg", "gif")
		);
		
		return $this->validate(
			/* Class Being Tested */		"Sanitize"
			/* Method Being Tested */,		"filepath"
			/* Summary of Test */,			'Protects against illegal file extensions.'
			
			/* Expected Return Value */,	false
			/* Actual Return Value */,		Sanitize::filepath($param[0], $param[1])
			
			/* Parameter List */,			$param
		);
	}
	
	public function filepath_detectNullBtye()
	{
		$param = array(
			"./images/someImage.php" . chr(0) . ".bmp"
		);
		
		return $this->validate(
			/* Class Being Tested */		"Sanitize"
			/* Method Being Tested */,		"filepath"
			/* Summary of Test */,			'Detects null byte attacks.'
			
			/* Expected Return Value */,	false
			/* Actual Return Value */,		Sanitize::filepath($param[0])
			
			/* Parameter List */,			$param
		);
	}
	
	public function filepath_detectParentPath()
	{
		$param = array(
			"./images/../../../etc/passwd"
		);
		
		return $this->validate(
			/* Class Being Tested */		"Sanitize"
			/* Method Being Tested */,		"filepath"
			/* Summary of Test */,			'Detects parent path injections.'
			
			/* Expected Return Value */,	false
			/* Actual Return Value */,		Sanitize::filepath($param[0])
			
			/* Parameter List */,			$param
		);
	}
}