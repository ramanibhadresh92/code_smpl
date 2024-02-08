<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\NetSuitWmServiceProductMapping;
use DB;
class NetSuitWmServiceMaster extends Model
{
	protected 	$table 		= 'net_suit_wm_service_master';
	protected 	$primaryKey = "id"; // or null
	protected 	$guarded 	= ["id"];
	public 		$timestamps = true;
	protected $casts 		= [];

    public function ListItem() {
    	return $this->hasMany(NetSuitWmServiceProductMapping::class,"service_id","service_id");
    }

	public static function StoreServiceTransactionDataForNetSuit()
	{
		$INVOICE_DATE 	= "2022-01-01";
   		$TO 			= date("Y-m-d H:i:s");
   		$FROM 			= date('Y-m-d H:i:s', strtotime(INTERVAL_TIME));
   		$FROM 			= "2022-01-01 00:00:00";
   		$TO 			= date("Y-m-d")." 23:59:59";
		$SQL 			= "	SELECT WSM.*, WD.department_name, WD.net_suit_code as mrf_ns_id, WCM.client_name, WCM.net_suit_code as client_code
							FROM wm_service_master as WSM
							LEFT JOIN wm_department WD on WSM.mrf_id = WD.id
							LEFT JOIN wm_client_master WCM on WSM.client_id = WCM.id
							WHERE WSM.approval_status = 1
							AND WSM.invoice_date >= '".$INVOICE_DATE."'
							AND WSM.updated_at BETWEEN '".$FROM."' AND '".$TO."'";
		$DATA 			= DB::select($SQL);
		if(!empty($DATA))
		{
			foreach($DATA AS $KEY => $VALUE)
			{
				$ID = $VALUE->id;
				NetSuitWmServiceMaster::updateOrCreate(["service_id"	=> $ID],[	"service_id" 			=> $ID,
																					"serial_no" 			=> $VALUE->serial_no,
																					"service_type" 			=> $VALUE->service_type,
																					"company_id" 			=> $VALUE->company_id,
																					"mrf_id" 				=> $VALUE->mrf_id,
																					"mrf_ns_id" 			=> $VALUE->mrf_ns_id,
																					"mrf" 					=> $VALUE->department_name,
																					"invoice_date" 			=> $VALUE->invoice_date,
																					"client_id" 			=> $VALUE->client_id,
																					"client_name" 			=> $VALUE->client_name,
																					"client_ns_code" 		=> $VALUE->client_code,
																					"delivery_note" 		=> $VALUE->delivery_note,
																					"remarks" 				=> $VALUE->remarks,
																					"terms_payment" 		=> $VALUE->terms_payment,
																					"supplier_ref" 			=> $VALUE->supplier_ref,
																					"buyer_no" 				=> $VALUE->buyer_no,
																					"dispatch_doc_no" 		=> $VALUE->dispatch_doc_no,
																					"dated" 				=> $VALUE->dated,
																					"delivery_note_date" 	=> $VALUE->delivery_note_date,
																					"dispatch_through" 		=> $VALUE->dispatch_through,
																					"irn" 					=> $VALUE->irn,
																					"ack_no" 				=> $VALUE->ack_no,
																					"ack_date" 				=> $VALUE->ack_date,
																					"signed_qr_code" 		=> $VALUE->signed_qr_code,
																					"approval_status" 		=> $VALUE->approval_status,
																					"action_remark" 		=> $VALUE->action_remark,
																					"destination" 			=> $VALUE->destination,
																					"created_at" 			=> date("Y-m-d H:i:s"),
																					"updated_at" 			=> date("Y-m-d H:i:s")]);
				############# DELETE PRODUCT ############
				DB::table("net_suit_wm_service_product_mapping")->where("service_id",$ID)->delete();
				############# INSERT PRODUCT ############
				$ProductData 	= WmServiceProductMapping::select(	"WSPM.*","WSPM.product as product_name","wm_service_product_mapping.*",
																"PARA.para_value","P1.para_value as product_class","P2.para_value as product_department")
									->leftjoin("wm_service_product_master as WSPM","wm_service_product_mapping.product_id","=","WSPM.id")
									->leftjoin("parameter as PARA","PARA.para_id","=","wm_service_product_mapping.uom")
									->leftjoin("parameter as P1","WSPM.net_suit_class","=","P1.para_id")
									->leftjoin("parameter as P2","WSPM.net_suit_department","=","P2.para_id")
									->where("service_id",$ID)
									->get()
									->toArray();
				if(!empty($ProductData))
				{
					foreach($ProductData as $key => $raw)
					{
						$array 	= [	"service_id" 			=> $raw["service_id"],
									"product_id" 			=> $raw["product_id"],
									"product" 				=> $raw["product_name"],
									"description" 			=> $raw["description"],
									"hsn_code" 				=> $raw["hsn_code"],
									"quantity" 				=> $raw["quantity"],
									"rate" 					=> $raw["rate"],
									"gross_amt" 			=> $raw["gross_amt"],
									"uom" 					=> $raw["para_value"],
									"product_class" 		=> $raw["product_class"],
									"product_department" 	=> $raw["product_department"],
									"uom_id" 				=> $raw["uom"],
									"sgst" 					=> $raw["sgst"],
									"cgst" 					=> $raw["cgst"],
									"igst" 					=> $raw["igst"],
									"gst_amt" 				=> $raw["gst_amt"],
									"net_amt" 				=> $raw["net_amt"],
									"product_ns_code" 		=> $raw["service_net_suit_code"]];
						NetSuitWmServiceProductMapping::insert($array);
					}
				}
			}
		}
	}

   	/*
	Use 	: Send Service Data to Net Suit
	Author 	: Axay Shah
	Date 	: 16 April 2021
    */
 	public static function SendServiceTransactionDataToNetSuit($date="")
 	{
		$date 		= (!empty($date)) ? date("Y-m-d H:i:s",strtotime($date)) : "";
		$response 	= array();
		if(!empty($date)) {
			$response = self::with("ListItem")->where("updated_at",">=",$date)->get()->toArray();
		}
		return $response;
   	}
}