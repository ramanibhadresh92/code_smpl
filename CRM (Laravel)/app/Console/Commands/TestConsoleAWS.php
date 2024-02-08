<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminTransactionGroups;
use App\Models\InvoiceAdditionalCharges;
use App\Models\WmDispatch;
use CURLFile;
use Mail;

class TestConsoleAWS extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'TestConsoleAWS';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Test AWS API';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		echo "\r\n--StartTime::".date("Y-m-d H:i:s")."--\r\n";

		$to = "kalpak.prajapati@yahoo.com";
		$subject = "This is a test";
		$message = "This is a PHP plain text email example.";
		$headers ="From: kalpak.p@letsrecycle.in" ."\r\n" ."Reply-To: kalpak.p@letsrecycle.in" ."\r\n";
		mail($to, $subject, $message, $headers);

		$to = "kalpak.prajapati@gmail.com";
		$subject = "This is a test";
		$message = "This is a PHP plain text email example.";
		$headers ="From: kalpak.p@letsrecycle.in" ."\r\n" ."Reply-To: kalpak.p@letsrecycle.in" ."\r\n";
		mail($to, $subject, $message, $headers);
		die;

		// // $output = shell_exec("wget http://ec2-65-2-73-13.ap-south-1.compute.amazonaws.com:8000/redaction");
		// // echo "<pre>$output</pre>";

		// $ch = curl_init();
		// $curlConfig = array(
		// 	CURLOPT_URL            => "http://ec2-65-2-73-13.ap-south-1.compute.amazonaws.com:8000/redaction",
		// 	CURLOPT_PORT           => 8000,
		// 	CURLOPT_POST           => true,
		// 	CURLOPT_RETURNTRANSFER => true,
		// 	CURLOPT_POSTFIELDS     => array(
		// 		'field1' => 'some date',
		// 		'field2' => 'some other data',
		// 	)
		// );
		// curl_setopt_array($ch, $curlConfig);
		// $result = curl_exec($ch);
		// curl_close($ch);

		// echo "\r\n--Endtime::".date("Y-m-d H:i:s")."--\r\n";
		// die;

		$url 	  	= "http://ec2-65-2-73-13.ap-south-1.compute.amazonaws.com:8000/redaction";
		$filename 	= "6100373.pdf";
		$filename 	= "6100332.pdf";
		$filename 	= "6100326.pdf";
		$target 	= storage_path("epr/".$filename);
		$post_data 	= $this->makeCurlFile($target);
		$headers 	= array("Content-type: multipart/form-data","X-Forwarded-For: 202.66.172.148");
		$curl 		= curl_init();
		curl_setopt($curl, CURLOPT_VERBOSE, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_PORT, '8000');
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$response 	= curl_exec($curl);
		$status 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
		prd($response);
		curl_close($curl);
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}

	private function makeCurlFile($file)
	{
		$mime 		= mime_content_type($file);
		$info 		= pathinfo($file);
		$name 		= $info['basename'];
		$output 	= new CURLFile($file, $mime, $name);
		$return 	= array("uploaded_file" => $output,"file" => $output);
		return $return;
	}
}