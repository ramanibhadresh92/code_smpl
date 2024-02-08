<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseLocationMaster;
use App\Models\LocationMaster;
use App\Models\UserBaseLocationMapping;
use App\Models\WmDepartmentTitleMaster;
use App\Models\GstStateData;
use App\Models\MasterCodes;
use App\Models\AdminUserRights;
use App\Models\WmSaleableProductTagging;
use App\Models\TransporterDetailsMaster;
use App\Models\WmDispatch;
use App\Facades\LiveServices;
use DB;
class TransporterDispatchInvoiceProcessMaster extends Model
{
	//
	protected 	$table 		=	'transporter_dispatch_invoice_process_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	false;
	protected $casts = [
      
    ];
	
	public static function AddTransporterPOProcessRecord($transporter_detail_id,$dispatch_id){
		$IS_IN_BAMS = TransporterDetailsMaster::where("id",$transporter_detail_id)->where("po_detail_id",">",0)->first();
		if($IS_IN_BAMS){
			$self = new self;
			$self->transporter_detail_id 	= $transporter_detail_id;
			$self->dispatch_id 				= $dispatch_id;
			$self->company_id 				= Auth()->user()->company_id;
			$self->created_at 				= date("Y-m-d  H:i:s");
			$self->updated_at 				= date("Y-m-d  H:i:s");
			$self->save();
		}
	}

	public static function SendInvoiceGenerationDataToBams(){
		$data = self::where("process",0)->get()->toArray();
		
		if(!empty($data)){
			foreach($data as $raw){
				$PARA_TRANSPORTER_MEDIA = WmDispatchMediaMaster::select("dispatch_id","image_name","image_path","id")->where("dispatch_id",$raw['dispatch_id'])->where("media_type",PARA_TRANSPORTER_INV)->orderBy("id","DESC")->first();
				if($PARA_TRANSPORTER_MEDIA){
					self::where("id",$raw['id'])->update(["process"=>1]);
					$DispatchData 		= WmDispatch::find($raw['dispatch_id']);
					$Details 			= TransporterDetailsMaster::where("id",$raw['transporter_detail_id'])->first();
					if($Details){
						$DISPATCH_ID 	= $raw['dispatch_id'];
						$CHALLAN_NO 	= $DispatchData->challan_no;
						$PO_DETAILS_ID 	= $Details->po_detail_id;
						$RATE 			= $Details->rate;
						$PO_DETAILS 	= TransporterPoDetailsMaster::find($PO_DETAILS_ID);
						$ADV_PER 		= ($PO_DETAILS) ? $PO_DETAILS->advance_in_percentage : 0;
						$PO_ID 			= ($PO_DETAILS) ? $PO_DETAILS->po_id : 0;
						
						$VEHICLE_NO 			= VehicleMaster::where("vehicle_id",$Details->vehicle_id)->value("vehicle_number");
						$TRANSPORTERMEDIA 		= array();
						$PARA_TRANSPORTER_MEDIA = WmDispatchMediaMaster::select("dispatch_id","image_name","image_path","id")->where("dispatch_id",$DISPATCH_ID)->where("media_type",PARA_TRANSPORTER_INV)->orderBy("id","DESC")->first();
						if(!empty($PARA_TRANSPORTER_MEDIA)) {
							$TRANSPORTERMEDIA['mime_type'] =  mime_content_type(public_path("/".$PARA_TRANSPORTER_MEDIA->image_path."/".$PARA_TRANSPORTER_MEDIA->image_name));
							$TRANSPORTERMEDIA['url'] =  url('/')."/".$PARA_TRANSPORTER_MEDIA->image_path."/".$PARA_TRANSPORTER_MEDIA->image_name;
						}
						$EWAYBILLMEDIA 		= array();
						$EWAYBILL_MEDIA 	= WmDispatchMediaMaster::select("dispatch_id","image_name","image_path","id")->where("dispatch_id",$DISPATCH_ID)->where("media_type",PARA_EWAY_BILL)->orderBy("id","DESC")->first();

						if(!empty($EWAYBILL_MEDIA)) {
							$EWAYBILLMEDIA['mime_type'] =  mime_content_type(public_path("/".$EWAYBILL_MEDIA->image_path."/".$EWAYBILL_MEDIA->image_name));
							$EWAYBILLMEDIA['url'] =  url('/')."/".$EWAYBILL_MEDIA->image_path."/".$EWAYBILL_MEDIA->image_name;
						}
						$WAYBRIDGEMEDIA 	= array();
						$WAYBRIDGE_MEDIA 	= WmDispatchMediaMaster::select("dispatch_id","image_name","image_path","id")->where("dispatch_id",$DISPATCH_ID)->where("media_type",PARA_WAYBRIDGE)->orderBy("id","DESC")->first();
						if(!empty($WAYBRIDGE_MEDIA)) {
							$WAYBRIDGEMEDIA['mime_type'] =  mime_content_type(public_path("/".$WAYBRIDGE_MEDIA->image_path."/".$WAYBRIDGE_MEDIA->image_name));
							$WAYBRIDGEMEDIA['url'] 	=  url('/')."/".$WAYBRIDGE_MEDIA->image_path."/".$WAYBRIDGE_MEDIA->image_name;
						}
						$BILLTMEDIA 	= array();
						$BILLT_MEDIA 	= WmDispatchMediaMaster::select("dispatch_id","image_name","image_path","id")->where("dispatch_id",$DISPATCH_ID)->where("media_type",PARA_BILLT)->orderBy("id","DESC")->first();
						if(!empty($BILLT_MEDIA)) {
							$BILLTMEDIA['mime_type'] 	=  mime_content_type(public_path("/".$BILLT_MEDIA->image_path."/".$BILLT_MEDIA->image_name));
							$BILLTMEDIA['url'] 			=  url('/')."/".$BILLT_MEDIA->image_path."/".$BILLT_MEDIA->image_name;
						}
							$QTY 	= 1;
							$UOM 	= BAMS_TRANSPORTE_UOM_ID;
							if($PO_DETAILS && isset($PO_DETAILS->vehicle_cost_type)){
								if($PO_DETAILS->vehicle_cost_type == PER_TONNE_ACTUAL_CAPACITY){
									$RATE 	= $PO_DETAILS->rate_per_kg;
									$QTY 	= ($DispatchData) ? $DispatchData->quantity : 0;
									$UOM 	= BAMS_TRANSPORTE_UOM_KG_ID;
								}elseif($PO_DETAILS->vehicle_cost_type == PER_TONNE_ACTUAL_CAPACITY){
									$RATE 	= $PO_DETAILS->rate_per_kg;
									$QTY 	= $PO_DETAILS->vehicle_capacity_in_kg;
									$UOM 	= BAMS_TRANSPORTE_UOM_KG_ID;
								}
							}
						
						$ARRAY 	= array(
							"adv_type" 				=> BAMS_ADVANCE_PERCENTAGE_PARAMETER,
							"adv_amt" 				=> $ADV_PER,
							"vehicle_no" 			=> $VEHICLE_NO,
							"lr_invoice_no" 		=> ($DispatchData) ? $CHALLAN_NO : "",
							"external_invoice_id" 	=> $raw['dispatch_id'],
							"qty" 					=> $QTY,
							"uom" 					=> $UOM,
							"rate" 					=> $RATE,
							'invoice_description' 	=> $VEHICLE_NO."- LR INVOICE NO-".$CHALLAN_NO,
							"po_id" 				=> $PO_ID,
							'from_lr' 				=> 1,
							"lr_invoice_url" 		=> $TRANSPORTERMEDIA, 
							"eway_bill" 			=> $EWAYBILLMEDIA, 
							"built_in" 				=> $BILLTMEDIA, 
							"webridge" 				=> $WAYBRIDGEMEDIA, 
						);
						$USER_ID 		= (isset($PO_DETAILS->created_by) && !empty($PO_DETAILS->created_by)) ? $PO_DETAILS->created_by : 0;
						$ORANGE_CODE 	= AdminUser::where("adminuserid",$USER_ID)->value("orange_code");
						$token 			= TransporterPoDetailsMaster::LoginInBAMS($ARRAY,$ORANGE_CODE,$USER_ID);
						
						if(empty($token)){
							self::where("id",$raw['id'])->update(["process"=>3]);
						}
						if(!empty($token)){
							$authorization 	= "Authorization: Bearer $token";
							$apiURL 		= "https://bams.nepra.co.in/api/company/invoice/transporter/generate";
						    $authorization = "Authorization: Bearer $token";
							$curl = curl_init($apiURL);
							curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
					    	curl_setopt($curl, CURLOPT_POST, true);
					        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($ARRAY));
					        // curl_setopt($curl, CURLOPT_POSTFIELDS, $postRequest);
						    $response 	= curl_exec($curl);
					        $res 		= json_decode($response);
					        TransporterPoDetailsMaster::AddRequestLog($ORANGE_CODE,json_encode($ARRAY),$response,$USER_ID,$apiURL);
						    if(isset($res->code)){
								$code = $res->code;
								if($code == SUCCESS){
									self::where("id",$raw['id'])->update(["process"=>2]);
								}else{
									self::where("id",$raw['id'])->update(["process"=>3]);
								}
				       		}
						}
					}
				}
			}
		}
	}
}