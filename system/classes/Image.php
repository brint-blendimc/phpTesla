<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Image Class ******
* This class provides several methods for manipulating images.
* 
****** Example of Use ******

$image = Image::create("./images/image.png");
Image::trimTransparency($image);
Image::crop($image, 10, 10, 80, 80);
Image::changeHue($image, 45);
Image::display($image);

****** Methods Available ******
* Image::upload($path, $filename, $type, $size, $tmpFile, $permittedMIME);		// Uploads an image.
* 
* Image::create($image)							// Creates an image object.
* Image::crop($image, $x, y, $toX, $toY)		// Crops an image based on the dimensions provided
* Image::trimTransparency(&$image)				// Crops off any transparent edges
* Image::rescale($image, $newWidth, $newHeight, $x, $y, $x2, $y2);		// Rescales the image.
* Image::display(&$image)						// Displays the image directly to the screen
* Image::swapColors(&$image, $swap)				// Switch colors on an image
* Image::changeHue(&$image, $angle)				// Changes the hue of the image up to 360 degrees
*/

abstract class Image {

	public static $maxFileSize = 102400;	// 100 kilobtyes

/****** Upload an Image ******/
	public static function upload
	(
		$imagePath			/* <str> The path that leads to the image. */,
		$imageFilename		/* <str> The filename of the image itself. */,
		$imageType			/* <str> The type of the image (png, jpg, gif, etc). */,
		$imageSize			/* <int> The size of the image in bytes. */,
		$imageTempFile		/* <str> The path where the temporary image was located. */,
		
							/* <array> Array of permitted mime types. */
		$permittedMIME = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png')
		
	)						/* RETURNS <mixed> : TRUE on success, ERROR MESSAGE on failure. */
	
	// Image::upload("./path/to/folder", "myImage.png", "image/png", 10000, $_FILES['images']['tmp_name'])
	{
		// Confirm that the directory exists (otherwise create it)
		$imagePath = rtrim($imagePath, "/") . "/";
		Dir::create($imagePath);
		
		// Make sure the image name is sanitized
		$imageFilename = str_replace(" ", "_", $imageFilename);
		$imageFilename = Sanitize::variable($imageFilename, ".");
		
		if($imageFilename == "" or strlen($imageFilename) < 5 or strpos($imageFilename, '.') < 2)
		{
			return "The image name is too short.";
		}
		
		// Check if the uploaded file is actually an image
		if(!in_array($imageType, $permittedMIME))
		{
			return "You may not upload that type of image.";
		}
		
		// Check if a file of the same name has been uploaded
		if(File::exists($imagePath . $imageFilename))
		{
			return "An image already exists with that name.";
		}
		
		// Check the image size
		if($imageSize <= 0 or $imageSize > self::$maxFileSize)
		{
			return "The file size must be smaller than " . self::$maxFileSize . " bytes.";
		}
		
		// Move the image into the appropriate directory (with the new name)
		if(!move_uploaded_file($imageTempFile, $imagePath . $imageFilename))
		{
			return "There was an error uploading this image. Please try again.";
		}
		
		return true;
	}
	

/****** Create Image Object from Path ******/
	public static function create
	(
		$imagePath		/* <str> The path of the image you'd like to create. */
	)					/* RETURNS <image object>  */
	
	// $image = Image::create("image.png");
	{
		return imagecreatefrompng($imagePath);
	}
	

/****** Prepare a New Image ******/
	public static function createBase
	(
		$width			/* <int> The width of the image you'd like to create. */,
		$height			/* <int> The height of the image you'd like to create. */
	)					/* RETURNS <void>  */
	
	// $image = Image::createBase(100, 100);
	{
		$image = imagecreatetruecolor($width, $height);
		$background_color = imagecolorallocatealpha($image, 0, 255, 0, 127);   #($im, 130, 130, 77);
		imagefill($image, 0, 0, $background_color);
		imagecolortransparent($image, $background_color);
		
		return $image;
	}
	
	
/****** Crop Image ******/
	public static function crop
	(
		&$image		/* <image object> The image object you want to crop. */,
		$x			/* <int> The X position to start cropping at. */,
		$y			/* <int> The Y position to start cropping at. */,
		$toX		/* <int> The X position to stop cropping at. */,
		$toY		/* <int> The Y position to stop cropping at. */
	)				/* RETURNS <void> */
	
	// Image::crop($image, $x, $y, $toX, $toY);
	{
		// Cropped Dimensions
		$cropWidth = abs($toX - $x);
		$cropHeight = abs($toY - $y);
		
		// Crop the Image
		$croppedImage = imagecreatetruecolor($cropWidth, $cropHeight);
		$transColor = imagecolorallocatealpha($croppedImage, 0, 0, 0, 127);
		imagefill($croppedImage, 0, 0, $transColor);
		imagecopyresampled($croppedImage, $image, 0, 0, $x, $y, $cropWidth, $cropHeight, $cropWidth, $cropHeight);
		
		// Set the new cropped image
		$image = $croppedImage;
		
		// Clear Memory
		unset($croppedImage);
	}
	
	
/****** Crop Transparent Edges Image ******/
	public static function trimTransparency
	(
		&$image		/* <image object> The image to crop. */
	)				/* RETURNS <void> */
	
	// Image::trimTransparency($image);
	{
		// Get dimensions of the image
		$width = imagesx($image);
		$height = imagesy($image);
		
		list($leftMost, $rightMost, $topMost, $bottomMost) = self::findCropbox($image, 0, 0, $width - 1, $height - 1, 7);
		
		// If the inner crop-check rectangle didn't detect any spots, realign to make sense
		if($leftMost > $rightMost) { $rightMost = $leftMost; }
		if($topMost > $bottomMost) { $bottomMost = $topMost; }
		
		// Create the four boxes that you'll scan through
		
		// Scan Crop Boxes
		if($leftMost > 0)
		{
			list($getLeft, $getRight, $getTop, $getBottom) = self::findCropbox($image, 0, 0, $leftMost, $height - 1, true);
			
			$leftMost = ($getLeft < $leftMost ? $getLeft : $leftMost);
			$topMost = ($getTop < $topMost ? $getTop : $topMost);
			$bottomMost = ($getBottom > $bottomMost ? $getBottom : $bottomMost);
		}
		
		if($rightMost < $width - 1)
		{
			list($getLeft, $getRight, $getTop, $getBottom) = self::findCropbox($image, $rightMost, 0, $width - 1, $height - 1, true);
			
			$rightMost = ($getRight > $rightMost ? $getRight : $rightMost);
			$topMost = ($getTop < $topMost ? $getTop : $topMost);
			$bottomMost = ($getBottom > $bottomMost ? $getBottom : $bottomMost);
		}
		
		if($topMost > 0)
		{
			list($getLeft, $getRight, $getTop, $getBottom) = self::findCropbox($image, $leftMost, 0, $rightMost, $topMost, true);
			
			$topMost = ($getTop < $topMost ? $getTop : $topMost);
		}
		
		if($bottomMost < $height - 1)
		{
			list($getLeft, $getRight, $getTop, $getBottom) = self::findCropbox($image, $leftMost, $bottomMost, $rightMost, $height - 1, true);
			
			$bottomMost = ($getBottom > $bottomMost ? $getBottom : $bottomMost);
		}
		
		// Trim all of the transparency from the image
		self::crop($image, $leftMost, $topMost, $rightMost, $bottomMost);
	}
	
	
/****** Find the Crop Box (used by trimTransparency()) ******/
	private static function findCropbox
	(
		&$image					/* <image object> The image that you want to return a cropbox indicator. */,
		$posX					/* <int> The x position of where to start searching on the image. */,
		$posY					/* <int> The y position of where to start searching on the image. */,
		$posX2					/* <int> The x position of where to stop searching. */,
		$posY2					/* <int> The y position of where to stop searching. */,
		$gridSize = 5			/* <int> Precision of the grid search (larger number = less precise, true = exact) */
	)					/* RETURNS <array> : list($x, $y, $x2, $y2) - the rectangle of detected boxes.  */
	
	// $image = Image::findCropbox($image, $leftMost, $topMost, $rightMost, $bottomMost);
	{
		// Prepare Important Values for the Image Grid
		$leftMost = $posX2;
		$rightMost = $posX;
		$topMost = $posY2;
		$bottomMost = $posY;
		
		if($gridSize === true)
		{
			$widthIntervals = 1;
			$heightIntervals = 1;
		}
		else
		{
			$widthIntervals = ($posX2 - $posX) / ($gridSize - 1);
			$heightIntervals = ($posY2 - $posY) / ($gridSize - 1);
		}
		
		// Search every 1/Xth of the image grid to speed up the crop-checking process.
		for($x = $posX;$x <= $posX2 + 1;$x += $widthIntervals)
		{
			for($y = $posY;$y <= $posY2;$y += $heightIntervals)
			{
				// Restrict to edges if applicable
				$getX = min(ceil($x), $posX2);
				$getY = min(ceil($y), $posY2);
				
				// Retrieve the color at the current location
				$rgb = imagecolorat($image, $getX, $getY);
				
				// Translate the colors to RGB values
				$alpha = ($rgb & 0x7F000000) >> 24;
				
				if($alpha != 127)
				{
					// Determine the borders of the inner rectangle that you want to crop-check.
					
					// Set Horizontal Values (Crop)
					if($getX < $leftMost) { $leftMost = $getX; }
					if($getX > $rightMost) { $rightMost = $getX; }
					
					// Set Vertical Values (Crop)
					if($getY < $topMost) { $topMost = $getY; }
					if($getY > $bottomMost) { $bottomMost = $getY; }
				}
			}
		}
		
		return array($leftMost, $rightMost, $topMost, $bottomMost);
	}
	
	
/****** Rescale Image ******/
	public static function rescale
	(
		&$image			/* <image object> The image object you want to crop. */,
		$newWidth		/* <int> The new width of the image (scaled proportionally). */,
		$newHeight		/* <int> The new height of the image (scaled proportionally). */,
		$x = 0			/* <int> The upper-left X boundary for what part of your image you want to rescale. */,
		$y = 0			/* <int> The upper-left Y boundary for what part of your image you want to rescale. */,
		$x2 = 0			/* <int> The bottom-right X boundary to rescale. (default is max width) */,
		$y2 = 0			/* <int> The bottom-right Y boundary to rescale. (default is max width0 */
	)					/* RETURNS <void> */
	
	// Image::rescale($image, $newWidth, $newHeight, $x, $y, $x2, $y2);
	{
		// Get dimensions of the image
		$width = ($x2 <= $x ? imagesx($image) - $x : $x2 - $x);
		$height = ($y2 <= $x ? imagesy($image) - $y : $y2 - $y);
		
		// Prepare New Rescaled Image
		$imageNew = imagecreatetruecolor($newWidth, $newHeight);
		$transColor = imagecolorallocatealpha($imageNew, 0, 0, 0, 127);
		imagefill($imageNew, 0, 0, $transColor);
		
		// Rescale Image
		$scaledImage = imagecreatetruecolor($newWidth, $newHeight);
		imagesavealpha($scaledImage, true);
		imagefill($scaledImage, 0, 0, $transColor);
		imagecopyresampled($scaledImage, $image, 0, 0, $x, $y, $newWidth, $newHeight, $width, $height);
		
		// Rescale the Image you're working with
		$image = $scaledImage;
		
		// Clear Memory
		unset($scaledImage);
		unset($imageNew);
	}
	
	
/****** Display Image ******/
	public static function display
	(
		&$image			/* <image> The image you'd like to output directly to the browser. */
	)					/* RETURNS <true>  */
	
	// Image::display($image);
	{
		header("Content-Type: image/png");
		
		imagealphablending($image, true);
		imagesavealpha($image, true);
		
		imagepng($image);
		imagedestroy($image);
		
		return true;
	}
	
	
/****** Swap Colors in an Image ******/
	public static function swapColors
	(
		&$image			/* <image> The image you'd like to modify. */,
		$swap			/* <array> A list of colors you'd like to swap to different colors. */
	)					/* RETURNS <true>  */
	
	// Image::swapColors($image, array('25,0,255' => '10,200,20'));
	{
		// Get Important Variables
		$width = imagesx($image);
		$height = imagesy($image);
		
		// Cycle through every pixel in the image
		for($x = 0;$x < $width;$x++)
		{
			for($y = 0;$y < $height;$y++)
			{
				// Retrieve the color at the current location
				$rgb = imagecolorat($image, $x, $y);
				
				// Translate the colors to RGB values
				$alpha	= ($rgb & 0x7F000000) >> 24;
				$r		= ($rgb >> 16) & 0xFF;
				$g		= ($rgb >> 8) & 0xFF;
				$b		= $rgb & 0xFF;
				
				if($r == 255 && $g == 0 && $b == 255)
				{
					imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, 10, 100, 200, 50));
				}
			}
		}
	}
	
	
/****** Change Hue of Image ******/
// Note: This was modified from an original script by Tatu Ulmanen on Stack Overflow.
	public static function changeHue
	(
		&$image			/* <image> The image that you'd like to change the hue of. */,
		$angle			/* <int> The degree of hue shift you'd like to change, up to 360 degrees. */
	)					/* RETURNS <void> */
	
	// $image = Image::create(100, 100);
	// Image::changeHue($image, 180);
	{
		// If the hue shift is irrelevant (i.e. if it's the same image), then return the normal image
		if($angle % 360 == 0)
		{
			return;
		}
		
		// Get Important Variables
		$width = imagesx($image);
		$height = imagesy($image);
		
		// Loop through every pixel
		for($x = 0; $x < $width; $x++)
		{
			for($y = 0; $y < $height; $y++)
			{
				// For each pixel, determine the color
				$rgb = imagecolorat($image, $x, $y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;            
				$alpha = ($rgb & 0x7F000000) >> 24;
				list($h, $s, $l) = self::changeRGBtoHSL($r, $g, $b);
				
				// For each pixel, provide a new pixel with appropriate hue shift
				$h += $angle / 360;
				if($h > 1) $h--;
				list($r, $g, $b) = self::changeHSLtoRGB($h, $s, $l);            
				imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, $r, $g, $b, $alpha));
			}
		}
	}

/****** Private Helper - Transmute RGB to HSL ******/
	private static function changeRGBtoHSL($r, $g, $b)
	{
		$var_R = ($r / 255);
		$var_G = ($g / 255);
		$var_B = ($b / 255);
		
		$var_Min = min($var_R, $var_G, $var_B);
		$var_Max = max($var_R, $var_G, $var_B);
		$del_Max = $var_Max - $var_Min;
		
		$v = $var_Max;
		
		if($del_Max == 0)
		{
			$h = 0;
			$s = 0;
		}
		else
		{
			$s = $del_Max / $var_Max;

			$del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
			$del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
			$del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

			if      ($var_R == $var_Max) $h = $del_B - $del_G;
			else if ($var_G == $var_Max) $h = ( 1 / 3 ) + $del_R - $del_B;
			else if ($var_B == $var_Max) $h = ( 2 / 3 ) + $del_G - $del_R;

			if ($h < 0) $h++;
			if ($h > 1) $h--;
		}
		
		return array($h, $s, $v);
	}

/****** Private Helper - Transmute HSL back to RGB ******/
	private static function changeHSLtoRGB($h, $s, $v)
	{
		if($s == 0)
		{
			$r = $g = $B = $v * 255;
		}
		else
		{
			$var_H = $h * 6;
			$var_i = floor( $var_H );
			$var_1 = $v * ( 1 - $s );
			$var_2 = $v * ( 1 - $s * ( $var_H - $var_i ) );
			$var_3 = $v * ( 1 - $s * (1 - ( $var_H - $var_i ) ) );
			
			if       ($var_i == 0) { $var_R = $v     ; $var_G = $var_3  ; $var_B = $var_1 ; }
			else if  ($var_i == 1) { $var_R = $var_2 ; $var_G = $v      ; $var_B = $var_1 ; }
			else if  ($var_i == 2) { $var_R = $var_1 ; $var_G = $v      ; $var_B = $var_3 ; }
			else if  ($var_i == 3) { $var_R = $var_1 ; $var_G = $var_2  ; $var_B = $v     ; }
			else if  ($var_i == 4) { $var_R = $var_3 ; $var_G = $var_1  ; $var_B = $v     ; }
			else                   { $var_R = $v     ; $var_G = $var_1  ; $var_B = $var_2 ; }
			
			$r = $var_R * 255;
			$g = $var_G * 255;
			$B = $var_B * 255;
		}
		
		return array($r, $g, $B);
	}

}