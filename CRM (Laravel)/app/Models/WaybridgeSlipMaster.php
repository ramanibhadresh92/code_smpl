<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Facades\LiveServices;
use App\Models\WmClientMaster;
use App\Models\VehicleMaster;
use App\Models\WmDepartment;
use App\Models\WmProductMaster;
use App\Models\CompanyMaster;
use PDF;
class WaybridgeSlipMaster extends Model implements Auditable
{
    protected 	$table 		=	'waybridge_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	/*
	Use 	: To Create customer Way Briedge Slip
	Author 	: Axay Shah
	Date 	: 24 July 2019
	*/

	public static function createWayBridge($request){
		try{
			$id = 0 ;
			$WayBridge 						= new self();
			$WayBridge->rst_no 				= (isset($request->rst_no) && !empty($request->rst_no)) ?  $request->rst_no : 0;
			$WayBridge->vehicle_no 			= (isset($request->vehicle_no) && !empty($request->vehicle_no)) ?  $request->vehicle_no : "";
			$WayBridge->route 				= (isset($request->route) && !empty($request->route)) ?  $request->route : "";
			$WayBridge->address 			= (isset($request->address) && !empty($request->address)) ?  $request->address : "";
			$WayBridge->product_name 		= (isset($request->product_name) && !empty($request->product_name)) ?  $request->product_name : "";
			$WayBridge->mrf_id 				= (isset($request->mrf_id) && !empty($request->mrf_id)) ?  $request->mrf_id : "";
			$WayBridge->mrf_title_id		= (isset($request->mrf_title_id) && !empty($request->mrf_title_id)) ?  $request->mrf_title_id : "";
			$WayBridge->client_name 		= (isset($request->client_name) && !empty($request->client_name)) ?  WmClientMaster::where("id",$request->client_name)->value("client_name"):"";
			$WayBridge->gross_weight 		= (isset($request->gross_weight) && !empty($request->gross_weight)) ?  $request->gross_weight : 0;
			$WayBridge->gross_weight_time 	= (isset($request->gross_weight_time) && !empty($request->gross_weight_time)) ?  $request->gross_weight_time : "";
			$WayBridge->tare_weight_time 	= (isset($request->tare_weight_time) && !empty($request->tare_weight_time)) ?  $request->tare_weight_time : "";
			$WayBridge->tare_weight 		= (isset($request->tare_weight) && !empty($request->tare_weight)) ?  $request->tare_weight : "";
			$WayBridge->city_id 			= (isset($request->city_id) && !empty($request->city_id)) ?  $request->city_id : 0;
			$WayBridge->created_by 			= Auth()->user()->adminuserid;
			$WayBridge->company_id 			= Auth()->user()->company_id;
			if($WayBridge->save()){
				LR_Modules_Log_CompanyUserActionLog($request,$WayBridge->id);
				return $id =  $WayBridge->id;
			}
		}catch(\Exception $e){
			dd($e);
		}

	}


	/*
	Use 	: To Create customer Way Briedge Slip
	Author 	: Axay Shah
	Date 	: 24 July 2019
	*/
	public static function GetById($id){
		try{
			$self 			= (new static)->getTable();
			$ClientMaster 	= new WmClientMaster();
			$VehicleMaster 	= new VehicleMaster();
			$MRFMaster 		= new WmDepartment();
			$Product 		= new WmProductMaster();
			$companyMaster	= new CompanyMaster();
			$TitleMaster	= new WmDepartmentTitleMaster();

			$data = self::select("$self.*",
				\DB::raw("COM.company_name"),
				\DB::raw("($self.gross_weight - $self.tare_weight) as net_weight"),
				\DB::raw("COM.address1 as company_address1"),
				\DB::raw("COM.address2 as company_address2"),
				\DB::raw("COM.phone_office as company_phone_office"),
				\DB::raw("COM.gst_no"),
				\DB::raw("MRF.department_name"),
				\DB::raw("MT.title"),
				\DB::raw("MT.address as mrf_address"),
				\DB::raw("IF(MT.title IS NULL,COM.company_name,MT.title) as title"),
				\DB::raw("IF(MT.title IS NULL,COM.address1,MT.address) as add_1"),
				\DB::raw("IF(MT.title IS NULL,COM.address2,'') as add_2"),
				\DB::raw("'0' as charges"),
				\DB::raw("MRF.signature")
			)
			->leftjoin($companyMaster->getTable()." as COM","$self.company_id","=","COM.company_id")
			->leftjoin($MRFMaster->getTable()." as MRF","$self.mrf_id","=","MRF.id")
			->leftjoin($TitleMaster->getTable()." as MT","$self.mrf_title_id","=","MT.id")
			->where("$self.id",$id)
			// LiveServices::toSqlWithBinding($data);
			->first();
			if($data){
				$data->tare_weight_date 		= (!empty($data->tare_weight_time)) ? date("d/m/Y",strtotime($data->tare_weight_time)) : "-" ;
				$data->tare_weight_time_hours 	= (!empty($data->tare_weight_time)) ? date("H:i",strtotime($data->tare_weight_time)) : "-" ;
				$data->gross_weight_date 		= (!empty($data->gross_weight_time)) ? date("d/m/Y",strtotime($data->gross_weight_time)) : "-" ;
				$data->gross_weight_time_hours 	= (!empty($data->gross_weight_time)) ? date("H:i",strtotime($data->gross_weight_time)) : "-" ;
			}
			return $data;
		}catch(\Exception $e){
			// dd($e);
			prd($e->getMessage());
		}

	}

	/*
	Use 	: List Way Bridge Slip
	Author 	: Axay Shah
	Date 	: 24 July 2019
	*/
	public static function ListWayBridgeSlip($request){
		$self 			= (new static)->getTable();
		$ClientMaster 	= new WmClientMaster();
		$VehicleMaster 	= new VehicleMaster();
		$MRFMaster 		= new WmDepartment();
		$Product 		= new WmProductMaster();
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$ClientName     = ($request->has('params.client_name')   && !empty($request->input('params.client_name'))) ? $request->input('params.client_name') : "";
		$cityId      	= ($request->has('params.city_id')   && !empty($request->input('params.city_id'))) ? $request->input('params.city_id') : "";
		$mrfId      	= ($request->has('params.mrf_id')   && !empty($request->input('params.mrf_id'))) ? $request->input('params.mrf_id') : "";
		$VehicleName    = ($request->has('params.vehicle_no')   && !empty($request->input('params.vehicle_no'))) ? $request->input('params.vehicle_no') : "";
		$Id      		= ($request->has('params.id') && !empty($request->input('params.id'))) ? $request->input('params.id') : "";
		$RSTNO      	= ($request->has('params.rst_no')   && !empty($request->input('params.rst_no'))) ? $request->input('params.rst_no') : "";
		$Route      	= ($request->has('params.route')   && !empty($request->input('params.route'))) ? $request->input('params.route') : "";
		$productName    = ($request->has('params.product_name')   && !empty($request->input('params.product_name'))) ? $request->input('params.product_name') : "";
		$FromDate 		= ($request->has('params.startDate') && !empty($request->input('params.startDate'))) ? date("Y-m-d",strtotime($request->input('params.startDate'))) :"" ;
		$EndDate 		= ($request->has('params.endDate') && !empty($request->input('params.endDate'))) ? date("Y-m-d",strtotime($request->input('params.endDate'))) : "";

		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$cityId         = GetBaseLocationCity();
		$data = self::select("$self.*",
					\DB::raw("MRF.department_name"),
					\DB::raw("MRF.address as mrf_address"),
					\DB::raw("MRF.title"))
				->leftjoin($MRFMaster->getTable()." as MRF","$self.mrf_id","=","MRF.id")
				->where("$self.company_id",Auth()->user()->company_id);

		if($request->has('params.city_id') && !empty($request->input('params.city_id')))
		{
			$data->whereIn("$self.city_id", explode(",",$request->input('params.city_id')));
		}else{
			$data->whereIn("$self.city_id",$cityId);
		}

		if(!empty($ClientName))
		{
			$data->where("$self.client_name","like","%$ClientName%");
		}

		if(!empty($Id))
		{
			$Id = (!is_array($Id)) ? explode(",",$Id) : $Id;
			$data->whereIn("$self.id",$Id);
		}

		if(!empty($RSTNO))
		{
			$data->where("$self.rst_no",$RSTNO);
		}
		if(!empty($Route))
		{
			$data->where("$self.route","like","%$Route%");
		}
		if(!empty($productName))
		{
			$data->where("$self.product_name","like","%$productName%");
		}

		if(!empty($mrfId))
		{
			$data->where("$self.mrf_id",$mrfId);
		}

		if(!empty($VehicleName)){
			$data->where("$self.vehicle_no","like","%$VehicleName%");
		}

		if(!empty($FromDate) && !empty($EndDate)){
			$data->whereBetween("$self.tare_weight_time",array($FromDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		}elseif(!empty($FromDate)){
			$data->whereBetween("$self.tare_weight_time",array($FromDate." ".GLOBAL_START_TIME,$FromDate." ".GLOBAL_END_TIME));
		}elseif(!empty($EndDate)){
			$data->whereBetween("$self.tare_weight_time",array($EndDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		}
		$result 	=  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		$toArray 	= $result->toArray();
		if(isset($toArray['totalElements']) && $toArray['totalElements']>0){
			foreach($toArray['result'] as $key => $value){
				$toArray['result'][$key]['waybridge_url'] = url('/waybridge/print/')."/".passencrypt($value['id']);
			}
		}
		return $toArray;
	}

	/*
	Use 	: Generate
	Author 	: Axay Shah
	Date 	: 24 July 2019
	*/
	public static function GenerateWayBridgePDF($id = 0){
		$TIME 	= time();
		$DATE 	= (date("Y-m-d H:i:s",$TIME));
		$data 	= WaybridgeSlipMaster::GetById($id);
		$PDFFILENAME = "";
		if($data){
			$data->mrf_address  = nl2br($data->mrf_address);
			// return view('welcome',compact("data",$data));
		 	$FILENAME 		= $id.time().".pdf";
			return $pdf = PDF::loadView('email-template.waybridge',compact("data",$data))
			->setPaper('A4','portrait')
			->stream($PDFFILENAME);
		}
		return $PDFFILENAME;
	}
}