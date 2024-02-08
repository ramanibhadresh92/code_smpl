<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CustomerComplaintComment;
use App\Models\CustomerMaster;
use DB;
class CustomerComplaint extends Model
{
    protected 	$table 		=	'customer_complaint';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public      $timestamps =   true;



    public function CustomerComplaint(){
    	return $this->hasMany(CustomerComplaintComment::class,'complaint_id');
    }

    /*
	Use 	: Add Customer Compalint
	Author 	: Axay Shah
	Date 	: 19 June,2019
	*/
	public static function ListCustomerComplaint($request){
		$Today          = date('Y-m-d');
		$table 			= (new static)->getTable();
	    $CustomerMaster = new CustomerMaster();
	    $Customer 		= $CustomerMaster->getTable();
	    $AdminUser 		= new AdminUser();
	    $Admin 			= $AdminUser->getTable();
	    $Parameter 		= new Parameter();
	    $Para 			= $Parameter->getTable();
	    $LocationMaster	= new LocationMaster();
	    $Location		= $LocationMaster->getTable();
		$cityId         = GetBaseLocationCity();
		$sortBy         = ($request->has('sortBy')              && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
	    $sortOrder      = ($request->has('sortOrder')           && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
	    $recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
	    $pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';

	    // $table 			= (new static)->getTable();
	    // $CustomerMaster = CustomerMaster()

		$data	= 	self::select($table.".*",
						DB::raw("CONCAT(CUS.first_name,' ',CUS.last_name) as customer_name"),
						DB::raw("IF(U1.adminuserid IS NULL,'CUSTOMER',CONCAT(U1.firstname,' ',U1.lastname)) AS created_by_name"),
						DB::raw("P1.para_value AS complaint_name"),
						DB::raw("P2.para_value AS complaint_status_name"),
						DB::raw("$Location.city AS city_name")
					)
					->WITH(['CustomerComplaint' => function($query){
            			$query->LEFTJOIN("adminuser","customer_complaint_comment.created_by","=","adminuser.adminuserid");
            			$query->select("customer_complaint_comment.*",DB::raw("IF(adminuser.adminuserid IS NULL,'CUSTOMER',CONCAT(adminuser.firstname,' ',adminuser.lastname)) AS created_by_name"));
        			}])
					->JOIN($Customer." AS CUS",$table.".customer_id","=","CUS.customer_id")
					->JOIN($Para." AS P1",$table.".complaint_type","=","P1.para_id")
					->JOIN($Para." AS P2",$table.".complaint_status","=","P2.para_id")
					->JOIN($Location,"$table.city_id","=","$Location.location_id")
					->LEFTJOIN($Admin." AS U1",$table.".created_by","=","U1.adminuserid");
		$data->where("$table.company_id",Auth()->user()->company_id);

		if($request->has('params.id') && !empty($request->input('params.id'))){
			$id = explode(",",$request->input('params.id'));
          	$data->whereIn("$table.id",$id);
	    }
	    if($request->has('params.complaint_type') && !empty($request->input('params.complaint_type'))){
          	$data->where("$table.complaint_type",$request->input('params.complaint_type'));
	    }
	    if($request->has('params.complaint_status') && !empty($request->input('params.complaint_status'))){
          	$data->where("$table.complaint_status",$request->input('params.complaint_status'));
	    }
	    if($request->has('params.customer_name') && !empty($request->input('params.customer_name'))){
          	$data->where("CUS.first_name","like","%".$request->input('params.customer_name')."%")->orWhere("CUS.last_name","like","%".$request->input('params.customer_name')."%");
	    }
		if($request->has('params.city_id') && $request->input('params.city_id') !=""){
          	$data->where("$table.city_id",$request->input('params.city_id'));
	    }else{
          	$data->whereIn("$table.city_id",$cityId);
	    }

	    if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to')))
		{
			$data->whereBetween("$table.complaint_date",array(date("Y-m-d", strtotime($request->input('params.created_from')))." ".GLOBAL_START_TIME,date("Y-m-d", strtotime($request->input('params.created_to')))." ".GLOBAL_END_TIME));
		}else if(!empty($request->input('params.created_from'))){
		   $data->whereBetween("$table.complaint_date",array(date("Y-m-d", strtotime($request->input('params.created_from')))." ".GLOBAL_START_TIME,$Today));
		}else if(!empty($request->input('params.created_to'))){
			$data->whereBetween("table.complaint_date",array(date("Y-m-d", strtotime($request->input('params.created_to')))." ".GLOBAL_START_TIME,$Today));
		}
		// LiveServices::toSqlWithBinding($data);
		$data->orderBy($sortBy, $sortOrder);
	    $list    = $data->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
	    return $list;
	}

	/*
	Use 	: Add Customer Compalint
	Author 	: Axay Shah
	Date 	: 19 June,2019
	*/
	public static function AddCustomerCompalint($request){
		$InsertedId = 0;
		$compalint = new self();
		$customerId						= (isset($request['customer_id']) && !empty($request['customer_id'])) ? $request['customer_id'] : 0 ;
		$createdBy 						= Auth()->user()->adminuserid;
		$comment 						= (isset($request['comment']) && !empty($request['comment'])) ? $request['comment'] : '';
		$status 						= (isset($request['complaint_status']) && !empty($request['complaint_status'])) ? $request['complaint_status'] : PARA_COMPLAINT_TYPE_OPEN ;
		$compalint->customer_id 		= $customerId;
		$compalint->complaint_type 		= (isset($request['complaint_type']) && !empty($request['complaint_type'])) ? $request['complaint_type'] : 0 ;
		$compalint->complaint_status 	= (isset($request['complaint_status']) && !empty($request['complaint_status'])) ? $request['complaint_status'] : 0 ;
		$compalint->closing_comment		= ($status == PARA_COMPLAINT_TYPE_CLOSE) ? $comment : '';
		$compalint->complaint_date 		= date("Y-m-d H:i:s");
		$compalint->company_id 			= Auth()->user()->company_id;
		$compalint->city_id 			= CustomerMaster::where("customer_id",$customerId)->value('city');
		$compalint->created_by 			= $createdBy;
		if($compalint->save()){
			$InsertedId = $compalint->id;
			if($status == PARA_COMPLAINT_TYPE_OPEN){
				$addComment = new  CustomerComplaintComment();
				$addComment->complaint_id 	= $InsertedId;
				$addComment->comment 		= $comment;
				$addComment->created_by 	= $createdBy;
				$addComment->save();
			}
			LR_Modules_Log_CompanyUserActionLog($request,$compalint->id);
		}
		return $InsertedId;
	}

	/*
	Use 	: Update Customer Compalint
	Author 	: Axay Shah
	Date 	: 19 June,2019
	*/
	public static function UpdateCustomerCompalint($request){
		$id = 0;
		$compalint = self::find($request['id']);
		if($compalint){
			$customerId						= (isset($request['customer_id']) && !empty($request['customer_id'])) ? $request['customer_id'] : 0 ;
			$createdBy 						= Auth()->user()->adminuserid;
			$comment 						= (isset($request['comment']) && !empty($request['comment'])) ? $request['comment'] : '';
			$status 						= (isset($request['complaint_status']) && !empty($request['complaint_status'])) ? $request['complaint_status'] : PARA_COMPLAINT_TYPE_OPEN ;
			$compalint->customer_id 		= $customerId;
			$compalint->complaint_type 		= (isset($request['complaint_type']) && !empty($request['complaint_type'])) ? $request['complaint_type'] : 0 ;
			$compalint->complaint_status 	= (isset($request['complaint_status']) && !empty($request['complaint_status'])) ? $request['complaint_status'] : 0 ;
			$compalint->closing_comment		= ($status == PARA_COMPLAINT_TYPE_CLOSE) ? $comment : '';
			$compalint->complaint_date 		= date("Y-m-d H:i:s");
			$compalint->company_id 			= Auth()->user()->company_id;
			$compalint->city_id 			= CustomerMaster::where("customer_id",$customerId)->value('city');
			$compalint->created_by 			= $createdBy;
			if($compalint->save()){
				$InsertedId = $compalint->id;
				if($status == PARA_COMPLAINT_TYPE_OPEN){
					$addComment = new  CustomerComplaintComment();
					$addComment->complaint_id 	= $InsertedId;
					$addComment->comment 		= $comment;
					$addComment->created_by 	= $createdBy;
					$addComment->save();
				}
				$requestObj = json_encode($request,JSON_FORCE_OBJECT);
				LR_Modules_Log_CompanyUserActionLog($requestObj,$requestObj->id);
			}
		}
		return $compalint;
	}

	/*
	Use 	: Update Customer Compalint
	Author 	: Axay Shah
	Date 	: 20 June,2019
	*/
	public static function GetById($Id){
		$compalint = 0;
		IF($Id > 0){
			$compalint = self::with("CustomerComplaint")->where("id",$Id)->first();
		}
		return $compalint;
	}

	/*
	Use 	: Save Customer Compalint
	Author 	: Kalpak Prajapati
	Date 	: 31 Dec,2019
	*/
	public static function SaveCustomerComplain($request){
		$InsertedId 					= 0;
		$compalint						= new self();
		$customerId						= (isset($request['customer_id']) && !empty($request['customer_id'])) ? $request['customer_id'] : 0 ;
		$createdBy 						= 0;
		$comment 						= (isset($request['comment']) && !empty($request['comment'])) ? $request['comment'] : '';
		$status 						= PARA_COMPLAINT_TYPE_OPEN;
		$compalint->customer_id 		= $customerId;
		$compalint->complaint_type 		= 1015002;
		$compalint->complaint_status 	= PARA_COMPLAINT_TYPE_OPEN;
		$compalint->closing_comment		= ($status == PARA_COMPLAINT_TYPE_CLOSE) ? $comment : '';
		$compalint->complaint_date 		= date("Y-m-d H:i:s");
		$compalint->company_id 			= CustomerMaster::where("customer_id",$customerId)->value('company_id');
		$compalint->city_id 			= CustomerMaster::where("customer_id",$customerId)->value('city');
		$compalint->created_by 			= $createdBy;
		if($compalint->save()){
			$InsertedId = $compalint->id;
			$addComment = new  CustomerComplaintComment();
			$addComment->complaint_id 	= $InsertedId;
			$addComment->comment 		= $comment;
			$addComment->created_by 	= $createdBy;
			$addComment->save();
		}
		return $InsertedId;
	}
}