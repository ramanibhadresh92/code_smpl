<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmServiceProductMapping extends Model
{
	protected $table 	= 'wm_service_product_mapping';
	public $timestamps 	= false;

	/*
	Use 	: GetServiceDetails
	Author 	: Upasana
	Date 	: 04 March 2021
	*/

	public static function SaveServiceProduct($request,$service_id=0,$igst_flag=0)
	{
		$id 			= 0;
		$product_list = "";
		if(isset($request->product_list) && !empty($request->product_list)){
			if(is_array($request->product_list)){
				$product_list = $request->product_list;	
			}else{
				$product_list = $request->product_list;
				if(is_object($request->product_list)){
					$product_list = json_encode($request->product_list);
				}
				$product_list = json_decode($product_list,true);								
			}
		}		
		$serviceData =  WmServiceMaster::find($service_id);
		if($serviceData)
		{
			if(!empty($product_list) && is_array($product_list))
			{
				$arrRows 	= self::where("service_id",$service_id)->get()->toArray();
		 		$delete 	= self::where("service_id",$serviceData->id)->delete();
		 		foreach ($product_list as $key => $value)
		 		{
					$productId 						= (isset($value["product_id"]) && !empty($value["product_id"])) ? $value["product_id"] : 0;
					$PRODUCT_DATA 					= WmServiceProductMaster::find($value['product_id']);
	 				$product 						= (isset($value["product"]) && !empty($value["product"])) ? $value["product"] : "";
	 				$description 					= (isset($value["description"]) && !empty($value["description"])) ? $value["description"] : "";
					$hsn_code 						= (isset($value["hsn_code"]) && !empty($value["hsn_code"])) ? $value["hsn_code"] : "";
					$quantity 						= (isset($value["quantity"]) && !empty($value["quantity"])) ? $value["quantity"] : 0;
					$rate 							= (isset($value["rate"]) && !empty($value["rate"])) ? $value["rate"] : 0;
					$uom 							= (isset($value["uom"]) && !empty($value["uom"])) ? $value["uom"] : "";
					$gross_amt 						= (isset($value["gross_amt"]) && !empty($value["gross_amt"])) ? $value["gross_amt"] : 0;
					$sgst 							= (isset($value["sgst"]) && !empty($value["sgst"])) ? $value["sgst"] : 0;
					$igst 							= (isset($value["igst"]) && !empty($value["igst"])) ? $value["igst"] : 0;
					$cgst 							= (isset($value["cgst"]) && !empty($value["cgst"])) ? $value["cgst"] : 0;
					$gst_amt						= (isset($value["gst_amt"]) && !empty($value["gst_amt"])) ? $value["gst_amt"] : 0;
					$net_amt 						= (isset($value["net_amt"]) && !empty($value["net_amt"])) ? $value["net_amt"] : 0;
					$epr_batch_id 					= 0;
					$epr_batch_invoice_process_id 	= 0;
					$igst 							= 0;
					$sgst 							= 0;
					$cgst 							= 0;
					if (isset($arrRows[$key]['epr_batch_id'])) {
						$epr_batch_id = $arrRows[$key]['epr_batch_id'];
					}
					if (isset($arrRows[$key]['epr_batch_invoice_process_id'])) {
						$epr_batch_invoice_process_id = $arrRows[$key]['epr_batch_invoice_process_id'];
					}
					$ProductRow 	= WmServiceProductMaster::where('id',$productId)->where('status',PARA_STATUS_ACTIVE)->first();
					$productId 		= $productId;
					$product 		= $ProductRow->product;
					$description 	= $description;
					$hsn_code 		= $ProductRow->hsn_code;
					$quantity 		= $quantity;
					$rate 			= $rate;
					$uom 			= $uom;
					$gross_amt 		= round(($quantity * $rate),2);
					if ($igst_flag == 0) {
						$sgst 		= $ProductRow->sgst;
						$cgst 		= $ProductRow->cgst;
						$igst 		= 0;
						$sgst_amt 	= ($gross_amt > 0)?round((($gross_amt * $sgst)/100),2):0;
						$cgst_amt 	= ($gross_amt > 0)?round((($gross_amt * $cgst)/100),2):0;
						$gst_amt 	= $sgst_amt + $cgst_amt;
						$net_amt 	= $gross_amt + $gst_amt;
					} else {
						$sgst 		= 0;
						$cgst 		= 0;
						$igst 		= $ProductRow->igst;
						$igst_amt 	= ($gross_amt > 0)?round((($gross_amt * $igst)/100),2):0;
						$gst_amt 	= $igst_amt;
						$net_amt 	= $gross_amt + $gst_amt;
					}
					$data 								= new WmServiceProductMapping();
					$data->service_id 					= $service_id;
					$data->product_id 					= $productId;
					$data->product 						= $product;
					$data->description 					= $description;
					$data->hsn_code 					= $hsn_code;
					$data->uom 							= $uom;
					$data->quantity 					= $quantity;
					$data->rate 						= $rate;
					$sameState 							= (empty($igst)) ? true : false;
					$data->gross_amt 					= $gross_amt;
					$data->sgst 						= $sgst;
					$data->cgst 						= $cgst;
					$data->igst 						= $igst;
					$data->gst_amt 						= $gst_amt;
					$data->net_amt 						= ($gst_amt == 0) ? $gross_amt : $net_amt;
					$data->epr_batch_id 				= $epr_batch_id;
					$data->epr_batch_invoice_process_id = $epr_batch_invoice_process_id;
					if($data->save()) {
						$id =  $data->id;
					}
	 			}
			}
		}
	 	return $id;
	}

	/*
	Use 	: SaveServiceProductFromEPR
	Author 	: Kalpak Prajapati
	Date 	: 24 Nov 2021
	*/
	public static function SaveServiceProductFromEPR($request,$service_id=0,$client_id=0,$mrf_id=0)
	{
		$id 			= 0;
		$product_list 	= (isset($request->product_list) && !empty($request->product_list)) ? $request->product_list:"";
		if(!empty($product_list) && is_array($product_list))
		{
			$delete 		= self::where("service_id",$service_id)->delete();
			$ClientMaster 	= WmClientMaster::where('id',$client_id)->first();
			$MRFDepartment 	= WmDepartment::where('id',$mrf_id)->first();
			foreach ($product_list as $Product)
			{
				$ProductRow 					= WmServiceProductMaster::where('id',$Product['product_id'])->where('status',PARA_STATUS_ACTIVE)->first();
				$productId 						= $Product['product_id'];
				$product 						= $ProductRow->product;
				$description 					= $Product['description'];
				$hsn_code 						= $ProductRow->hsn_code;
				$quantity 						= $Product['qty'];
				$rate 							= $Product['rate'];
				$uom 							= $ProductRow->uom;
				$epr_batch_id 					= $Product['batch_id'];
				$epr_batch_invoice_process_id 	= $Product['batch_invoice_process_id'];
				$gross_amt 						= round(($Product['qty'] * $Product['rate']),2);
				if (isset($ClientMaster->gst_state_code) && isset($MRFDepartment->gst_state_code_id)) {
					if ($ClientMaster->gst_state_code == $MRFDepartment->gst_state_code_id) {
						$sgst 		= $ProductRow->sgst;
						$cgst 		= $ProductRow->cgst;
						$igst 		= 0;
						$sgst_amt 	= ($gross_amt > 0)?round((($gross_amt * $sgst)/100),2):0;
						$cgst_amt 	= ($gross_amt > 0)?round((($gross_amt * $cgst)/100),2):0;
						$gst_amt 	= $sgst_amt + $cgst_amt;
						$net_amt 	= $gross_amt + $gst_amt;
					} else {
						$sgst 		= 0;
						$cgst 		= 0;
						$igst 		= $ProductRow->igst;
						$igst_amt 	= ($gross_amt > 0)?round((($gross_amt * $igst)/100),2):0;
						$gst_amt 	= $igst_amt;
						$net_amt 	= $gross_amt + $gst_amt;
					}
				} else {
					$sgst 		= 0;
					$cgst 		= 0;
					$igst 		= 0;
					$igst_amt 	= 0;
					$gst_amt 	= $igst_amt;
					$net_amt 	= $gross_amt + $gst_amt;
				}
				$data 								= new WmServiceProductMapping();
				$data->service_id 					= $service_id;
				$data->product_id 					= $productId;
				$data->product 						= $product;
				$data->description 					= $description;
				$data->hsn_code 					= $hsn_code;
				$data->uom 							= $uom;
				$data->quantity 					= $quantity;
				$data->rate 						= $rate;
				$sameState 							= (empty($igst)) ? true : false;
				$data->gross_amt 					= $gross_amt;
				$data->sgst 						= $sgst;
				$data->cgst 						= $cgst;
				$data->igst 						= $igst;
				$data->gst_amt 						= $gst_amt;
				$data->net_amt 						= $net_amt;
				$data->epr_batch_id 				= $epr_batch_id;
				$data->epr_batch_invoice_process_id = $epr_batch_invoice_process_id;
				if($data->save()) {
					$id =  $data->id;
				}
			}
		}
		return $id;
	}

	/*
	Use 	: GetServiceProduct
	Author 	: Kalpak Prajapati
	Date 	: 07 Jan 2022
	*/
	public static function GetServiceProduct($service_id=0,$wma_id=0,$batch_id=0,$batch_invoice_process_id=0)
	{
		$arrResult 		= array();
		$ProductList 	= self::where("service_id",$service_id)->get()->toArray();
		if (!empty($ProductList)) {
			foreach($ProductList as $ProductRow) {
				$arrResult[] 	= array("id"=>$ProductRow['id'],
										"product"=>$ProductRow['product'],
										"description"=>$ProductRow['description'],
										"hsn_code"=>$ProductRow['hsn_code'],
										"qty"=>$ProductRow['quantity'],
										"rate"=>$ProductRow['rate'],
										"sgst"=>$ProductRow['sgst'],
										"cgst"=>$ProductRow['cgst'],
										"igst"=>$ProductRow['igst'],
										"gst_amt"=>$ProductRow['gst_amt'],
										"gross_amt"=>$ProductRow['gross_amt'],
										"net_amt"=>$ProductRow['net_amt'],
										"batch_id"=>!empty($batch_id)?0:$ProductRow['epr_batch_id'],
										"batch_invoice_process_id"=>!empty($batch_invoice_process_id)?0:$ProductRow['epr_batch_invoice_process_id']);
			}
		}
		return $arrResult;
	}
	/*
	Use 	: View Save Service Product  Detail
	Author 	: Hardyesh Gupta
	Date 	: 15 Sep 2023
	*/

	public static function ViewSaveServiceProduct($request,$service_id=0)
	{
		$id 			= 0;
		$product_list 	= "";
		if(isset($request->product_list) && !empty($request->product_list)){
			if(is_array($request->product_list)){
				$product_list = $request->product_list;	
			}else{
				$product_list = $request->product_list;
				if(is_object($request->product_list)){
					$product_list = json_encode($request->product_list);
				}
				$product_list = json_decode($product_list,true);								
			}
		}		
		
		if(!empty($product_list) && is_array($product_list))
		{
	 		foreach ($product_list as $key => $value)
	 		{
				$id 			= (isset($value["id"]) && !empty($value["id"])) ? $value["id"] : 0;
 				$description 	= (isset($value["description"]) && !empty($value["description"])) ? $value["description"] : "";
				$data 				= self::find($id);
				$data->description 	= $description;
				if($data->save()) {
					$id =  $data->id;
				}
 			}
		}
	 	return $id;
	}
}