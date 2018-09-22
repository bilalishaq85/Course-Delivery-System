<?php
	ob_start(); ini_set('output_buffering','1'); 
	session_start();
	$imgWidth=200;
	$imgHeight=45;
	$caplenmin=5;
	$caplenmax=7;
	$imgMargin=10;
	$fontsize=22;
	$str="123456789abcdefghijklmnopqrstuvwxyz";
	#
	$caplen=rand($caplenmin, $caplenmax);
	$image = @imagecreatetruecolor($imgWidth, $imgHeight) or die(showError("Cannot Initialize new GD image stream"));
	
	// background color
	#$background = imagecolorallocate($image, 0x66, 0xCC, 0xFF);
	$background = imagecolorallocate($image, 0x55, 0xCC, 0x55);
	
	imagefill($image, 0, 0, $background);
	#$linecolor = imagecolorallocate($image, 0x33, 0x99, 0xCC);
	$linecolor = imagecolorallocate($image, 0x22, 0x99, 0x22);
		
	$txtcols = array();
	$txtcols[] = imagecolorallocate($image, 0x33, 0x33, 0x33);		//Black
	$txtcols[] = imagecolorallocate($image, 0xEE, 0xEE, 0xEE);		//White
	$txtcols[] = imagecolorallocate($image, 0xED, 0x87, 0x2D);		//Orange
	$txtcols[] = imagecolorallocate($image, 0x99, 0x22, 0x22);		//Red
	$txtcols[] = imagecolorallocate($image, 0x55, 0xFF, 0x55);		//Green
	$txtcols[] = imagecolorallocate($image, 0x22, 0x22, 0x99);		//Blue
	$txtcols[] = imagecolorallocate($image, 0x77, 0x77, 0x77);		//Grey (Light)
	$txtcols[] = imagecolorallocate($image, 0x99, 0x32, 0xCC);		//Grey (Dark)

	// random lines
	for($i=0; $i < 25; $i++) {
	  imagesetthickness($image, rand(1,3));
	  imageline($image, rand(0,$imgWidth), 0, rand(0,$imgWidth), $imgHeight, $linecolor);
	}
	
	// TTF fonts
	$fonts = array();
	$fonts[] = "fonts/DejaVuSerif-Bold.ttf";
	$fonts[] = "fonts/DejaVuSans-Bold.ttf";
	$fonts[] = "fonts/DejaVuSansMono-Bold.ttf";
	$fonts[] = "fonts/DejaVuMathTeXGyre.ttf";
	
	$captcha = '';
	for($i = $imgMargin; $i <= $imgWidth-$imgMargin*2; $i += ($imgWidth-$imgMargin)/$caplen) {
	  $textcolor = $txtcols[array_rand($txtcols)];
	  $captcha.= ($num = $str[array_rand(str_split($str))]);
	  imagettftext($image, $fontsize, rand(-30,30), $i, rand($fontsize*1.5, $imgHeight-$fontsize), $textcolor, $fonts[array_rand($fonts)], $num);
	}
	$_SESSION['captcha'] = $captcha;
	
	// display image and release memory
	header('Content-type: image/png');
	imagepng($image);
	imagedestroy($image);
?>
