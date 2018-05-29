<?php

session_start();

$text = substr(md5(microtime()),mt_rand(0,26),4);
$_SESSION['captcha'] = $text;
$tt_image = imagecreate(95, 40); 
$white = imagecolorallocate($tt_image, 255, 255, 255); 
$black = imagecolorallocate($tt_image, 0, 0, 0); 
$font_size = 22;
$ttfont = "resources/fonts/times_new_yorker.ttf";
imagettftext($tt_image, $font_size, 0, 7, 30, $black, $ttfont, $text);

/* Avoid Caching */
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header( "Content-type: image/png" );

imagepng($tt_image);
imagedestroy($tt_image);
exit();