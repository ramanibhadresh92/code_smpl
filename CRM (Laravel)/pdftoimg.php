<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// print_r($_SERVER);
// die;

// create Imagick object
$imagick = new Imagick();
// Reads image from PDF

$SOURCEFILE 		= isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:"";
$DESTINATIONFILE 	= isset($_SERVER['argv'][2])?$_SERVER['argv'][2]:"";

if (!empty($SOURCEFILE) && file_exists($SOURCEFILE) && !empty($DESTINATIONFILE))
{
	$imagick->readImage($SOURCEFILE);
	// $imagick->resetIterator();
	// $combined = $imagick->appendImages(false);

	// Writes an image or image sequence Example- converted-0.jpg, converted-1.jpg
	// $combined->writeImages($DESTINATIONFILE, false);
	$imagick->setCompressionQuality(1);
	$imagick->setResolution(1024,768);
	$imagick->writeImages($DESTINATIONFILE, false);
	$LOG_DIR 	= __DIR__."/storage/logs/pdftoimg.log";
	$COMMAND	= "chmod 777 -f ".$DESTINATIONFILE;
	if (file_exists($DESTINATIONFILE)) exec($COMMAND . " > ".$LOG_DIR." &"); //chmod($DESTINATIONFILE,0777);
}
?>