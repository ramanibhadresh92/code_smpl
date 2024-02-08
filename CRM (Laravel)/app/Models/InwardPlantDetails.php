<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use Carbon\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\GtsNameMaster;
use App\Models\WmDepartment;
use App\Models\Parameter;
use App\Models\InwardRemarkMap;
use App\Models\ProductInwardLadger;
use App\Models\WmBatchProductDetail;
use App\Models\StockLadger;
use App\Models\AutoWayBridgeDetails;
use DateTime;
use DateInterval;
use DatePeriod;
class InwardPlantDetails extends Model implements Auditable
{
    protected 	$table 		=	'inward_plant_details';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;


	/*
	Use 	: Store inward plat area details
	Author 	: Axay Shah
	Date 	: 11 Dec,2019
	*/
	public static function StoreInwardDetail($request,$auto=0){
		$TODAY 				= date("Y-m-d");
		$id 				= 0;
		$VEHICLE_ID 		= 0;
		$add 				= new self();
		$VEHICLE_NO 		= (isset($request['vehicle_no']) && !empty($request['vehicle_no'])) ? $request['vehicle_no'] : "";
		$GoodQty 			= (isset($request['qty_good_in']) && !empty($request['qty_good_in'])) ? $request['qty_good_in'] : 0;
		$BadQty 			= 100 - $GoodQty;
		$GTSNAME 			= (isset($request['gts_name']) && !empty($request['gts_name'])) ? $request['gts_name'] : "";
		$GTSID 				= (isset($request['gts_name_id']) && !empty($request['gts_name_id'])) ? $request['gts_name_id'] : 0;
		$IS_USED 			= (isset($request['is_used']) && !empty($request['is_used'])) ? $request['is_used'] : 0;
		$GET_GTS 			= false;
		if($auto == 0){
			if($GTSID > 0){
				$GET_GTS 		= GtsNameMaster::Where("id",$GTSID)->where('cityid',Auth()->user()->city)->first();
			}
		}
		$auto_waybridge_ref_id=(isset($request['auto_waybridge_ref_id']) && !empty($request['auto_waybridge_ref_id'])) ? $request['auto_waybridge_ref_id'] : 0;
		if($auto == 0){
			if(!$GET_GTS){
				$GTSID 			= GtsNameMaster::AddGtsDetails($GTSNAME);
			}else{
				$GTSID 			= $GET_GTS->id;
			}
		}
		$VEHICLE_ID 		= InwardVehicleMaster::AddVehicle($VEHICLE_NO);
		$remark 			= (isset($request['remark']) && !empty($request['remark'])) ? $request['remark'] : "";
		if(!empty($remark) && !is_array($remark)){
			$remark = explode(",", $remark);
		}
		$add->vehicle_no 			= $VEHICLE_NO;
		$add->vehicle_id 			= $VEHICLE_ID;
		$add->gts_name_id 			= $GTSID;
		$add->auto_waybridge_ref_id = $auto_waybridge_ref_id;
		$add->inward_date 			= (isset($request['inward_date']) && !empty($request['inward_date'])) ? date("Y-m-d",strtotime($request['inward_date'])) : "";
		$add->inward_time 			= (isset($request['inward_time']) && !empty($request['inward_time'])) ? $request['inward_time'] : "";
		$add->inward_qty 			= (isset($request['inward_qty']) && !empty($request['inward_qty'])) ? $request['inward_qty'] : 0;
		$add->mrf_id 				= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0;
		$add->qty_good_in 			= $GoodQty;
		$add->qty_bad_in 			= $BadQty;
		$add->created_by 			= Auth()->user()->adminuserid;
		$add->company_id 			= Auth()->user()->company_id;
		$add->product_id 			= FOC_PRODUCT;
		if($add->save()){
			$id 			= $add->id;
			$MRF_ID 		= $add->mrf_id;
			$INWARD_QTY 	= ($add->inward_qty > 0 ) ? _FormatNumberV2($add->inward_qty) : 0;
			$INWARD_DATE 	= date("Y-m-d");
			$USERID 		= Auth()->user()->adminuserid;
			$COMPANY_ID 	= Auth()->user()->company_id;
			$PRODUCT_ID 	= FOC_PRODUCT;
			/*########## AUTO APPROVE INWARD ###########*/
			$status = 1;
			if($auto_waybridge_ref_id > 0){
				$status = 0;
			}
			self::ApproveOrRejectPlantData($id,$status);

			/* INWARD SALES PRODUCT STOCK TO DESTINATION MRF */
			$INWARDDATA['product_id'] 			= $PRODUCT_ID;
			$INWARDDATA['quantity']				= $INWARD_QTY;
			$INWARDDATA['type']					= TYPE_INWARD;
			$INWARDDATA['product_type']			= PRODUCT_PURCHASE;
			$INWARDDATA['mrf_id']				= $MRF_ID;
			$INWARDDATA['ref_id']				= $id;
			$INWARDDATA['company_id']			= $COMPANY_ID;
			$INWARDDATA['inward_date']			= $INWARD_DATE;
			$INWARDDATA['created_by']			= $USERID;
			$INWARDDATA['updated_by']			= $USERID;
			if($auto == 0){
				############# AVG PRICE CALCULATION FOR PURCHASE PRODUCT 08 JAN 2021  ##############
               	$inward_record_id = ProductInwardLadger::AutoAddInward($INWARDDATA);
                $STOCK_AVG_PRICE  = WmBatchProductDetail::GetPurchaseProductAvgPriceN1($MRF_ID,$PRODUCT_ID,$inward_record_id);
                StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,PRODUCT_PURCHASE,$MRF_ID,$INWARD_DATE,$STOCK_AVG_PRICE);
                self::where('id',$id)->update(['is_updated'=>1]);
			}
			if($IS_USED == 1){
				AutoWayBridgeDetails::where('id', $auto_waybridge_ref_id)->update(['is_used'=>$IS_USED]);
				self::where('id',$id)->update(['is_updated'=>1]);
			}
			if(!empty($remark)){
				foreach($remark as $raw){
					InwardRemarkMap::insert([
						"inward_plant_id"	=>	$id,
						"remark_id"			=>	$raw
					]);
				}
			}
			LR_Modules_Log_CompanyUserActionLog($request,$add->id);
		}
		return $id;
	}

	/*
	Use 	: Update inward plat area details
	Author 	: Axay Shah
	Date 	: 17 Dec,2019
	*/
	/*
	Use 	: Update inward plat area details
	Author 	: Axay Shah
	Date 	: 17 Dec,2019
	*/
	public static function UpdateInwardDetail($request){
		$id 					= (isset($request['id']) && !empty($request['id'])) ? $request['id'] : 0;
		$add 					= self::find($id);
		if($add){
			$GoodQty 			= (isset($request['qty_good_in']) && !empty($request['qty_good_in'])) ? $request['qty_good_in'] : 0;
			$BadQty 			= 100 - $GoodQty;

			$GTSNAME 			= (isset($request['gts_name']) && !empty($request['gts_name'])) ? $request['gts_name'] : "";
			$GTSID 				= (isset($request['gts_name_id']) && !empty($request['gts_name_id'])) ? $request['gts_name_id'] : 0;
			$GET_GTS 			= GtsNameMaster::where("gts_name",$GTSID)->orWhere("id",$GTSID)->where('cityid',Auth()->user()->city)->first();
			$GET_GTS 			= false;
			$GTSID 				= (isset($request['gts_name_id']) && !empty($request['gts_name_id'])) ? $request['gts_name_id'] : 0;
			if($GTSID > 0){
				$GET_GTS = GtsNameMaster::Where("id",$GTSID)->where('cityid',Auth()->user()->city)->first();
			}
			if(!$GET_GTS){
				$GTSID 	= GtsNameMaster::AddGtsDetails($GTSNAME);
			}else{
				$GTSID 	= $GET_GTS->id;
			}
			$remark = (isset($request['remark']) && !empty($request['remark'])) ? $request['remark'] : "";
			if(!empty($remark) && !is_array($remark)){
				$remark = explode(",", $remark);
			}
			$add->gts_name_id 	= $GTSID;
			$add->vehicle_no 	= (isset($request['vehicle_no']) && !empty($request['vehicle_no'])) ? $request['vehicle_no'] : "";
			$add->gts_name_id 	= $GTSID;
			$add->inward_date 	= (isset($request['inward_date']) && !empty($request['inward_date'])) ? $request['inward_date'] : "";
			$add->inward_time 	= (isset($request['inward_time']) && !empty($request['inward_time'])) ? $request['inward_time'] : "";
			$add->inward_qty 	= (isset($request['inward_qty']) && !empty($request['inward_qty'])) ? $request['inward_qty'] : 0;
			$add->mrf_id 		= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0;
			$add->qty_good_in 	= $GoodQty;
			$add->qty_bad_in 	= $BadQty;
			$add->created_by 	= Auth()->user()->adminuserid;
			$add->company_id 	= Auth()->user()->company_id;
			$add->product_id 	= FOC_PRODUCT;
			$add->updated_by 	= Auth()->user()->company_id;
			$add->approved_by 	= Auth()->user()->company_id;
			if($add->save()){
				$id 			= $add->id;
				$MRF_ID 		= $add->mrf_id;
				$INWARD_QTY 	= ($add->inward_qty > 0 ) ? _FormatNumberV2($add->inward_qty) : 0;
				$INWARD_DATE 	= date("Y-m-d");
				$USERID 		= Auth()->user()->adminuserid;
				$COMPANY_ID 	= Auth()->user()->company_id;
				$PRODUCT_ID 	= FOC_PRODUCT;
				InwardRemarkMap::where("inward_plant_id",$id)->delete();
				if(!empty($remark)){
					foreach($remark as $raw){
						InwardRemarkMap::insert(["inward_plant_id"=>$id,"remark_id"=>$raw,"created_by"=>Auth()->user()->adminuserid,"created_at"=>date('Y-m-d H:i:s')]);
					}
				}
				/* INWARD PURCHASE PRODUCT STOCK TO DESTINATION MRF */
				$INWARDDATA['product_id'] 			= $PRODUCT_ID;
				$INWARDDATA['quantity']				= $INWARD_QTY;
				$INWARDDATA['type']					= TYPE_INWARD;
				$INWARDDATA['product_type']			= PRODUCT_PURCHASE;
				$INWARDDATA['mrf_id']				= $MRF_ID;
				$INWARDDATA['ref_id']				= $id;
				$INWARDDATA['company_id']			= $COMPANY_ID;
				$INWARDDATA['inward_date']			= $INWARD_DATE;
				$INWARDDATA['created_by']			= $USERID;
				$INWARDDATA['updated_by']			= $USERID;
				############# AVG PRICE CALCULATION FOR PURCHASE PRODUCT 08 JAN 2021  ##############
               	$inward_record_id = ProductInwardLadger::AutoAddInward($INWARDDATA);
                $STOCK_AVG_PRICE  = WmBatchProductDetail::GetPurchaseProductAvgPrice($MRF_ID,$PRODUCT_ID,$inward_record_id);
                StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,PRODUCT_PURCHASE,$MRF_ID,$INWARD_DATE,$STOCK_AVG_PRICE);
                $requestObj = json_encode($request,JSON_FORCE_OBJECT);
				LR_Modules_Log_CompanyUserActionLog($requestObj,$id);
			}
		}
		return $id;
	}

	/*
	Use 	: Update inward plat area details
	Author 	: Axay Shah
	Date 	: 17 Dec,2019
	*/
	public static function GetById($id = 0){
		$parameter  = new Parameter();
		$GtsName  	= new GtsNameMaster();
		$MRF  		= new WmDepartment();
		$self 		= (new static)->getTable();
		$list = self::select("$self.*","MRF.department_name as mrf_name","GTS.gts_name","GTS.cityid")
		->leftjoin($MRF->getTable()." as MRF","$self.mrf_id","=","MRF.id")
		->leftjoin($GtsName->getTable()." as GTS","$self.gts_name_id","=","GTS.id")
		->where("$self.id",$id)
		->first();
		if($list){
			$auto_waybridge_ref_id 	= (isset($list->auto_waybridge_ref_id) && (!empty($list->auto_waybridge_ref_id)) ? $list->auto_waybridge_ref_id : 0);
			$list->isDisbled 		= (!empty($auto_waybridge_ref_id)) ?  true  : false;
			$remark = InwardRemarkMap::GetRemark($id);
			$list->remark 		=  $remark['remark_id'];
			$list->remark_name 	=  $remark['remark_name'];


		}
		return 	$list;
	}
	/*
	Use 	: List Inward Plant area Detils
	Author 	: Axay Shah
	Date 	: 16 Dec,2019
	*/
	public static function ListInwardPlantAreaDetils($request,$report = false){
		$BaseLocationID = Auth()->user()->base_location;
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "$self.inward_date";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 10;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$startDate     	= !empty($request->input('params.startDate')) ?  date('Y-m-d',strtotime($request->input('params.startDate')))   : '';
		$endDate     	= !empty($request->input('params.endDate')) ?   date('Y-m-d',strtotime($request->input('params.endDate')))   : '';
		$parameter  	= new Parameter();
		$GtsName  		= new GtsNameMaster();
		$MRF  			= new WmDepartment();
		$AdminUser 		= new AdminUser();
		$self 			= (new static)->getTable();
		$list = self::select("$self.*","MRF.department_name as mrf_name","GTS.gts_name","GTS.cityid",
			\DB::raw("(CASE
			    WHEN approval_status = 1 THEN 'Accepted'
			    WHEN approval_status = 2 THEN 'Reject'
    			ELSE 'Pending'
			END) AS approval_status_name"),
			\DB::raw("TIME_FORMAT(inward_time,'%h:%i %p') AS inward_time"),
			\DB::raw("U1.username as approved_by_name"),
			\DB::raw("U2.username as created_by_name")
		)
		->join($MRF->getTable()." as MRF","$self.mrf_id","=","MRF.id")
		->leftjoin($GtsName->getTable()." as GTS","$self.gts_name_id","=","GTS.id")
		->leftjoin($AdminUser->getTable()." as U1","$self.approved_by","=","U1.adminuserid")
		->leftjoin($AdminUser->getTable()." as U2","$self.created_by","=","U2.adminuserid");

		if($request->has('params.gts_name') && !empty($request->input('params.gts_name')))
		{
			$list->where('GTS.gts_name','like', '%'.$request->input('params.gts_name').'%');
		}
		if($request->has('params.gts_name_id') && !empty($request->input('params.gts_name_id')))
		{
			$list->where("$self.gts_name_id",$request->input('params.gts_name_id'));
		}
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$list->where("$self.mrf_id",$request->input('params.mrf_id'));
		}

		if($request->has('params.vehicle_id') && !empty($request->input('params.vehicle_id')))
		{
			$list->where("$self.vehicle_id",$request->input('params.vehicle_id'));
		}
		if($request->has('params.status'))
		{
			if($request->input('params.status') == 1){
				$list->where("$self.status",$request->input('params.status'));
			}elseif($request->input('params.status') == "0"){
				$list->where("$self.status",$request->input('params.status'));
			}

		}
		if($request->has('params.vehicle_no') && !empty($request->input('params.vehicle_no')))
		{
			$list->where("$self.vehicle_no",'like', '%'.$request->input('params.vehicle_no').'%');
		}
		if(!empty($startDate) && !empty($endDate))
		{

			 $list->whereBetween("$self.inward_date",array($startDate,$endDate));
		}
		elseif(!empty($startDate))
		{
			 $list->whereBetween("$self.inward_date",$startDate);

		}elseif(!empty($endDate)){
			$list->whereBetween("$self.inward_date",$endDate);
		}
		$list->where("MRF.base_location_id",$BaseLocationID);
		$list->orderBy($sortBy, $sortOrder);
		$result  		= array();
		if(!$report){
			$data    	= $list->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			if(!empty($data)){
				$toArray = $data->toArray();
				if(isset($toArray['totalElements']) && $toArray['totalElements']>0){
					foreach($toArray['result'] as $key => $value){
						$auto_waybridge_ref_id 	= (isset($value['auto_waybridge_ref_id']) && (!empty($value['auto_waybridge_ref_id'])) ? $value['auto_waybridge_ref_id'] : 0);
						$is_updated 			= (isset($value['is_updated']) && (!empty($value['is_updated'])) ? $value['is_updated'] : 0);
						$approval_status 		= (isset($value['approval_status']) && (!empty($value['approval_status'])) ? $value['approval_status'] : 0);
						$inward = InwardRemarkMap::GetRemarkCommaSeprated($value['id']);
						$toArray['result'][$key]["remark"] 		=  (!empty($inward)) ?  $inward[0]['remark_name']  : "";
						$toArray['result'][$key]["editEnabled"] =  (!empty($is_updated)) ?  false  : true;
						$toArray['result'][$key]["isDisbled"] 	=  (!empty($auto_waybridge_ref_id)) ?  true  : false;
					}
					$result = $toArray;
				}
			}
		}else{
			$TOTAL_QTY 	= 0;
			$data    	= $list->get()->toArray();
			if(!empty($data)){
				foreach($data as $key => $value){
					$auto_waybridge_ref_id 	= (isset($value['auto_waybridge_ref_id']) && (!empty($value['auto_waybridge_ref_id'])) ? $value['auto_waybridge_ref_id'] : 0);
					$is_updated 			= (isset($value['is_updated']) && (!empty($value['is_updated'])) ? $value['is_updated'] : 0);
					$inward = InwardRemarkMap::GetRemarkCommaSeprated($value['id']);
					$data[$key]["remark"] =  (!empty($inward)) ?  $inward[0]['remark_name']  : "";
					$data[$key]["editEnabled"] 	=  (!empty($is_updated) ? false : true);
					$data[$key]["isDisbled"] 	=  (!empty($auto_waybridge_ref_id)) ?  true  : false;
					$data[$key]["editEnabled"] 	=  false;
					$data[$key]["isDisbled"] 	=  false;
					$TOTAL_QTY += _FormatNumberV2($value['inward_qty']);
				}
			}
			$result['TOTAL_INWARD_QTY'] 	= ($TOTAL_QTY > 0) ? _FormatNumberV2($TOTAL_QTY) : 0;
			$result['result'] 				= $data;
		}

		// LiveServices::toSqlWithBinding($list);
		return $result;
	}
	/*
	Use 	: Approve or Reject
	Author 	: Axay Shah
	Date 	: 18 Dec 2019
	*/
	public static function ApproveOrRejectPlantData($id,$status = 0){
		return self::where('id',$id)->update([
							"approval_status"	=>	$status,
							"approved_by"		=>	Auth()->user()->adminsuserid
						]);
	}
	/*
	Use 	: Inward Total Number of trip report
	Author 	: Axay Shah
	Date 	: 24 March 2020
	*/
	public static function InwardTotalNumberOfTripReport($request){
		$cityId         = GetBaseLocationCity();
		$GtsName  		= new GtsNameMaster();
		$MRF  			= new WmDepartment();
		$AdminUser 		= new AdminUser();
		$self 			= (new static)->getTable();
		$result 		= array();
		$startDate     	= !empty($request->input('startDate')) ?  date('Y-m-d',strtotime($request->input('startDate')))   : '';
		$endDate     	= !empty($request->input('endDate')) ?   date('Y-m-d',strtotime($request->input('endDate')))   : '';
		$list 			= self::select(
							\DB::raw("SUM($self.inward_qty) as total_qty"),
							\DB::raw("COUNT($self.id) as total_trip"),
							"MRF.department_name as mrf_name",
							"GTS.gts_name",
							"GTS.cityid")
		->join($MRF->getTable()." as MRF","$self.mrf_id","=","MRF.id")
		->join($GtsName->getTable()." as GTS","$self.gts_name_id","=","GTS.id");
		$list->whereIn("GTS.cityid",$cityId);
		if($request->has('gts_name_id') && !empty($request->input('gts_name_id')))
		{
			$list->where("$self.gts_name_id",$request->input('gts_name_id'));
		}
		if($request->has('mrf_id') && !empty($request->input('mrf_id')))
		{
			$list->where("$self.mrf_id",$request->input('mrf_id'));
		}
		if($request->has('status'))
		{
			$status = $request->input('status');
			if($status == "0"){
				$list->where("$self.approval_status",$status);
			}elseif($status > 0){
				$list->where("$self.approval_status",$status);
			}
		}

		if($request->has('vehicle_id') && !empty($request->input('vehicle_id')))
		{
			$list->where("$self.vehicle_id",$request->input('vehicle_id'));
		}
		if(!empty($startDate) && !empty($endDate))
		{
			$list->whereBetween("$self.inward_date",array($startDate,$endDate));
		}
		elseif(!empty($startDate))
		{
			$list->whereBetween("$self.inward_date",$startDate);
		}elseif(!empty($endDate)){
			$list->whereBetween("$self.inward_date",$endDate);
		}
		$list->groupBy("$self.gts_name_id");
		// LiveServices::toSqlWithBinding($list);
		$result = $list->get()->toArray();
		return $result;
	}

	/*
	Use 	: Update store inward detail
	Author 	: Hasmukhi Patel
	Date 	: 19 July 2021
	*/
	public static function UpdateStoreInwardDetail($request){
		$TODAY 				= date("Y-m-d");
		$id 				= 0;
		$VEHICLE_ID 		= 0;
		$inward_plant_id 	= (isset($request['id']) && !empty($request['id'])) ? $request['id'] : "";
		$add 				= self::where('id',$inward_plant_id)->first();
		$VEHICLE_NO 		= (isset($request['vehicle_no']) && !empty($request['vehicle_no'])) ? $request['vehicle_no'] : "";
		$GoodQty 			= (isset($request['qty_good_in']) && !empty($request['qty_good_in'])) ? $request['qty_good_in'] : 0;
		$BadQty 			= 100 - $GoodQty;
		$GTSNAME 			= (isset($request['gts_name']) && !empty($request['gts_name'])) ? $request['gts_name'] : "";
		$GTSID 				= (isset($request['gts_name_id']) && !empty($request['gts_name_id'])) ? $request['gts_name_id'] : 0;
		$GET_GTS 			= false;
		if($GTSID > 0){
			$GET_GTS 		= GtsNameMaster::Where("id",$GTSID)->where('cityid',Auth()->user()->city)->first();
		}

		if(!$GET_GTS){
			$GTSID 			= GtsNameMaster::AddGtsDetails($GTSNAME);
		}else{
			$GTSID 			= $GET_GTS->id;
		}
		$VEHICLE_ID 		= InwardVehicleMaster::AddVehicle($VEHICLE_NO);
		$remark 			= (isset($request['remark']) && !empty($request['remark'])) ? $request['remark'] : "";
		if(!empty($remark) && !is_array($remark)){
			$remark = explode(",", $remark);
		}
		$add->vehicle_no 	= $VEHICLE_NO;
		$add->vehicle_id 	= $VEHICLE_ID;
		$add->gts_name_id 	= $GTSID;
		$add->inward_date 	= (isset($request['inward_date']) && !empty($request['inward_date'])) ? date("Y-m-d",strtotime($request['inward_date'])) : "";
		$add->inward_time 	= (isset($request['inward_time']) && !empty($request['inward_time'])) ? $request['inward_time'] : "";
		$add->inward_qty 	= (isset($request['inward_qty']) && !empty($request['inward_qty'])) ? $request['inward_qty'] : 0;
		$add->mrf_id 		= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0;
		$add->qty_good_in 	= $GoodQty;
		$add->qty_bad_in 	= $BadQty;
		$add->created_by 	= Auth()->user()->adminuserid;
		$add->company_id 	= Auth()->user()->company_id;
		$add->product_id 	= FOC_PRODUCT;
		if($add->save()){
			$id 			= $add->id;
			$MRF_ID 		= $add->mrf_id;
			$INWARD_QTY 	= ($add->inward_qty > 0 ) ? _FormatNumberV2($add->inward_qty) : 0;
			$INWARD_DATE 	= $add->inward_date;
			$USERID 		= Auth()->user()->adminuserid;
			$COMPANY_ID 	= Auth()->user()->company_id;
			$PRODUCT_ID 	= FOC_PRODUCT;
			/*########## AUTO APPROVE INWARD ###########*/
			self::ApproveOrRejectPlantData($id,1);

			/* INWARD SALES PRODUCT STOCK TO DESTINATION MRF */
			$INWARDDATA['product_id'] 			= $PRODUCT_ID;
			$INWARDDATA['quantity']				= $INWARD_QTY;
			$INWARDDATA['type']					= TYPE_INWARD;
			$INWARDDATA['product_type']			= PRODUCT_PURCHASE;
			$INWARDDATA['mrf_id']				= $MRF_ID;
			$INWARDDATA['ref_id']				= $id;
			$INWARDDATA['company_id']			= $COMPANY_ID;
			$INWARDDATA['inward_date']			= $INWARD_DATE;
			$INWARDDATA['created_by']			= $USERID;
			$INWARDDATA['updated_by']			= $USERID;
			ProductInwardLadger::AutoAddInward($INWARDDATA);
			/* INWARD PURCHASE PRODUCT STOCK TO UPDATE FOR SPECIFIC MRF */
			$BEGIN 								= new DateTime($INWARD_DATE);
			$END 								= new DateTime();
			$PRIVIOUS_DATE_CLOSING_STOCK 		=  0;
			$DATE_RANGE 						= new DatePeriod($BEGIN, new DateInterval('P1D'), $END);
			foreach($DATE_RANGE as $DATE_VAL){
				$INWARD_STOCK_QTY 				=  0;
				$OUTWARD_STOCK_QTY 				=  0;
				$OPENING_STOCK_QTY 				=  0;
				$CLOSING_STOCK_QTY 				=  0;
				$PRIVIOUS_CLOSING_STOCK_QTY 	=  0;
				$CURRENT_PROCESS_DATE 			=  $DATE_VAL->format("Y-m-d");
				$GET_DATA 						= StockLadger::GetProductStockData($PRODUCT_ID,PRODUCT_PURCHASE,$MRF_ID,$CURRENT_PROCESS_DATE,$COMPANY_ID);

				if($CURRENT_PROCESS_DATE == $INWARD_DATE){
					if($CURRENT_PROCESS_DATE == $TODAY){
						$OPENING_STOCK_QTY 				= $GET_DATA['opening_stock'];
						$OUTWARD_STOCK_QTY 				= $GET_DATA['outward'];
						$INWARD_STOCK_QTY 				= $GET_DATA['inward'] + $INWARD_QTY;
						$CLOSING_STOCK_QTY 				= 0;
						$PRIVIOUS_DATE_CLOSING_STOCK 	= $CLOSING_STOCK_QTY;
					}else{
						$OPENING_STOCK_QTY 				= $GET_DATA['opening_stock'];
						$OUTWARD_STOCK_QTY 				= $GET_DATA['outward'];
						$INWARD_STOCK_QTY 				= $GET_DATA['inward'] + $INWARD_QTY;
						$CLOSING_STOCK_QTY 				= ($INWARD_STOCK_QTY + $OPENING_STOCK_QTY) - $OUTWARD_STOCK_QTY;
						$PRIVIOUS_DATE_CLOSING_STOCK 	= $CLOSING_STOCK_QTY;
					}
				}else{
					if($CURRENT_PROCESS_DATE == $TODAY){
						$OPENING_STOCK_QTY 				= $PRIVIOUS_DATE_CLOSING_STOCK;
						$OUTWARD_STOCK_QTY 				= 0;
						$INWARD_STOCK_QTY 				= 0;
						$CLOSING_STOCK_QTY 				= 0;
						$PRIVIOUS_DATE_CLOSING_STOCK 	= 0;
					}else{
						$OPENING_STOCK_QTY 				= $PRIVIOUS_DATE_CLOSING_STOCK;
						$OUTWARD_STOCK_QTY 				= $GET_DATA['outward'];
						$INWARD_STOCK_QTY 				= $GET_DATA['inward'];
						$CLOSING_STOCK_QTY 				= ($INWARD_STOCK_QTY + $OPENING_STOCK_QTY) - $OUTWARD_STOCK_QTY;
						$PRIVIOUS_DATE_CLOSING_STOCK 	= $CLOSING_STOCK_QTY;
					}
				}
				StockLadger::createOrUpdate($PRODUCT_ID,PRODUCT_PURCHASE,$MRF_ID,$CURRENT_PROCESS_DATE,$COMPANY_ID,$OPENING_STOCK_QTY,$CLOSING_STOCK_QTY,$INWARD_STOCK_QTY,$OUTWARD_STOCK_QTY,TYPE_PURCHASE);
			}
			AutoWayBridgeDetails::where(\DB::raw('LOWER(REPLACE(vehicle_no," ",""))'), str_replace(' ', '', strtolower($VEHICLE_NO)))->update(['is_used'=>1]);
			self::where('id',$inward_plant_id)->update(['is_updated'=>1]);
			if(!empty($remark)){
				foreach($remark as $raw){
					InwardRemarkMap::insert([
						"inward_plant_id"	=>	$id,
						"remark_id"			=>	$raw
					]);
				}
			}
		}
		return $id;
	}
}
