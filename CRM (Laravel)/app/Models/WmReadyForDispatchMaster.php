<?php

namespace App\Models;

use App\Facades\LiveServices;
use Illuminate\Database\Eloquent\Model;
use Validator;
use DB;
use Log;
use App\Models\WmClientMaster;
use App\Models\LocationMaster;
use App\Models\WmProductMaster;
use App\Models\WmDepartment;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class WmReadyForDispatchMaster extends Model implements Auditable
{
	use AuditableTrait;
	protected 	$table 		=	'wm_ready_for_dispatch_master';
	protected 	$guarded 	=	['id'];
	protected 	$primaryKey =	'id'; // or null
	public 		$timestamps = 	true;
	
	/*
	Use 	: List Department
	Author 	: Axay Shah
	Date 	: 03 June,2019
	*/
	public static function ListReadyForSales($request,$isPainate=true){
		try{
			$self 		= (new static)->getTable(); 
			$Department = new WmDepartment();
			$Product 	= new WmProductMaster();
			$Today      = date('Y-m-d');
			$cityId     = GetBaseLocationCity();
			$sortBy     = ($request->has('sortBy') && !empty($request->input('sortBy'))) ? $request->input('sortBy') : "id";
			$sortOrder  = ($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
			$recordPerPage = !empty($request->input('size')) ? $request->input('size') : DEFAULT_SIZE;
			$pageNumber = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
			$createdAt = ($request->has('params.created_from') && $request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input("params.created_from"))) : "";
			$createdTo = ($request->has('params.created_to') && $request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input("params.created_to"))) : "";
			$data 	= 	self::select("$self.*",
									"P.title",
									"MRF.department_name")
			->join($Department->getTable()."  AS MRF","$self.mrf_id","=","MRF.id")
			->join($Product->getTable()."  AS P","$self.sales_product_id","=","P.id")
			->where("$self.company_id",Auth()->user()->company_id);
			if($request->has('params.city_id') && !empty($request->input('params.city_id')))
			{
				$data->whereIn("MRF.location_id", explode(",",$request->input('params.city_id')));
			}else{
				$data->whereIn("MRF.location_id",$cityId);
			}

			if($request->has('params.sales_product_id') && !empty($request->input('params.sales_product_id')))
			{
				$data->where("$self.sales_product_id", $request->input('params.sales_product_id'));
			}
			if(!empty($createdAt) && !empty($createdTo)){
				$data->whereBetween("$self.ready_for_date",[$createdAt,$createdTo]);
			}elseif(!empty($createdAt)){
				$data->whereBetween("$self.ready_for_date",[$createdAt,$createdAt]);
			}elseif(!empty($createdTo)){
				$data->whereBetween("$self.ready_for_date",[$createdTo,$createdTo]);
			}
			//LiveServices::toSqlWithBinding($data);
			if($isPainate == true){
				$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			}else{
				$result = $data->get();
			}
			return $result;
		}catch(\Exception $e){
			prd($e->getMessage());
		}
		
	}

	/*
	Use 	: Add Product Price with client
	Author 	: Axay Shah
	Date 	: 19 June,2019
	*/
	public static function CreateReadyForDispatch($request){
		$id 					= 0 ;
		$Add 					= new self();
		$Add->sales_product_id 	= (isset($request->sales_product_id) && !empty($request->sales_product_id)) ? $request->sales_product_id : 0;
		$Add->mrf_id			= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : " ";
		$Add->estimated_qty		= (isset($request->estimated_qty) && !empty($request->estimated_qty)) ? $request->estimated_qty : 0;
		$Add->ready_for_date 	= (isset($request->ready_for_date) && !empty ($request->ready_for_date))? date("Y-m-d",strtotime($request->ready_for_date)) : date("Y-m-d");
		$Add->created_by 		= Auth()->user()->adminuserid;
		$Add->company_id 		= Auth()->user()->company_id;
		if($Add->save()){
			$id = $Add->id;
		}
		return $id;
	}

	

	
	
}



