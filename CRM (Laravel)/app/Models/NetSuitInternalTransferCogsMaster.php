<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\NetSuitInternalTransferCogsTransaction;
use Mail;

class NetSuitInternalTransferCogsMaster extends Model
{
	protected $table 		= 'netsuit_internal_transfer_cogs_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	/**
	* Function Name : GetCogsData
	* @param object $Request
	* @return arry $arrResult
	* @author Axay Shah
	* @since 2022-02-10
	* @access public
	* @uses method used to send Cogs Data to internal stock transfer Netsuit
	*/
	public static function GetInternalTransferCogsData($Request)
	{
		$arrResult 	= array();
		$counter 	= 0;
		$cogs_date 	= date("Y-m-d",strtotime("yesterday"));
		if (isset($Request->date) && !empty($Request->date)) {
			$cogs_date 	= date("Y-m-d",strtotime($Request->date));
		}
		// $cogs_date  = "2022-01-01";
		$SelectSql 	= self::select("id","cogs_date")->where("cogs_date",">=",$cogs_date)->first();
		if (!empty($SelectSql)) {
			$Lines 	= NetSuitInternalTransferCogsTransaction::select("mrf_id","mrf_ns_id","credit_amount","debit_amount","amount","is_credit")->where("ref_id",$SelectSql->id)->get();
			if (!empty($Lines)) {
				$arrResult[$counter]['lr_no'] 			= $SelectSql->id;
				$arrResult[$counter]['journal_no'] 		= "";
				$arrResult[$counter]['txn_date'] 		= $SelectSql->cogs_date;
				$arrResult[$counter]['currency'] 		= "INR";
				$arrResult[$counter]['exchange_rate'] 	= "1";
				foreach($Lines as $Line) {
					$is_credit = ($Line->is_credit == 1) ? $Line->is_credit : 0;
					// $debit_amount = (!empty($Line->debit_amount)) ? _FormatNumberV2($Line->debit_amount) : 0;
					// $credit_amount = (!empty($Line->credit_amount)) ? _FormatNumberV2($Line->credit_amount) : 0;
					// if($debit_amount > 0){
					// 	$arrResult[$counter]['lines'][] = array("account"=>411000,"debit_amount"=>$debit_amount,"credit_amount"=>0,"location_id"=>$Line->mrf_ns_id,"class"=>"Sales of goods","dept"=>"MRF Operations");
					// }
					// if($credit_amount > 0){
					// 	$arrResult[$counter]['lines'][] = array("account"=>231100,"debit_amount"=>0,"credit_amount"=>$credit_amount,"location_id"=>$Line->mrf_ns_id,"class"=>"Sales of goods","dept"=>"collection");
					// }
					
					$amount = (!empty($Line->amount)) ? _FormatNumberV2($Line->amount) : 0;
					if($is_credit == 0){
						$arrResult[$counter]['lines'][] = array("account"=>411000,"debit_amount"=>$amount,"credit_amount"=>0,"location_id"=>$Line->mrf_ns_id,"class"=>"Sales of goods","dept"=>"MRF Operations");
					}
					if($is_credit == 1){
						$arrResult[$counter]['lines'][] = array("account"=>231100,"debit_amount"=>0,"credit_amount"=>$amount,"location_id"=>$Line->mrf_ns_id,"class"=>"Sales of goods","dept"=>"collection");
					}
					
				}
			}
		}
		return $arrResult;
	}
}