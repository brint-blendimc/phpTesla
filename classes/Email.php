<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Email Class ******
* This class allows you to send emails, as well as provides handling for attachments.
* 
****** In-Practice Examples ******

// Emails "someone@hotmail.com" with an important update.
Email::send("someone@hotmail.com", "Super Important", "Dude, this is incredibly important!!!");

****** Methods Available ******
* Email::send($emailTo, $subject, $message, [$emailFrom], [$headers])						// Sends an email
* Email::sendAttachment($emailTo, $subject, $message, $filepath, $filename, [$emailFrom])	// Email with attachment
*/

abstract class Email {

/****** Sends a Simple Email ******/
	public static function send
	(
		$emailTo			/* <array> or <str> The email(s) you're sending a message to. */,
		$subject			/* <str> The subject of the message. */,
		$message			/* <str> The content of your message. */,
		$emailFrom = ""		/* <str> An email that you would like to send from. */,
		$headers = ""		/* <str> A set of headers in a single string (use cautiously). */
	)						/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// Email::send(array("somebody@email.com"), "Greetings!", "This welcome message will make you feel welcome!")
	{
		// Determine the Email being sent from
		if($emailFrom == "")
		{
			$emailFrom = WEBMASTER_EMAIL;
		}
		
		// Handle Multiple Recipients
		if(is_array($emailTo))
		{
			$emailTo = implode(", ", $emailTo);
		}
		
		// Handle the Email Headers
		if($headers == "")
		{
			$headers = 'From: ' . $emailFrom . "\r\n" .
			'Reply-To: ' . $emailFrom . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
		}
		
		// Send the Mail
		if(!mail($emailTo, $subject, $message, $headers))
		{
			return false;
		}
		
		return true;
	}
	
/****** Sends an Email with an Attachment ******
Most credit goes to dqhendricks on Stack Overflow. */
	public static function sendAttachment
	(
		$emailTo			/* <array> or <str> The email(s) you're sending a message to. */,
		$subject			/* <str> The subject of the message. */,
		$message			/* <str> The content of your message. */,
		$filePath			/* <str> The file path to the attachment you're sending. */,
		$filename			/* <str> The name of the file as you'd like it to appear. */,
		$emailFrom = ""		/* <str> An email that you would like to send from. */
	)						/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// Email::sendAttachment("joe@email.com", "Hi!", "Sup!?", "./assets/file.csv", "excelPage.csv"])
	// May use: $_FILES["file"]["tmp_name"] and $_FILES["file"]["name"]
	{
		// Determine if you're sending to more than one email
		if(is_array($emailTo))
		{
			$emailTo = implode(", ", $emailTo);
		}
		
		// Determine the Email being sent from
		if($emailFrom == "")
		{
			$emailFrom = WEBMASTER_EMAIL;
		}
		
		// $filePath should include path and filename
		$filename = basename($filename);
		$file_size = filesize($filePath);
		
		$content = chunk_split(base64_encode(file_get_contents($filePath))); 
		
		$uid = md5(uniqid(time()));
		
		// Designed to prevent email injection, although we should run stricter validation if we're going to allow
		// other people to insert emails into the email.
		$emailFrom = str_replace(array("\r", "\n"), '', $emailFrom);
		
		// Prepare header
		$header = "From: ".$emailFrom."\r\n"
			."MIME-Version: 1.0\r\n"
			."Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n"
			."This is a multi-part message in MIME format.\r\n" 
			."--".$uid."\r\n"
			."Content-type:text/plain; charset=iso-8859-1\r\n"
			."Content-Transfer-Encoding: 7bit\r\n\r\n"
			.$message."\r\n\r\n"
			."--".$uid."\r\n"
			."Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"
			."Content-Transfer-Encoding: base64\r\n"
			."Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n"
			.$content."\r\n\r\n"
			."--".$uid."--";
			
		// Send the email
		return mail($emailTo, $subject, "", $header);
	}
	
}
