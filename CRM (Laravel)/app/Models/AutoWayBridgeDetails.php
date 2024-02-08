<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WaybridgeModuleVehicleInOut;
use App\Models\AdminUserRights;

class AutoWayBridgeDetails extends Model
{
	protected 	$table 		= 'auto_way_bridge_details';
	protected 	$primaryKey = 'id';
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;

	public static function AddNewRecord($request)
	{
		$wayslip_pdf 	 	= (isset($request['wayslip_pdf']) && !empty($request['wayslip_pdf']))?$request['wayslip_pdf']:"";
		$wayslip_photo_1 	= (isset($request['wayslip_photo_1']) && !empty($request['wayslip_photo_1']))?$request['wayslip_photo_1']:"";
		$wayslip_photo_2 	= (isset($request['wayslip_photo_2']) && !empty($request['wayslip_photo_2']))?$request['wayslip_photo_2']:"";
		$wayslip_photo_3 	= (isset($request['wayslip_photo_3']) && !empty($request['wayslip_photo_3']))?$request['wayslip_photo_3']:"";
		$wayslip_photo_4 	= (isset($request['wayslip_photo_4']) && !empty($request['wayslip_photo_4']))?$request['wayslip_photo_4']:"";
		$tare_time 			= (isset($request['tare_time']) && !empty($request['tare_time']))?date("H:i:s",strtotime($request['tare_time'])):"";
		$gross_time 		= (isset($request['gross_time']) && !empty($request['gross_time']))?date("H:i:s",strtotime($request['gross_time'])):"";
		$tare_date 			= (isset($request['tare_date']) && !empty($request['tare_date']))?date("Y-m-d",strtotime($request['tare_date'])):"";
		$gross_date 		= (isset($request['gross_date']) && !empty($request['gross_date']))?date("Y-m-d",strtotime($request['gross_date'])):"";
		$gross_date_time 	= $gross_date." ".$gross_time;
		$tare_date_time 	= $tare_date." ".$tare_time;
		$tran_flag 			= 0;
		if(strtotime($gross_date_time) > strtotime($tare_date_time)) {
			$tran_flag 		= 2;
		} else if(strtotime($gross_date_time) <= strtotime($tare_date_time)) {
			$tran_flag 		= 1;
		}
		$NewRecord 					= new self();
		$NewRecord->row_id 			= (isset($request['row_id']) && !empty($request['row_id']))?$request['row_id']:"";
		$NewRecord->wb_id 			= (isset($request['wb_id']) && !empty($request['wb_id']))?$request['wb_id']:"";
		$NewRecord->ticket_no 		= (isset($request['ticket_no']) && !empty($request['ticket_no']))?$request['ticket_no']:0;
		$NewRecord->vehicle_no 		= (isset($request['vehicle_no']) && !empty($request['vehicle_no']))?$request['vehicle_no']:0;
		$NewRecord->tare_weight 	= (isset($request['tare_weight']) && !empty($request['tare_weight']))?$request['tare_weight']:0;
		$NewRecord->gross_weight 	= (isset($request['gross_weight']) && !empty($request['gross_weight']))?$request['gross_weight']:0;
		$NewRecord->net_weight 		= (isset($request['net_weight']) && !empty($request['net_weight']))?$request['net_weight']:0;
		$NewRecord->gross_date 		= $gross_date;
		$NewRecord->tare_date 		= $tare_date;
		$NewRecord->gross_time 		= $gross_time;
		$NewRecord->tare_time 		= $tare_time;
		$NewRecord->tran_tag 		= $tran_flag;
		$NewRecord->wayslip_pdf 	= (isset($request['wayslip_pdf']) && !empty($request['wayslip_pdf']))?$request['wayslip_pdf']:"";
		$NewRecord->wayslip_photo_1 = (isset($request['wayslip_photo_1']) && !empty($request['wayslip_photo_1']))?$request['wayslip_photo_1']:"";
		$NewRecord->wayslip_photo_2 = (isset($request['wayslip_photo_2']) && !empty($request['wayslip_photo_2']))?$request['wayslip_photo_2']:"";
		$NewRecord->wayslip_photo_3 = (isset($request['wayslip_photo_3']) && !empty($request['wayslip_photo_3']))?$request['wayslip_photo_3']:"";
		$NewRecord->wayslip_photo_4 = (isset($request['wayslip_photo_4']) && !empty($request['wayslip_photo_4']))?$request['wayslip_photo_4']:"";
		$wayslip_pdf1_name 			= "";
		$wayslip_pdf2_name 			= "";
		$wayslip_pdf3_name 			= "";
		$wayslip_pdf4_name 			= "";
		$wayslip_pdf_name 			= "";
		$path 						= public_path(PATH_IMAGE.'/')."auto_way_bridge";
		$partialPath 				= PATH_IMAGE.'/'."auto_way_bridge";
		if(!is_dir($path)) {
        	mkdir($path,0777,true);
        }
        if(!empty($wayslip_pdf)){
        	$ws_pdf 			= base64_decode($wayslip_pdf, true);
        	$wayslip_pdf_name 	= "way_slip_".uniqid().'.pdf';
			$pdf 				= $path."/".$wayslip_pdf_name;
			file_put_contents($pdf, $ws_pdf);
        }
        if(!empty($wayslip_photo_1)){
        	$wayslip_pdf_1 		= base64_decode($wayslip_photo_1, true);
			$wayslip_pdf1_name 	= "way_slip_1_".uniqid().'.pdf';
			$pdf 				= $path."/".$wayslip_pdf1_name;
			file_put_contents($pdf, $wayslip_pdf_1);
        }
        if(!empty($wayslip_photo_2)){
        	$wayslip_pdf_2 		= base64_decode($wayslip_photo_2, true);
			$wayslip_pdf2_name 	= "way_slip_2_".uniqid().'.pdf';
			$pdf 				= $path."/".$wayslip_pdf2_name;
			file_put_contents($pdf, $wayslip_pdf_2);
        }
        if(!empty($wayslip_photo_3)){
        	$wayslip_pdf_3 		= base64_decode($wayslip_photo_3, true);
			$wayslip_pdf3_name 	= "way_slip_3_".uniqid().'.pdf';
			$pdf 				= $path."/".$wayslip_pdf3_name;
			file_put_contents($pdf, $wayslip_pdf_3);
        }
        if(!empty($wayslip_photo_4)){
        	$wayslip_pdf_4 		= base64_decode($wayslip_photo_4, true);
			$wayslip_pdf4_name 	= "way_slip_4_".uniqid().'.pdf';
			$pdf 				= $path."/".$wayslip_pdf4_name;
			file_put_contents($pdf, $wayslip_pdf_4);
        }
        $NewRecord->path 			=  $partialPath;
        $NewRecord->wayslip_pdf 	=  (!empty($wayslip_pdf_name)) 	? $wayslip_pdf_name  : $wayslip_pdf;
        $NewRecord->wayslip_photo_1 =  (!empty($wayslip_pdf1_name)) ? $wayslip_pdf1_name : $wayslip_photo_1;
        $NewRecord->wayslip_photo_2 =  (!empty($wayslip_pdf2_name)) ? $wayslip_pdf2_name : $wayslip_photo_2;
        $NewRecord->wayslip_photo_3 =  (!empty($wayslip_pdf3_name)) ? $wayslip_pdf3_name : $wayslip_photo_3;
        $NewRecord->wayslip_photo_4 =  (!empty($wayslip_pdf4_name)) ? $wayslip_pdf4_name : $wayslip_photo_4;
		if ($NewRecord->save()) {
			WaybridgeModuleVehicleInOut::SaveAutoWayBridgeInformation($NewRecord->id);
		}
	}

	public static function MarkRowAsUsed($row_id=0,$adminuserid=0) {
		$Authorized = AdminUserRights::checkUserAuthorizeForTrn(47013,$adminuserid); //Validate For Mark As Used Transaction Permission
		if (!empty($Authorized)) {
			self::where("id",$row_id)->update(["is_used"=>1]);
			return true;
		} else {
			return false;
		}
	}
}