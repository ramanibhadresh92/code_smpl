<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\JobWorkMaster;

class JobWorkOutwardMappingMaster extends Model
{
	protected $table =	'jobwork_outward_product_mapping';
	protected $primaryKey =	'id';

	public function jobwork()
	{
		return $this->belongsTo('App\JobWorkMaster',"jobwork_id");
	}
	/*
	Use 	: Add product
	Author 	: Upasana	
	Date 	: 6/2/2020
	*/

	public static function Addproduct($productid = 0,$jobworkid=0,$quantity="",$price="",$grossamount="",$netamount="",$gst_amount="",$igst="",$cgst="",$sgst="",$reference_no="",$inward_date="",$approve_by="",$actual_quantity="")
	{
		$id    = 0; 
		$data  = new self();
		$data->product_id	  	 = $productid;
		$data->jobwork_id	  	 = $jobworkid;
		$data->quantity       	 = $quantity;
		$data->actual_quantity	 = $actual_quantity;
		$data->price			 = $price;
		$data->gross_amount		 = $grossamount;
		$data->net_amount		 = $netamount;
		$data->gst_amount		 = $gst_amount;
		$data->igst				 = $igst;
		$data->cgst				 = $cgst;
		$data->sgst			 	 = $sgst;
		$data->reference_no		 = $reference_no;
		$data->approve_by		 = $approve_by;
		$data->inward_date		 = !empty($inward_date) ? date("Y-m-d",strtotime($inward_date)) : "0000-00-00";
   
		if($data->save())
		{
			$id = $data;
		}
		return $id;
	}
	/*
	Use 	: Add product
	Author 	: Upasana	
	Date 	: 6/2/2020
	*/
	public static function AddJobworkOutProduct($request)
	{
		$id    				   = 0; 
		$data  				   = new self();
		$data->product_id	   = (isset($request['product_id']) && !empty($request['product_id'])) ? $request['product_id'] : 0;
		$data->jobwork_id	   = (isset($request['jobwork_id']) && !empty($request['jobwork_id'])) ? $request['jobwork_id'] : 0;
		$data->quantity        = (isset($request['quantity']) && !empty($request['quantity'])) ? $request['quantity'] : 0;
		$data->actual_quantity = (isset($request['actual_quantity']) && !empty($request['actual_quantity'])) ? $request['actual_quantity'] : 0;
		$data->product_type  = (isset($request['product_type']) && !empty($request['product_type'])) ? $request['product_type'] : 0;
		$data->price		 = (isset($request['price']) && !empty($request['price'])) ? $request['price'] : 0;
		$data->gross_amount	 = (isset($request['gross_amount']) && !empty($request['gross_amount'])) ? $request['gross_amount'] : 0;
		$data->net_amount	 = (isset($request['net_amount']) && !empty($request['net_amount'])) ? $request['net_amount'] : 0;
		$data->gst_amount	 = (isset($request['gst_amount']) && !empty($request['gst_amount'])) ? $request['gst_amount'] : 0;
		$data->igst			 = (isset($request['igst']) && !empty($request['igst'])) ? $request['igst'] : 0;
		$data->cgst			 = (isset($request['cgst']) && !empty($request['cgst'])) ? $request['cgst'] : 0;
		$data->sgst			 = (isset($request['sgst']) && !empty($request['sgst'])) ? $request['sgst'] : 0;
		$data->reference_no	 = (isset($request['reference_no']) && !empty($request['reference_no'])) ? $request['reference_no'] : 0;
		$data->approve_by	 = (isset($request['approve_by']) && !empty($request['approve_by'])) ? $request['approve_by'] : 0;
		$data->inward_date	 = (isset($request['inward_date']) && !empty($request['inward_date'])) ? $request['inward_date'] : date("Y-m-d");
   		if($data->save())
		{
			$id = $data;
		}
		return $id;
	}
}
