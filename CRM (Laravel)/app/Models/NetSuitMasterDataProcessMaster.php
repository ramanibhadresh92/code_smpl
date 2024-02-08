<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseLocationMaster;
use App\Models\LocationMaster;
use App\Models\VehicleMaster;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmDispatch;
use App\Models\WmDispatchProduct;
use App\Models\Parameter;
use App\Models\WmClientMaster;
use App\Models\WmProductMaster;
use App\Models\CustomerMaster;
use App\Models\TransporterDetailsMaster;
use App\Models\CompanyProductMaster;
use App\Models\WmDepartment;
use App\Models\NetSuitApiLogMaster;
use DB;
class NetSuitMasterDataProcessMaster extends Model implements Auditable
{
	//
	protected 	$table 		=	'net_suit_master_data_process_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts = [

    ];

    /*
	Use 	:  Store master data Record  which are update or created in table
	Author 	:  Axay Shah
	Date 	:  21 April 2021
	*/
	public static function NetSuitStoreMasterData($table_name="",$record_id=0,$company_id=0){
  		
  		if($table_name != "net_suit_stock_ledger"){
  			self::updateOrCreate(
	            [	
	            	'table_name' => $table_name,
	            	"record_id" => $record_id
	            ],
	            [	
	            	"table_name" 	=> $table_name,
		            "record_id" 	=> $record_id,
		            "company_id"	=> $company_id,
		            "process" 		=> 0
	        	]
        	);
  		}else{
  			$add 				= new self();
  			$add->table_name 	= $table_name;
	  		$add->record_id 	= $record_id;
	  		$add->company_id 	= $company_id;
	  		$add->save();
  		}
  	}

	/*
	Use 	:  Send Master Data to Net Suit
	Author 	:  Axay Shah
	Date 	:  21 April 2021
	*/
	public static function SendMasterDataToNetSuit()
	{
		$datetime 		= date("Y-m-d H:i:s",strtotime('-2 hour'));
		$statusArray 	= array(PROCESS_FAILD,PROCESS_PENDING);
		$data 			= self::whereIn("process",array(PROCESS_PENDING))
							->where("created_at",">=",$datetime)
							// ->whereIn("table_name",['wm_client_master','customer_master','adminuser','wm_product_master','company_product_master'])
							->whereIn("table_name",['wm_client_master','customer_master','adminuser'])
							->groupBy(["table_name","record_id"])
							->get()
							->toArray();
		if(!empty($data))
		{
			$ClientMaster 				= new WmClientMaster();
			$CustomerMaster 			= new CustomerMaster();
			$CompanyProductMaster 		= new CompanyProductMaster();
			$WmProductMaster 			= new WmProductMaster();
			$TransporterDetailsMaster 	= new TransporterDetailsMaster();
			$AdminUser 					= new AdminUser();
			$Stock 						= new NetSuitStockLedger();
			foreach($data as $key => $value){
				$table_name = $value["table_name"];
				$process_id = $value["id"];
				$record_id 	= $value["record_id"];
				$requestArr = array("record_id" => $record_id,"process_id"=>$process_id,"product_type"=>"");
				switch ($table_name) {
					case $ClientMaster->getTable():
						######## AS PER ACCOUNTING LANGUAGE CUSTOMER IS A CLIENT MASTER OF LR SOFTWARE #########
						self::where("id",$process_id)->where("record_id",$record_id)->update(["process"=>1]);
						NetSuitApiLogMaster::SendCustomerData($requestArr);
						break;
					case $CustomerMaster->getTable():
						######## AS PER ACCOUNTING LANGUAGE VENDOR IS A CUSTOMER MASTER OF LR SOFTWARE #########
						self::where("id",$process_id)->where("record_id",$record_id)->update(["process"=>1]);
						NetSuitApiLogMaster::SendVendorData($requestArr);
						break;
					case $CompanyProductMaster->getTable():
						$requestArr["product_type"] = INVENTORY_TYPE_PURCHASE;
						self::where("id",$process_id)->where("record_id",$record_id)->update(["process"=>1]);
						NetSuitApiLogMaster::SendInventoryMasterData($requestArr);
					break;
					case $WmProductMaster->getTable():
						$requestArr["product_type"] = INVENTORY_TYPE_SALES;
						self::where("id",$process_id)->where("record_id",$record_id)->update(["process"=>1]);
						NetSuitApiLogMaster::SendInventoryMasterData($requestArr);
					break;
					case $TransporterDetailsMaster->getTable():
					break;
					case $AdminUser->getTable():
						self::where("id",$process_id)->where("record_id",$record_id)->update(["process"=>1]);
						NetSuitApiLogMaster::DriverDetailList($requestArr);
					break;
					case $Stock->getTable():
					break;
					default:
					break;
				}
			}
		}
	}
}