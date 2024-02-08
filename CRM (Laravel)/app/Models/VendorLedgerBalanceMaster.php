<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\VendorLedgerBalanceMaster;
use App\Models\WmDepartment;
use App\Models\CustomerMaster;
use App\Facades\LiveServices;
use Validator;
use DB;
use JWTAuth;
use Log;
class VendorLedgerBalanceMaster extends Model
{
	protected   $table          = 'vendor_ledger_balance_master';
	public      $timestamps     = false;
	protected   $primaryKey     = 'id'; 
	protected   $guarded        = ['id']; 

	/*
	Use     :  Get LR Vendor Ledger Data from Bams
	Author  :  Hardyesh Gupta
	Date    :  18 Sep 2023
	*/
	public static function GetVendorLedgerBalanceData($request){
		$keyword    = (isset($request->keyword) && !empty($request->keyword))?$request->keyword : "";
		$to_mrf_ns_id   = (isset($request->to_mrf_ns_id) && !empty($request->to_mrf_ns_id))?$request->to_mrf_ns_id : "";
		$createdAt  = (isset($request->created_from) && !empty($request->created_from))?$request->created_from." ".GLOBAL_START_TIME : "";
		$createdTo  = (isset($request->created_to) && !empty($request->created_to))?$request->created_to." ".GLOBAL_END_TIME : "";
		$StartTime  = (isset($request->starttime) && !empty($request->starttime))? date("Y-m-d",strtotime($request->starttime))." ".GLOBAL_START_TIME : '';
		$EndTime    = (isset($request->endtime) && !empty($request->endtime))? date("Y-m-d",strtotime($request->endtime))." ".GLOBAL_END_TIME  : '';
		$res        = "";
		$last_bams_id       = VendorLedgerBalanceMaster::max('bams_id');
		$start_from_id      = (!empty($last_bams_id)) ? $last_bams_id : 0;
		$createdby_userid   = (\Auth::check()) ? Auth()->user()->adminuserid :  0;
		$ch         = curl_init();
		$apiURL     = PROJECT_BAMS_LR_VENDOR_DATA_URL;
		$dataArray  = ['keyword' => $keyword,"from_lr"=>1,"start_from_id"=>$start_from_id];
		$dataArrayJson = json_encode($dataArray);
		$ch         = curl_init();
		$curl       = curl_init($apiURL);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataArray));
		$responseAPI        = curl_exec($curl);
		$response           = $responseAPI;
		$ResultData         = json_decode($response);
		$response_status_code = $ResultData->code;
		$res                = $ResultData->data; 
		$InsertID   = DB::table('vendor_ledger_balance_api_log')->insertGetId(['input_parameter' => $dataArrayJson,'response_status_code' => $response_status_code,'response_parameter'=>$responseAPI,'created_by' => $createdby_userid,'created_at'=>date('Y-m-d H:i:s')]);
		if(!empty($res)){
			$res        = json_decode(json_encode($res),true); 
			foreach($res as $key => $value){
				$bill_date      = $value['bill_date'];
				$bill_type      = $value['bill_type'];
				$bill_no        = $value['bill_no'];
				$balance_amount = $value['balance_amount'];
				$mrf_ns_id      = $value['mrf_ns_id'];
				$mrf_name       = $value['mrf_name'];
				$mrfID          = WmDepartment::where("net_suit_code",$mrf_ns_id)->value('id');
				$mrf_id         = (!empty($mrfID)) ? $mrfID : 0;
				$vendor_id      = $value['vendor_id'];
				$bams_id        = $value['id'];
				VendorLedgerBalanceMaster::insert(
				 array(
					 "bill_date"         => $bill_date,
					 "bill_type"         => $bill_type,
					 "bill_no"           => $bill_no,
					 "balance_amount"    => $balance_amount,
					 "mrf_ns_id"         => $mrf_ns_id,
					 "balance_amount"    => $balance_amount,
					 "mrf_name"          => $mrf_name,
					 "mrf_id"            => $mrf_id,
					 "vendor_id"         => $vendor_id,
					 "bams_id"           => $bams_id,
					 "created_at"        => date("Y-m-d H:i:s"),
					 "updated_at"        => date("Y-m-d H:i:s")
				 ));
			}       
		}
		return $res;
	}

	/*
	Use     :  LR VENDOR BALANCE REPORT
	Author  :  Hardyesh Gupta
	Date    :  20 Sep 2023
	*/

	public static function VendorLedgerBalanceReport($request,$Paginate=false){
		$table      = (new static)->getTable();
		$Customer   = new CustomerMaster();
		$Department = new WmDepartment();
		$keyword    = (isset($request->keyword) && !empty($request->keyword))?$request->keyword : "";
		$mrf_ns_id  = (isset($request->mrf_ns_id) && !empty($request->mrf_ns_id))?$request->mrf_ns_id : 0;
		$mrf_id     = (isset($request->mrf_id) && !empty($request->mrf_id))?$request->mrf_id : 0;
		$bill_no    = (isset($request->bill_no) && !empty($request->bill_no))?trim($request->bill_no) : "";
		$vendor_code = (isset($request->vendor_code) && !empty($request->vendor_code))?$request->vendor_code : "";
		$createdAt  = (isset($request->created_from) && !empty($request->created_from))?$request->created_from." ".GLOBAL_START_TIME : "";
		$createdTo  = (isset($request->created_to) && !empty($request->created_to))?$request->created_to." ".GLOBAL_END_TIME : "";
		// $StartTime  = (isset($request->starttime) && !empty($request->starttime))? date("Y-m-d",strtotime($request->starttime))." ".GLOBAL_START_TIME : '';
		// $EndTime   = (isset($request->endtime) && !empty($request->endtime))? date("Y-m-d",strtotime($request->endtime))." ".GLOBAL_END_TIME  : '';
		$StartTime  = $createdAt;
		$EndTime  	= $createdTo;
		
		$ReportSql = self::select(
						"$table.id as id",
						"$table.bill_date as bill_date",
						"$table.bill_type as bill_type",
						"$table.vendor_code as vendor_code",
						"$table.bill_no as bill_no",
						"$table.balance_amount as balance_amount",
						"$table.mrf_ns_id as mrf_ns_id",
						"$table.mrf_name as mrf_name",
						"$table.mrf_id as mrf_id",
						"$table.vendor_id as vendor_id",
						"$table.bams_id as bams_id",
						"$table.created_at as created_from",
						\DB::raw("CONCAT(CM.first_name,' ',CM.last_name) AS vendor_name"),
						\DB::raw("DEPT.department_name")
					)
					->leftjoin($Customer->getTable()." as CM","$table.vendor_code","=","CM.net_suit_code")
					->leftjoin($Department->getTable()." as DEPT","$table.mrf_id","=","DEPT.id");
		if(!empty($mrf_id)){
			$ReportSql->where("$table.mrf_id",$mrf_id);
		}
		if(!empty($bill_no)){
			$ReportSql->where("$table.bill_no",$bill_no);
		}
		if(!empty($vendor_code)){
			$ReportSql->where("$table.vendor_code",$vendor_code);
		}
		if(!empty($vendor_name)){
			$ReportSql->where("CM.first_name","like",$vendor_name."%");
		}
		if(!empty($createdAt) && !empty($createdTo)){
			$ReportSql->whereBetween("$table.created_at",[$createdAt,$createdTo]);
		}elseif(!empty($createdAt)){
			$ReportSql->whereBetween("$table.created_at",[$createdAt,$createdAt]);
		}elseif(!empty($createdTo)){
			$ReportSql->whereBetween("$table.created_at",[$createdTo,$createdTo]);
		}
		$ReportSql->orderBy("$table.id","ASC");

		// $qry =  Liveservices::toSqlWithBinding($ReportSql,true);
		// prd($qry);
		$Result              = $ReportSql->get()->toArray();
		$GrossVendorBalance  = 0;
		$array                  = array();
		$res                    = array();
		if (count($Result) > 0)
		{
			foreach ($Result as $Collection)
			{
				if(is_object($Collection)){
					$Collection = json_decode(json_encode($Collection),true);   
				}
				$GrossVendorBalance     += _FormatNumberV2($Collection['balance_amount']);
			}
			$array['GROSS_VENDOR_BAL_AMT']      = _FormatNumberV2(round($GrossVendorBalance));
			$res["total_data"]                  = $array;
			$res["res"]                         = $Result;
		}
		return $res;
	}
}
