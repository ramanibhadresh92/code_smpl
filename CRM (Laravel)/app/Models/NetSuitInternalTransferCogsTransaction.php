<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\NetSuitInternalTransferCogsTransaction;
use Mail;

class NetSuitInternalTransferCogsTransaction extends Model
{
	protected $table 		= 'netsuit_internal_transfer_cogs_transaction';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	/**
	* Function Name : GetCogsData
	* @param object $Request
	* @return arry $arrResult
	* @author Kalpak Prajapati
	* @since 2022-01-25
	* @access public
	* @uses method used to send Cogs Data to Netsuit
	*/
	public static function GetCogsData($Request)
	{
		$arrResult 	= array();
		$counter 	= 0;
		$cogs_date 	= date("Y-m-d",strtotime("yesterday"));
		if (isset($Request->date) && !empty($Request->date)) {
			$cogs_date 	= date("Y-m-d",strtotime($Request->date));
		}
		$SelectSql 	= self::select("id","cogs_date")->where("cogs_date",$cogs_date)->first();
		if (!empty($SelectSql)) {
			$Lines 	= NetSuitCogsTransaction::select("mrf_id","amount")->where("ref_id",$SelectSql->id)->get();
			if (!empty($Lines)) {
				$arrResult[$counter]['lr_no'] 			= $SelectSql->id;
				$arrResult[$counter]['journal_no'] 		= "";
				$arrResult[$counter]['txn_date'] 		= $SelectSql->cogs_date;
				$arrResult[$counter]['currency'] 		= "INR";
				$arrResult[$counter]['exchange_rate'] 	= "1";
				foreach($Lines as $Line) {
					$arrResult[$counter]['lines'][] = array("account"=>411000,"debit_amount"=>$Line->amount,"credit_amount"=>0,"location_id"=>$Line->mrf_id,"class"=>"Purchase","dept"=>"Logistics and Collection");
					$arrResult[$counter]['lines'][] = array("account"=>231100,"debit_amount"=>0,"credit_amount"=>$Line->amount,"location_id"=>$Line->mrf_id,"class"=>"Sales of goods","dept"=>"Sales");
					// $arrResult['lines'][] = array("account"=>231100,"debit_amount"=>$Line->amount,"credit_amount"=>0,"location_id"=>$Line->mrf_id,"class"=>"Purchase","dept"=>"Logistics and Collection");
					// $arrResult['lines'][] = array("account"=>231200,"debit_amount"=>0,"credit_amount"=>$Line->amount,"location_id"=>$Line->mrf_id,"class"=>"Sales of goods","dept"=>"Sales");
				}
			}
		}
		return $arrResult;
	}
}