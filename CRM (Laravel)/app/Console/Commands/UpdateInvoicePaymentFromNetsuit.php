<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\NetSuit;
use App\Models\WmPaymentReceive;
use App\Models\WmPaymentReceiveONSLog;
use Mail;

class UpdateInvoicePaymentFromNetsuit extends Command
{
	/** @var OAuth */
	public $oauth;

	/** @var cURL */
	public $curl;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'UpdateInvoicePaymentFromNetsuit';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Update Invoice Payment From Netsuit';

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
		$NetSuit 					= new NetSuit();
		$NetSuit->CONSUMER_KEY		= "3646b083278380d32942f01a2dc509f8469b13af729dd155871847004317bc02";
		$NetSuit->CONSUMER_SECRET	= "7b22f0dec5cba9d142d8993ff359c456461e16a9d39eec50a926d57977ce1d00";
		$NetSuit->TOKEN_ID			= "b9687065dc72b9697e4056f8ac43ccfe3a3bbe5c10ed49038dcf226a14366425";
		$NetSuit->TOKEN_SECRET		= "9aa53dc5f1916c3771a2b7a65869855444e361e8e48fa22ec894536258adacf2";
		$Date 						= date("Y-m-d",strtotime("yesterday"));
		$LastDate 					= date("Y-m-d");
		while (strtotime($Date) < strtotime($LastDate)) {
			$arrPostFields 				= array("fromDate"=>$Date,"toDate"=>$Date);
			echo "\r\n--Api StartTime::".date("Y-m-d H:i:s")."--\r\n";
			echo "\r\n--Api Payment Date::".$Date."--\r\n";
			$NetSuit->GetDataFromNetSuit($arrPostFields,353,1);
			echo "\r\n--Api EndTime::".date("Y-m-d H:i:s")."--\r\n";
			if (!empty($NetSuit->API_RESPONSE)) {
				$API_RESPONSE = json_decode($NetSuit->API_RESPONSE);
				foreach ($API_RESPONSE as $RESPONSEROW) {
					WmPaymentReceive::AddPaymentReceiveByONS($RESPONSEROW);
				}
			}
			$Date = date("Y-m-d",strtotime($Date . ' +1 day'));
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}