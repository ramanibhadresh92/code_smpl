<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\NetSuitWmAssetProductMapping;
use DB;
class NetSuitWmAssetMaster extends Model implements Auditable
{
	//
	protected 	$table 		=	'net_suit_wm_asset_master';
	protected 	$primaryKey =	"id"; // or null
	protected 	$guarded 	=	["id"];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts = [

    ];
    public function ListItem(){
    	return $this->hasMany(NetSuitWmAssetProductMapping::class,"asset_id","asset_id");
    }
   	public static function StoreAssetTransactionDataForNetSuit(){
   		$TO 		= date("Y-m-d H:i:s");
   		$FROM 		= date('Y-m-d H:i:s', strtotime(INTERVAL_TIME));
   		$FROM 		= "2021-04-01 00:00:00";
		$SQL 		= 	"SELECT
							WAM.*,
							WD.department_name as from_mrf,
							WD.net_suit_code as from_mrf_ns_id,
							WD1.net_suit_code as to_mrf_ns_id,
							WD1.department_name as to_mrf
						FROM wm_asset_master as WAM
						INNER JOIN wm_department WD on WAM.from_mrf_id = WD.id
						INNER JOIN wm_department WD1 on WAM.to_mrf_id  = WD1.id
						WHERE WAM.approval_status NOT IN(0)
						AND WAM.updated_at BETWEEN '".$FROM."' AND '".$TO."'";
		// echo $SQL;
		// exit;
		$DATA = DB::select($SQL);
		if(!empty($DATA)){
			foreach($DATA AS $KEY => $VALUE){
				$ID = $VALUE->id;
				NetSuitWmAssetMaster::updateOrCreate([
					"asset_id"	=> $ID,
				],[
					"asset_id" 				=> $ID,
					"serial_no" 			=> $VALUE->serial_no,
					"from_mrf_id" 			=> $VALUE->from_mrf_id,
					"from_mrf_ns_id" 		=> $VALUE->from_mrf_ns_id,
					"from_mrf" 				=> $VALUE->from_mrf,
					"to_mrf_id" 			=> $VALUE->to_mrf_id,
					"to_mrf_ns_id" 			=> $VALUE->to_mrf_ns_id,
					"to_mrf" 				=> $VALUE->to_mrf,
					"invoice_date" 			=> $VALUE->invoice_date,
					"delivery_note" 		=> $VALUE->delivery_note,
					"remarks" 				=> $VALUE->remarks,
					"terms_payment" 		=> $VALUE->terms_payment,
					"supplier_ref" 			=> $VALUE->supplier_ref,
					"buyer_no" 				=> $VALUE->buyer_no,
					"dispatch_doc_no" 		=> $VALUE->dispatch_doc_no,
					"dated" 				=> $VALUE->dated,
					"delivery_note_date" 	=> $VALUE->delivery_note_date,
					"dispatch_through" 		=> $VALUE->dispatch_through,
					"destination" 			=> $VALUE->destination,
					"created_at" 			=> date("Y-m-d H:i:s"),
					"updated_at" 			=> date("Y-m-d H:i:s"),
				]);
				############# DELETE PRODUCT ############
				DB::table("net_suit_wm_asset_product_mapping")->where("asset_id",$ID)->delete();

				############# INSERT PRODUCT ############
				$ProductData = WmAssetProductMapping::where("asset_id",$ID)->get()->toArray();

				if(!empty($ProductData)){
					foreach($ProductData as $key => $raw) {
						$array = [
							"asset_id" 		=> $raw["asset_id"],
							"product_id" 	=> "",
							"product" 		=> $raw["product"],
							"description" 	=> $raw["description"],
							"hsn_code" 		=> $raw["hsn_code"],
							"quantity" 		=> $raw["quantity"],
							"rate" 			=> $raw["rate"],
							"gross_amt" 	=> $raw["gross_amt"],
							"sgst" 			=> $raw["sgst"],
							"cgst" 			=> $raw["cgst"],
							"igst" 			=> $raw["igst"],
							"gst_amt" 		=> $raw["gst_amt"],
							"net_amt" 		=> $raw["net_amt"],
							"product_code" 	=> "",
						];
						NetSuitWmAssetProductMapping::insert($array);
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
 	public static function SendAssetTransactionDataToNetSuit($date=""){
		$date 		= (!empty($date)) ? date("Y-m-d H:i:s",strtotime($date)) : "";
		$response 	= array();
		if(!empty($date)){
			$response = self::with("ListItem")->where("updated_at",">=",$date)->get()->toArray();
		}
		return $response;
   	}

}
