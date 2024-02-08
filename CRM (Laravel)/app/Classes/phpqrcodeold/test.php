<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include $_SERVER["DOCUMENT_ROOT"]."/phpqrcode/qrlib.php";
$img = $_SERVER["DOCUMENT_ROOT"]."/phpqrcode/temp/test.png";
// create a QR Code with this text and display it
// and to generate image
$data = QRcode::png("THIS IS TESTING BY AKSHAY SHAH") ;
echo "<img src='".$data."'/>";
// to save image below code
$data = QRcode::png ("http:www.webcodegeeks.com " , $img, "L", 10, 10) ;

?>