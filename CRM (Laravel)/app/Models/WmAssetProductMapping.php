<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmAssetProductMapping extends Model
{
	protected $table 	= 'wm_asset_product_mapping';
	public $timestamps 	= false;

	/*
	Use 	: GetServiceDetails
	Author 	: Upasana
	Date 	: 04 March 2021
	*/

	public static function SaveAssetProduct($request,$asset_id=0){
		$id 			= 0;
		$product_list 	= (isset($request->product_list) && !empty($request->product_list)) ? json_decode($request->product_list,true): "";
		if(!empty($product_list) && is_array($product_list)){
	 		$delete 			= self::where("asset_id",$asset_id)->delete();
			foreach ($product_list as $key => $value){
 				$product 		= (isset($value["product"]) && !empty($value["product"])) ? $value["product"] : "";
 				$description 	= (isset($value["description"]) && !empty($value["description"])) ? $value["description"] : "";
				$hsn_code 		= (isset($value["hsn_code"]) && !empty($value["hsn_code"])) ? $value["hsn_code"] : "";
				$quantity 		= (isset($value["quantity"]) && !empty($value["quantity"])) ? $value["quantity"] : 0;
				$rate 			= (isset($value["rate"]) && !empty($value["rate"])) ? $value["rate"] : "";
				$uom 			= (isset($value["uom"]) && !empty($value["uom"])) ? $value["uom"] : "";
				$gross_amt 		= (isset($value["gross_amt"]) && !empty($value["gross_amt"])) ? $value["gross_amt"] : "";
				$sgst 			= (isset($value["sgst"]) && !empty($value["sgst"])) ? $value["sgst"] : 0;
				$igst 			= (isset($value["igst"]) && !empty($value["igst"])) ? $value["igst"] : 0;
				$cgst 			= (isset($value["cgst"]) && !empty($value["cgst"])) ? $value["cgst"] : 0;
				$gst_amt		= (isset($value["gst_amt"]) && !empty($value["gst_amt"])) ? $value["gst_amt"] : 0;
				$net_amt 		= (isset($value["net_amt"]) && !empty($value["net_amt"])) ? $value["net_amt"] : 0;
				$data 				= new self();
				$data->asset_id 	= $asset_id;
				$data->product 		= $product;
				$data->description 	= $description;
				$data->hsn_code 	= $hsn_code;
				$data->uom 			= $uom;
				$data->quantity 	= $quantity;
				$data->rate 		= $rate;
				$sameState 			= (empty($igst)) ? true : false;
				$gst_data 			= GetGSTCalculation($quantity,$rate,$sgst,$cgst,$igst,$sameState);
				if(!empty($gst_data)){
					$data->gross_amt 	= $gst_data["TOTAL_GR_AMT"];
					$data->sgst 		= $gst_data["SGST_RATE"];
					$data->cgst 		= $gst_data["CGST_RATE"];
					$data->igst 		= $gst_data["IGST_RATE"];
					$data->gst_amt 		= $gst_data["TOTAL_GST_AMT"];
					$data->net_amt 		= $gst_data["TOTAL_NET_AMT"];
				}
				if($data->save()){
					$id =  $data->id;
				}
 			}
		}
	 	return $id;
	}
}