<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use Carbon\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmProductMaster;
class BailInwardLedger extends Model implements Auditable
{
    protected 	$table 		=	'bail_inward_ledger';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;


	/*
	Use 	: Add Bail Inward MRF wise
	Date 	: 07 Jan,2020
	Author  : Axay Shah
 	*/
	public static function AddBailInward($request){
		$self 					=  new self();
		$id 					= 0;
		$self->product_id 		= (isset($request['product_id']) 		&& !empty($request['product_id'])) 	? $request['product_id']:0;
		$self->bail_qty 		= (isset($request['bail_qty']) 			&& !empty($request['bail_qty'])) 	? $request['bail_qty']:0;
		$self->bail_type 		= (isset($request['bail_type']) 		&& !empty($request['bail_type'])) 	? $request['bail_type']: 0;
		$self->bail_master_id 	= (isset($request['bail_master_id']) 	&& !empty($request['bail_master_id'])) 	? $request['bail_master_id']: 0;
		$self->mrf_id 			= (isset($request['mrf_id']) 			&& !empty($request['mrf_id'])) 		? $request['mrf_id']: ""; 
		$self->bail_inward_date = (isset($request['bail_inward_date']) 	&& !empty($request['bail_inward_date'])) ? $request['bail_inward_date']: ""; 
		$self->company_id 		= Auth()->user()->company_id;
		$self->created_by 		= Auth()->user()->adminuserid;
		if($self->save()){
			$id = $self->id;
		}
		return $id;
	}



	/*
	Use 	: Add Bail Inward MRF wise
	Date 	: 10 Jan,2020
	Author  : Axay Shah
 	*/
	public static function EditBailInward($request){
		$id 					= (isset($request['id']) 				&& !empty($request['id'])) 	? $request['id']:0;
		$self 					= self::find($id);
		if($self){
			$self->product_id 		= (isset($request['product_id']) 		&& !empty($request['product_id'])) 	? $request['product_id']:0;
			$self->bail_qty 		= (isset($request['bail_qty']) 			&& !empty($request['bail_qty'])) 	? $request['bail_qty']:0;
			$self->bail_type 		= (isset($request['bail_type']) 		&& !empty($request['bail_type'])) 	? $request['bail_type']: 0;
			$self->bail_master_id 	= (isset($request['bail_master_id']) 	&& !empty($request['bail_master_id'])) 	? $request['bail_master_id']: 0;
			$self->mrf_id 			= (isset($request['mrf_id']) 			&& !empty($request['mrf_id'])) 		? $request['mrf_id']: ""; 
			$self->bail_inward_date = (isset($request['bail_inward_date']) 	&& !empty($request['bail_inward_date'])) ? $request['bail_inward_date']: ""; 
			$self->company_id 		= Auth()->user()->company_id;
			$self->created_by 		= Auth()->user()->adminuserid;
			if($self->save()){
				$id = $self->id;
			}	
		}
		return $id;
	}


	/*
	Use 	: Get By Id
	Date 	: 10 Jan,2020
	Author  : Axay Shah
 	*/
	public static function BailGetById($id){
		return self::find($id);
	}


	/*
	Use 	: List Inward Details
	Date 	: 08 Jan,2020
	Author  : Axay Shah
 	*/







	public static function ListBailData($request){
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size') : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ? $request->input('pageNumber') : '';
		
		$self 			=  (new static)->getTable();
		$ProductMaster 	=  new WmProductMaster();
		$BailMaster 	=  new BailMaster();
		$Department 	=  new WmDepartment();
		$BailMas 		=  $BailMaster->getTable();
		$PRO 			=  $ProductMaster->getTable();
		$result 		=  array();
		// echo $Today;
		// exit;

			$query = "SELECT
			bail_inward_ledger.*,
			PRO.title,
			PRO.hsn_code,
			PRO.bailing,
			MAS.sales_product_id,
			MAS.product_dec,
			MAS.qty,
			DEPT.department_name,
			IF(
			bail_inward_ledger.bail_inward_date < '
			$Today',
			0,
			1
			) AS isEditable
			FROM
  			bail_inward_ledger
			INNER JOIN
  			wm_product_master AS PRO ON bail_inward_ledger.product_id = PRO.id
			INNER JOIN
  			wm_department AS DEPT ON bail_inward_ledger.mrf_id = DEPT.id
			LEFT JOIN
  			bail_master AS MAS ON bail_inward_ledger.bail_master_id = MAS.id";

  			$where = " where bail_inward_ledger.company_id=".Auth()->user()->company_id;
  			
  			if($request->has('params.id') && !empty($request->input('params.id')))
			{
				$where .= " AND bail_inward_ledger.id = ".$request->input('params.id');
			}


			if($request->has('params.product_id') && !empty($request->input('params.product_id')))
			{
				$where .= " AND bail_inward_ledger.product_id = ".$request->input('params.product_id');
			}
			if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
			{
				$where .= " AND bail_inward_ledger.mrf_id = ".$request->input('params.mrf_id');
			}
			if($request->has('params.bail_master_id') && !empty($request->input('params.bail_master_id')))
			{
				$where .= " AND bail_inward_ledger.bail_master_id = ".$request->input('params.bail_master_id');
			}
			if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to')))
			{
				$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_from')));
				$ENDDATE   = date("Y-m-d", strtotime($request->input('params.created_to')));
				$where .= " AND (bail_inward_ledger.bail_inward_date BETWEEN '".$STARTDATE."' AND '".$ENDDATE."')";
			}else if(!empty($request->input('params.created_from'))){
				$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_from')));
				$where .= " AND (bail_inward_ledger.bail_inward_date BETWEEN '".$STARTDATE."' AND '".$Today."')";
			   
			}else if(!empty($request->input('params.created_to'))){
				$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_to')));
				$where .= " AND (bail_inward_ledger.bail_inward_date BETWEEN '".$STARTDATE."' AND '".$Today."')";
			}
			$where.= " order by ".$sortBy." $sortOrder";
			$result 	= $query.$where;
			$GetCount  	= \DB::select($result);
			if(empty($pageNumber)){
		 		$pageNumber = 1;
		 	}
		 	$start_from = ($pageNumber-1) * $recordPerPage;
		 	$RawQuery 	= $result." LIMIT $start_from, $recordPerPage";
		 	$raw  		= \DB::select($RawQuery);
		 	$output['result'] 			= $raw;
		 	$output['pageNumber'] 		= $pageNumber;
		 	$output['totalElements'] 	= count($GetCount);
		 	$output['size'] 			= $recordPerPage;
		 	$output['totalPages'] 		= ceil(count($GetCount)/$recordPerPage);
			return $output;
	}
	
	
}
