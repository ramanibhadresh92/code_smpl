<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\NetSuitStockAddtionTransaction;
use Mail;

class NetSuitStockAdditionMaster extends Model
{
	protected $table 		= 'netsuit_stock_addition_master';

	/**
	* Function Name : GetStockAdditionData
	* @param object $Request
	* @return arry $arrResult
	* @author Kalpak Prajapati
	* @since 2022-01-25
	* @access public
	* @uses method used to send Stock Addition Data to Netsuit
	*/
	public static function GetStockAdditionData($Request)
	{
		$arrResult 		= array();
		$counter 		= 0;
		$WmDepartment 	= new WmDepartment();
		$stock_date 	= date("Y-m-d",strtotime("yesterday"));
		if (isset($Request->date) && !empty($Request->date)) {
			$stock_date = date("Y-m-d",strtotime($Request->date));
		}
		$SelectSql 	= self::select("id","stock_date")->where("stock_date",$stock_date)->first();
		if (!empty($SelectSql)) {
			$Lines 	= NetSuitStockAddtionTransaction::select("DEPT.net_suit_code as mrf_id","amount")
			->join($WmDepartment->getTable()." as DEPT","netsuit_cogs_master.mrf_id","=","DEPT.id")
			->where("ref_id",$SelectSql->id)
			->get();
			if (!empty($Lines)) {
				$arrResult[$counter]['lr_no'] 			= $SelectSql->id;
				$arrResult[$counter]['journal_no'] 		= "";
				$arrResult[$counter]['txn_date'] 		= $SelectSql->stock_date;
				$arrResult[$counter]['currency'] 		= "INR";
				$arrResult[$counter]['exchange_rate'] 	= "1";
				foreach($Lines as $Line) {
					if ($Line->mrf_id >= 25) continue;
					$arrResult[$counter]['lines'][] = array("account"=>231200,"debit_amount"=>$Line->amount,"credit_amount"=>0,"location_id"=>$Line->mrf_id,"class"=>"Purchase","dept"=>"Collection");
					$arrResult[$counter]['lines'][] = array("account"=>410001,"debit_amount"=>0,"credit_amount"=>$Line->amount,"location_id"=>$Line->mrf_id,"class"=>"Sales of goods","dept"=>"MRF Operations");
				}
			}
		}
		return $arrResult;
	}
}
