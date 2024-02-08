<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Parameter;
use App\Facades\LiveServices;
use Validator;
use DB;
use JWTAuth;
use Log;
class CustomerSlabwiseInvoiceProductDetails extends Model
{
	protected 	$table 		= 'customer_slabwise_invoice_product_details';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	
	/*
	Use 	: Get Customer Slabwise Invoice Product Detail List
	Date 	: 13, June 2023
	Author 	: Hardyesh Gupta
	*/
	public static function GetCustomerSlabwiseInvoiceProductDetailsList($request){
		/*
        $Today          	= date('Y-m-d');
		$ParameterMaster 	= new Parameter();
		$Parameter       	= $ParameterMaster->getTable(); 
        $selfTbl 			= (new static)->getTable();
        $sortBy         	= ($request->has('sortBy')              && !empty($request->input('sortBy')))    ? $request->input('sortBy')    : "id";
        $sortOrder      	= ($request->has('sortOrder')           && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
        $recordPerPage  	= !empty($request->input('size'))       ?   $request->input('size')         : 5;
        $pageNumber     	= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
        $result = self::select(					
					\DB::raw("$selfTbl.id as id"),
					\DB::raw("$selfTbl.customer_id as customer_id"),
					\DB::raw("$selfTbl.slab_id as slab_id"),
					\DB::raw("$selfTbl.month as month"),
					\DB::raw("$selfTbl.year as year"),
					\DB::raw("$selfTbl.ack_no as ack_no"),
					\DB::raw("$selfTbl.irn as irn"),
					\DB::raw("$selfTbl.signed_qr_code as signed_qr_code"),
					\DB::raw("$selfTbl.invoice_date as invoice_date"),
					\DB::raw("$selfTbl.ack_date as einvoice_date"),
					\DB::raw("$selfTbl.invoice_pdf as invoice_pdf"),
					\DB::raw("$selfTbl.invoice_path as invoice_path"),
					\DB::raw("$selfTbl.created_by as created_by"),
					\DB::raw("$selfTbl.updated_by as updated_by"),
					\DB::raw("DATE_FORMAT($selfTbl.created_at,'%d-%m-%Y') as created_from"),
					\DB::raw("DATE_FORMAT($selfTbl.created_at,'%d-%m-%Y') as created_to"),
					\DB::raw("DATE_FORMAT($selfTbl.updated_at,'%d-%m-%Y') as updated_date"));

        if($request->has('params.id') && !empty($request->input('params.id')))
        {
            $result->where("$selfTbl.id",$request->input('params.id'));
        }
        if($request->has('params.customer_id') && !empty($request->input('params.customer_id')))
        {
            $result->where("$selfTbl.customer_id",'like','%'.$request->input('params.customer_id').'%');
        }        
        if($request->has('params.ack_no') && !empty($request->input('params.ack_no')))
        {
            $result->where("$selfTbl.ack_no",'like','%'.$request->input('params.ack_no').'%');
        }
        if($request->has('params.irn') && !empty($request->input('params.irn')))
        {
            $result->where("$selfTbl.irn",'like','%'.$request->input('params.irn').'%');
        }
        if($request->has('params.invoice_date') && !empty($request->input('params.invoice_date')))
        {
            $result->whereDate("$selfTbl.invoice_date",'like','%'.$request->input('params.invoice_date').'%');
        }
        if($request->has('params.ack_date') && !empty($request->input('params.einvoack_dateice_date')))
        {
            $result->where("$selfTbl.ack_date",'like','%'.$request->input('params.ack_date').'%');
        }
       
        if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to')))
        {
            $result->whereBetween("$selfTbl.created_at",array(date("Y-m-d H:i:s", strtotime($request->input('params.created_from')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('params.created_to')." ".GLOBAL_END_TIME))));
        }else if(!empty($request->input('params.created_from'))){
           $datefrom = date("Y-m-d", strtotime($request->input('params.created_from')));
           $result->whereBetween("$selfTbl.created_at",array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
        }else if(!empty($request->input('params.created_to'))){
           $result->whereBetween("created_at",array(date("Y-m-d", strtotime($request->input('params.created_to'))),$Today));
        }
        //$bindings= LiveServices::toSqlWithBinding($result);
       // print_r($bindings);  
        $data = $result->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
        return $data;
        */
        
    }

	/*
	Use 	: Save Customer Invoice Product Details
	Date 	: 13, June 2023
	Author 	: Hardyesh Gupta
	*/
	public static function SaveCustomerInvoiceProduct($request)
	{
		$id 						= (isset($request->id) 				&& !empty($request->id)) 			? $request->id : 0;
		$invoice_id 		 		= (isset($request->invoice_id) 		&& !empty($request->invoice_id)) 	? $request->invoice_id : 0;
		$product_id 			 	= (isset($request->product_id) 		&& !empty($request->product_id)) 	? $request->product_id : 0;
		$product_max_qty 		 	= (isset($request->product_max_qty) && !empty($request->product_max_qty)) ? $request->product_max_qty : 0;
		$product_min_qty 		 	= (isset($request->product_min_qty) && !empty($request->product_min_qty)) ? $request->product_min_qty : 0;
		$product_surcharge 		 	= (isset($request->product_surcharge) && !empty($request->product_surcharge)) ? $request->product_surcharge : 0;
		$cgst 		 				= (isset($request->cgst) 	&& !empty($request->cgst)) 	 ? $request->cgst : 0;
		$sgst 		 				= (isset($request->sgst) 	&& !empty($request->sgst)) 	 ? $request->sgst : 0;
		$igst 		 				= (isset($request->igst) 	&& !empty($request->igst)) 	 ? $request->igst : 0;
		$collection_qty 		 	= (isset($request->collection_qty) 	&& !empty($request->collection_qty)) 	 ? $request->collection_qty : 0;

		$extra_surcharge 			= (isset($request->extra_surcharge) && !empty($request->extra_surcharge)) ? $request->extra_surcharge : 0;
		$invoice_product_data 		= self::find($id);
		if(!$invoice_product_data){
			$invoice_product_data 				= new self();
			$createdAt 							= date("Y-m-d H:i:s");
			$invoice_product_data->created_at	= $createdAt;
		}else{
			$updatedAt 							= date("Y-m-d H:i:s");
			$invoice_product_data->updated_at	= $updatedAt;
		}
		$invoice_product_data->invoice_id 		= $invoice_id;
		$invoice_product_data->product_id		= $product_id;
		$invoice_product_data->product_max_qty	= $product_max_qty;
		$invoice_product_data->product_min_qty	= $product_min_qty;
		$invoice_product_data->product_surcharge= $product_surcharge;
		$invoice_product_data->cgst 			= $cgst;
		$invoice_product_data->sgst 			= $sgst;
		$invoice_product_data->igst 			= $igst;
		$invoice_product_data->extra_surcharge	= $extra_surcharge;
		$invoice_product_data->collection_qty	= $collection_qty;
		
		if($invoice_product_data->save()){
			$id = $invoice_product_data->id;
		}
		return $id;
	}
	/*
	Use 	: Get Invoice Product Details - Get By ID
	Date 	: 13, June 2023
	Author 	: Hardyesh Gupta
	*/
	public static function CustomerInvoiceProductDetailGetById($id=null)
	{
		$data = "";
		$data =  self::find($id);
		
        if(!empty($data)){
            return $data;    
        }else{
        	return $data;
        } 
	}
}
