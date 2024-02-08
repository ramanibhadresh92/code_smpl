<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\VehicleDifferenceMapping;
use App\Models\WmBatchMaster;
use App\Models\VehicleMaster;
use App\Models\WmBatchAuditedProduct;
use App\Models\WmBatchProductDetail;
use App\Models\DispatchDifferenceMapping;
use App\Models\VehicleDriverMappings;
use App\Models\CompanyProductMaster;
use App\Models\AppointmentCollection;
use App\Facades\LiveServices;
use Carbon\Carbon;
class VehicleEarningMaster extends Model implements Auditable
{
    protected 	$table 		=	'vehicle_earning_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts =[
		"total_qty" 	=> "float",
		"total_amount" 	=> "float",
	];
	public function vehicleDetail(){
		return $this->belongesTo(VehicleMaster::class,"vehicle_id");
	}
	public function VehicleDifference(){
		return $this->hasMany(VehicleDifferenceMapping::class,"earning_id");
	}
	public function DispatchDifference(){
		return $this->hasMany(DispatchDifferenceMapping::class,"earning_id");
	}

	/*
	Use 	: List Earning
	Author 	: Axay Shah
	Date 	: 10 Oct,2019
	*/
	public static function ListEarning($startDate,$endDate,$vehicleId = 0){
		// "₹"
		$AmountIcon 	= "";
	 	$KgIcon 		= "";
	 	$Date 			= date("Y-m-d");
		$LastDate 		= date("Y-m-t", strtotime($startDate));
		$response 		= array();
		$vehicleMaster 	= new VehicleMaster();
		$vehicle 		= $vehicleMaster->getTable();
		$Table 			= (new static)->getTable();

		$list = self::select("$Table.vehicle_id","$vehicle.vehicle_type",\DB::raw("
						CASE WHEN $vehicle.owner_name = ''
	       				THEN $vehicle.vehicle_number
	       				ELSE CONCAT($vehicle.owner_name,' - ',$vehicle.vehicle_number)
						END AS vehicle_number"),"$Table.approve_date","$Table.approve_by","$Table.approval_status")
				->join($vehicle,"$Table.vehicle_id","=","$vehicle.vehicle_id")
				->where("$Table.company_id",Auth()->user()->company_id)
				->whereBetween("$Table.earning_date",[$startDate,$endDate]);
				
				if(intval($vehicleId) > 0){
					$list->where("$Table.vehicle_id",$vehicleId);
				}
				$data = $list->groupBy("$Table.vehicle_id")->get();
		if(!empty($data)){

			/*FIND SUNDAY FROM MONTH */
			$FromDate 	= Carbon::parse($startDate)->next(Carbon::SUNDAY); // Get the first friday.
			$ToDate 	= Carbon::parse($endDate);
			$sundays 	= array();
			for ($date = $FromDate; $date->lte($ToDate); $date->addWeek()) {
			    $sundays[] = $date->format('d');
			}

			$j = 0;
			foreach ($data as $result)
	        {
	        	$totalAmount 	= 0;
				$totalQty 		= 0;
	        	$i = 0;
	         	$InnerData = self::select("earning_date","total_qty","total_amount","id","approve_date","approve_by","approval_status")
	         				->whereBetween("$Table.earning_date",[$startDate,$endDate])
	         				->where("vehicle_id",$result->vehicle_id)
	         				->get();
				if($InnerData->count()>0){
					$approve = "";
					$response[$j]['vehicle_type'] 	= $result->vehicle_type;
	         		$response[$j]['vehicle_number'] = $result->vehicle_number;
	         		$response[$j]['vehicle_id'] 	= $result->vehicle_id;
	         		foreach($InnerData as $raw){
	         			$response[$j]['row'][date("d",strtotime($raw->earning_date))]['total_qty'] 		= $raw->total_qty;
	         			$response[$j]['row'][date("d",strtotime($raw->earning_date))]['total_amount'] 	= $raw->total_amount;
	         			$response[$j]['row'][date("d",strtotime($raw->earning_date))]['earning_id'] 			= $raw->id;
	         			$totalAmount 	= $totalAmount + $raw->total_amount;
	         			$totalQty 		= $totalQty + $raw->total_qty;
	         			if($Date == $LastDate && $raw->approve_status == 0){
							$approve =  1;
						}elseif(date("Y-m",strtotime($startDate)) < date("Y-m") && $raw->approve_status == 0){
							$approve = 1;
						}
	         		}
	         		$response[$j]['grand_total_amount'] = _FormatNumberV2(round($totalAmount));
	         		$response[$j]['grand_total_qty'] 	= (float)_FormatNumberV2($totalQty);
	         		$response[$j]['approve_by'] 		= $raw->approve_by;
	         		$response[$j]['approval_status'] 	= $raw->approval_status;
	         		$response[$j]['approve_date'] 		= $raw->approve_date;
	         		$response[$j]['color_code'] 		= ($raw->approval_status == "1") ? APPROVED_COLOR_CODE : "";
	         		$response[$j]['can_approve'] 		= $approve;
	         		$response[$j]['rs_icon'] 			= $AmountIcon;
	         		$response[$j]['kg_icon'] 			= $KgIcon;
	         		$response[$j]['sundays'] 			= $sundays;
	         	}
	         	$j++;

			}
		}
	    return $response;
	}

	/*
	Use 	: Store Vehicle Earning Data
	Author 	: Axay Shah
	Date 	: 10 Oct,2019
	*/
	public static function AddEarning($request){
		try{
			$EarningId 		= 0;
			$VehicleCost	= 0;
			$VehicleType 	= "";
			$vehicleId 		= (isset($request->vehicle_id) 	&& !empty($request->vehicle_id)) ? $request->vehicle_id : 0;
			$EarningDate 	= (isset($request->earning_date) && !empty($request->earning_date)) ? date("Y-m-d",strtotime($request->earning_date)) : date("Y-m-d");
			$VehicleData 	= VehicleMaster::find($vehicleId);
			$VehicleType 	= "";
			$VehicleAmt 	= 0;
			$TOTAL_AMOUNT 	= 0;
			$TOTAL_QUNATITY = 0;
			if($VehicleData){
				$VehicleType 	= $VehicleData->vehicle_type;
				$VehicleAmt  	= $VehicleData->vehicle_cost;
			}

			$GET_AUDIT 		= self::GetAuditedQty($vehicleId,$EarningDate);
			if(!empty($GET_AUDIT)){
				if($VehicleType == VEHICLE_FIXED){
					$TOTAL_AMOUNT 	= ($GET_AUDIT['total_qty'] > 0) ? $VehicleAmt : 0;
				}else{
					$TOTAL_AMOUNT 	= $GET_AUDIT['total_qty'] * $VehicleAmt ;
				}
					$TOTAL_QUNATITY =  $GET_AUDIT['total_qty'];
			}

			$Add 						= new self();
			$Add->vehicle_id 			= $vehicleId;
			$Add->vehicle_type 			= $VehicleType;
			$Add->vehicle_cost 			= $VehicleCost;
			$Add->audited_qty			= (isset($request->audited_qty) 	&& !empty($request->audited_qty)) ? $request->audited_qty : 0;
			$Add->earning_date 			= $EarningDate;
			$Add->dispatch_qty			= (isset($request->dispatch_qty) 	&& !empty($request->dispatch_qty)) ? $request->dispatch_qty : 0;
			$Add->pending_amount		= (isset($request->pending_amount) 	&& !empty($request->pending_amount)) ? $request->pending_amount : 0;
			$Add->pending_amount_reason	= (isset($request->pending_amount_reason) 	&& !empty($request->pending_amount_reason)) ? $request->pending_amount_reason : "";
			$Add->diesel_advance		= (isset($request->diesel_advance) 	&& !empty($request->diesel_advance)) ? $request->diesel_advance : 0;
			$Add->other_advance			= (isset($request->other_advance) 	&& !empty($request->other_advance)) ? $request->other_advance : 0;
			$Add->other_advance_reason 	= (isset($request->other_advance_reason) 	&& !empty($request->other_advance_reason)) ? $request->other_advance_reason : "";
			$Add->deduction				= (isset($request->deduction) 		&& !empty($request->deduction)) ? $request->deduction : 0;
			$Add->deduction_reason 		= (isset($request->deduction_reason) 	&& !empty($request->deduction_reason)) ? $request->deduction_reason : "";
			$Add->total_amount			= (isset($request->total_amount)	&& !empty($request->total_amount)) ? $request->total_amount : 0;
			$Add->total_qty				= (isset($request->total_qty) 		&& !empty($request->total_qty)) ? $request->total_qty : 0;
			$Add->company_id			= Auth()->user()->company_id;
			if($Add->save()){
				$EarningId 			= $Add->id;
				self::addTripAndDispatchData($request,$EarningId,$vehicleId,$VehicleType);
				$VehiCleDiffTotal 	= VehicleDifferenceMapping::where("earning_id",$EarningId)->sum('price');
				$VehiCleDiffQtyTotal= DispatchDifferenceMapping::where("earning_id",$EarningId)->sum('qty');
				$DispatchDiffTotal 	= DispatchDifferenceMapping::where("earning_id",$EarningId)->sum('price');
				$TOTAL_AMOUNT 		= $TOTAL_AMOUNT +  $VehiCleDiffTotal + $DispatchDiffTotal ;
				$TOTAL_AMOUNT 		= $TOTAL_AMOUNT - $Add->deduction;
				$TOTAL_QUNATITY 	= $TOTAL_QUNATITY + $VehiCleDiffQtyTotal;
				self::where("id",$EarningId)->update(['total_amount'=>$TOTAL_AMOUNT,"total_qty"=>$TOTAL_QUNATITY]);
			}
		}catch(\Exception $e){
			dd($e);
		}
	}
	/*
	Use 	: Store Vehicle Earning Data
	Author 	: Axay Shah
	Date 	: 10 Oct,2019
	*/
	public static function EditEarning($request){

		try{
			$EarningId = (isset($request->earning_id) && !empty($request->earning_id)) ? $request->earning_id : 0;
			$Add = self::find($EarningId);
			if($Add){
				$VehicleCost	= 0;
				$VehicleType 	= "";
				$vehicleId 		= (isset($request->vehicle_id) 	&& !empty($request->vehicle_id)) ? $request->vehicle_id : 0;
				$EarningDate 	= (isset($request->earning_date) && !empty($request->earning_date)) ? date("Y-m-d",strtotime($request->earning_date)) : date("Y-m-d");
				$VehicleData 	= VehicleMaster::find($vehicleId);
				$VehicleType 	= "";
				$VehicleAmt 	= 0;
				$TOTAL_AMOUNT 	= 0;
				$TOTAL_QUNATITY = 0;
				if($VehicleData){
					$VehicleType 	= $VehicleData->vehicle_type;
					$VehicleAmt  	= $VehicleData->vehicle_cost;
				}

				$GET_AUDIT 		= self::GetAuditedQty($vehicleId,$EarningDate);
				if(!empty($GET_AUDIT)){
					if($VehicleType == VEHICLE_FIXED){
						$TOTAL_AMOUNT 	= ($GET_AUDIT['total_qty'] > 0) ? $VehicleAmt : 0;
					}else{
						$TOTAL_AMOUNT 	= $GET_AUDIT['total_qty'] * $VehicleAmt ;
					}
						$TOTAL_QUNATITY =  $GET_AUDIT['total_qty'];
				}
				$Add->earning_date 			= (isset($request->earning_date) 	&& !empty($request->earning_date)) ? date("Y-m-d",strtotime($request->earning_date)) : date("Y-m-d");
				$Add->vehicle_id 			= $vehicleId;
				$Add->vehicle_type 			= $VehicleType;
				$Add->vehicle_cost 			= $VehicleCost;
				$Add->audited_qty			= (isset($request->audited_qty) 	&& !empty($request->audited_qty)) ? $request->audited_qty : 0;

				$Add->dispatch_qty			= (isset($request->dispatch_qty) 	&& !empty($request->dispatch_qty)) ? $request->dispatch_qty : 0;
				$Add->pending_amount		= (isset($request->pending_amount) 	&& !empty($request->pending_amount)) ? $request->pending_amount : 0;
				$Add->pending_amount_reason	= (isset($request->pending_amount_reason) 	&& !empty($request->pending_amount_reason)) ? $request->pending_amount_reason : "";
				$Add->diesel_advance		= (isset($request->diesel_advance) 	&& !empty($request->diesel_advance)) ? $request->diesel_advance : 0;
				$Add->other_advance			= (isset($request->other_advance) 	&& !empty($request->other_advance)) ? $request->other_advance : 0;
				$Add->other_advance_reason 	= (isset($request->other_advance_reason) 	&& !empty($request->other_advance_reason)) ? $request->other_advance_reason : "";
				$Add->deduction				= (isset($request->deduction) 		&& !empty($request->deduction)) ? $request->deduction : 0;
				$Add->deduction_reason 		= (isset($request->deduction_reason) 	&& !empty($request->deduction_reason)) ? $request->deduction_reason : "";
				$Add->total_amount			= (isset($request->total_amount)	&& !empty($request->total_amount)) ? $request->total_amount : 0;
				$Add->total_qty				= (isset($request->total_qty) 		&& !empty($request->total_qty)) ? $request->total_qty : 0;
				$Add->company_id			= Auth()->user()->company_id;
				if($Add->save()){
					$vehicleId 			= $Add->vehicle_id;
					self::addTripAndDispatchData($request,$EarningId,$vehicleId,$VehicleType);
					$VehiCleDiffTotal 	= VehicleDifferenceMapping::where("earning_id",$EarningId)->sum('price');
					$VehiCleDiffQtyTotal= DispatchDifferenceMapping::where("earning_id",$EarningId)->sum('qty');
					$DispatchDiffTotal 	= DispatchDifferenceMapping::where("earning_id",$EarningId)->sum('price');
					$TOTAL_AMOUNT 		= $TOTAL_AMOUNT +  $VehiCleDiffTotal + $DispatchDiffTotal ;
					$TOTAL_AMOUNT 		= $TOTAL_AMOUNT - $Add->deduction;
					$TOTAL_QUNATITY 	= $TOTAL_QUNATITY + $VehiCleDiffQtyTotal;
					self::where("id",$EarningId)->update(['total_amount'=>$TOTAL_AMOUNT,"total_qty"=>$TOTAL_QUNATITY]);
				}
			}
		}catch(\Exception $e){
		}
	}

	public static function addTripAndDispatchData($request,$EarningId,$vehicleId,$VehicleType){
		VehicleDifferenceMapping::where("earning_id",$EarningId)->delete();
		DispatchDifferenceMapping::where("earning_id",$EarningId)->delete();
		$customerTrip 		= (isset($request->customer_trip) && !empty($request->customer_trip)) ? $request->customer_trip : "";
		$DispatchQtyData 	= (isset($request->dispatch_qty_data) && !empty($request->dispatch_qty_data)) ? $request->dispatch_qty_data : "";
		if(!empty($customerTrip)){
			$CustomerData 	= json_decode(json_encode($customerTrip));
			foreach($CustomerData as $cus){
				VehicleDifferenceMapping::addVehicleDiffrence($EarningId,$cus->customer_id,$cus->trip);
			}
		}

		if(!empty($DispatchQtyData)){
			$CustomerData 	= json_decode(json_encode($DispatchQtyData));
			foreach($CustomerData as $Disp){
				DispatchDifferenceMapping::AddDispatchDifferenceMapping($EarningId,$Disp->customer_name,$Disp->qty,$Disp->price,$VehicleType);
			}
		}
	}

	/*
	Use 	: Get Total Audited Qty of vehicle
	Author 	: Axay Shah
	Date 	: 14 Oct,2019
	*/

	public static function GetAuditedQty($vehicleId,$date){
		try{
			$RESULT 	= 	array();
			$AuditDate	= 	date("Y-m-d",strtotime($date));
			$startDate 	= 	$AuditDate." ".GLOBAL_START_TIME;
			$endDate 	= 	$AuditDate." ".GLOBAL_END_TIME;
			$SUM 		= 	0;
			$WBPD 		=	new WmBatchProductDetail();
			$WBAP 		=	new WmBatchAuditedProduct();
			$Batch 		=   new WmBatchMaster();
			$PRODUCT	=   new CompanyProductMaster();
			$Table 		= 	$Batch->getTable();
			$FOC_QTY 	=   0;
			$PAID_QTY 	=   0;
			$TOTAL_QTY 	=   0;

			$COLLECTION = 	AppointmentCollection::where("audit_status","1")->whereBetween("collection_dt",array($startDate,$endDate))->where("vehicle_id",$vehicleId)->pluck('collection_id');
			// LiveServices::toSqlWithBinding($COLLECTION);
			$data 		= 	WmBatchMaster::select("P.foc_product",\DB::raw("sum(wap.qty) as audited_qty"))
							->join($WBPD->getTable()." as wpd","$Table.batch_id","=","wpd.batch_id")
							->join($WBAP->getTable()." as wap","wpd.id","=","wap.id")
							->join($PRODUCT->getTable()." as P","wpd.product_id","=","P.id")
							->where("$Table.vehicle_id",$vehicleId)
							->whereIn("$Table.collection_id",$COLLECTION)
							->where("$Table.is_audited","1")
							->groupBy("P.foc_product")
							->get();
			if(!$data->isEmpty()){
				foreach ($data as $value) {
					($value->foc_product == 1) ? $FOC_QTY = $FOC_QTY + $value->audited_qty : $PAID_QTY = $PAID_QTY + $value->audited_qty;
				}
				$TOTAL_QTY = $FOC_QTY + $PAID_QTY;
			}
			$RESULT['foc_qty'] 		= _FormatNumberV2($FOC_QTY);
			$RESULT['paid_qty'] 	= _FormatNumberV2($PAID_QTY);
			$RESULT['total_qty'] 	= _FormatNumberV2($TOTAL_QTY);

			return $RESULT;
		}catch(\Exception $e){
			dd($e);
		}

	}

	/*
	Use 	: Get By Id
	Author 	: Axay Shah
	Date 	: 18 Oct 2019
	*/

	public static function GetById($earningId){
		$compalint 	= 	0;
		$vehicle 	= 	new VehicleMaster();
		$Table 		= 	(new static)->getTable();
		$data 		=  	self::select("$Table.*","v.vehicle_type","v.vehicle_cost","v.owner_name")
				->join($vehicle->getTable()." as v","$Table.vehicle_id","=","v.vehicle_id")
				->with(["VehicleDifference","DispatchDifference"])
				->where("$Table.id",$earningId)
				->first();
				return $data;
	}

	/*
	Use 	: Approve all earning data
	Author 	: Axay Shah
	Date 	: 21 Oct 2019
	*/
	public static function ApproveAllEarning($startDate,$endDate,$vehicleId,$status){
		$data = self::where("vehicle_id",$vehicleId)
				->where("company_id",Auth()->user()->company_id)
				->whereBetween("earning_date",[$startDate,$endDate])
				->update([
					"approval_status" 	=> 	$status,
					"approve_date" 		=> 	date("Y-m-d H:i:s"),
					"approve_by" 		=>	Auth()->user()->adminuserid
				]);
		return $data;
	}

	/*
	Use 	: Vehicle Earning Report
	Author 	: Axay Shah
	Date 	: 01 Nov 2019
	*/
	public static function EarningReport($vehicleId,$startDate,$endDate){

		$FromDate 		= $startDate." ".GLOBAL_START_TIME;
		$ToDate 		= $endDate." ".GLOBAL_END_TIME;
		$ACDTbl 		= new AppointmentCollectionDetail();
		$ACTbl 			= new AppointmentCollection();
		$ACD 			= $ACDTbl->getTable();
		$AC 			= $ACTbl->getTable();
		$TOTALQTY 		= 0;
		$TOTAL_AMOUNT 	= 0;
		$data 			= AppointmentCollectionDetail::select(\DB::raw("SUM($ACD.actual_coll_quantity) AS net_qty"),
			\DB::raw("SUM($ACD.quantity) AS gross_qty"),
			\DB::raw("DATE_FORMAT($AC.collection_dt,'%Y-%m-%d') AS collection_at"),
			"$AC.collection_by"
		)
		->join($AC,"$ACD.collection_id","=","$AC.collection_id")
		->where("$AC.vehicle_id",$vehicleId)
		->whereBetween("$AC.collection_dt",array($startDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME))
		->groupBy("collection_at")
		->get()->toArray();
		$result = array();
		if(!empty($data)){
			$i = 0;
			foreach($data as $value){
				$VEHICLETYPE = "";
				$VEHICLECOST = "";

				$GETQTY =self::GetAuditedQty($vehicleId,$value['collection_at']);
				if(!empty($GETQTY)){
					$TOTALQTY = _FormatNumberV2($GETQTY['total_qty']);
					$VEHICLE_DATA = self::select("vehicle_cost","vehicle_type")
					->where("vehicle_id",$vehicleId)
					->where("earning_date",$value['collection_at'])
					->first();
					if($VEHICLE_DATA){
						$VEHICLETYPE = $VEHICLE_DATA->vehicle_type;
						$VEHICLECOST = $VEHICLE_DATA->vehicle_cost;
						if($VEHICLE_DATA->vehicle_type == VEHICLE_FIXED){
							$TOTAL_AMOUNT 	= ($TOTALQTY > 0) ? $VEHICLE_DATA->vehicle_cost : 0;
						}else{
							$TOTAL_AMOUNT 	= $TOTALQTY * $VEHICLE_DATA->vehicle_cost ;
						}
					}
				}
				$result[$i]['collection_at'] 	= $value['collection_at'];
				$result[$i]['gross_qty'] 		= (float)_FormatNumberV2($value['gross_qty']);
				$result[$i]['net_qty'] 			= (float)_FormatNumberV2($value['net_qty']);
				$result[$i]['total_qty'] 		= (float)_FormatNumberV2($TOTALQTY);
				$result[$i]['total_earn'] 		= (float)_FormatNumberV2($TOTAL_AMOUNT);
				$result[$i]['vehicle_cost']		= _FormatNumberV2($VEHICLECOST);
				$result[$i]['vehicle_type']		= $VEHICLETYPE;
				$result[$i]['adminuserid']		= $value['collection_by'];
				$result[$i]['vehicle_id']		= $vehicleId;
				$i++;
			}
		}

		$SQL = "SELECT
		DATE_FORMAT(
		wm_batch_master.created_date,
		'%Y-%m-%d'
		) AS c_date,
		wm_batch_master.code,
		CASE WHEN 1 = 1 THEN(
			SELECT
			SUM(quantity)
			FROM
			appointment_collection_details

			WHERE
			appointment_collection_details.collection_id in(wm_batch_master.collection_id)
			)
		END AS gross_weight,
		CASE WHEN 1 = 1 THEN(
			SELECT
			SUM(actual_coll_quantity)
			FROM
			appointment_collection_details

			WHERE
			appointment_collection_details.collection_id in(wm_batch_master.collection_id)
			)
		END AS net_weight,
		wm_batch_master.collection_id,
		wm_department.department_name,
		wm_batch_master.batch_id,
		CASE WHEN 1 = 1 THEN(
			SELECT
			SUM(wm_batch_audited_product.qty)
			FROM
			wm_batch_audited_product
			INNER JOIN
			wm_batch_product_detail ON wm_batch_product_detail.id = wm_batch_audited_product.id
			WHERE
			wm_batch_product_detail.batch_id = wm_batch_master.batch_id
			)
		END AS audit_qty
		FROM
		wm_batch_master
		INNER JOIN
		wm_department ON wm_department.id = wm_batch_master.master_dept_id
		WHERE
		wm_batch_master.vehicle_id = ".$vehicleId." AND wm_batch_master.batch_type_status = 0 AND wm_batch_master.created_date BETWEEN '".$FromDate."' AND '".$ToDate."'
		GROUP BY
		wm_batch_master.batch_id";
		$rightSide =  \DB::select($SQL);
		$rightRes = array();
		if(!empty($rightSide)){
			$i = 0;
			foreach($rightSide as $raw){
				$rightRes[$i]['batch_id'] 			= $raw->batch_id;
				$rightRes[$i]['audit_qty'] 			= (float)_FormatNumberV2($raw->audit_qty);
				$rightRes[$i]['gross_weight'] 		= (float)_FormatNumberV2($raw->gross_weight);
				$rightRes[$i]['net_weight'] 		= (float)_FormatNumberV2($raw->net_weight);
				$rightRes[$i]['c_date'] 			= $raw->c_date;
				$rightRes[$i]['code'] 				= $raw->code;
				$rightRes[$i]['collection_id']		= $raw->collection_id;
				$rightRes[$i]['department_name']	= $raw->department_name;

				$i++;
			}
		}
		$res['rightSide'] 	= $rightRes;
		$res['leftSide'] 	= $result;
		return $res;
	}

	/*
	Use 	: List Earning Summery Report
	Author 	: Axay Shah
	Date 	: 01 Feb,2020
	*/
	public static function EarningSummery($startDate,$endDate,$vehicleId = 0){
		// "₹"
		$AmountIcon 	= "";
	 	$KgIcon 		= "";
	 	$Date 			= date("Y-m-d");
		$LastDate 		= date("Y-m-t", strtotime($startDate));
		$response 		= array();
		$vehicleMaster 	= new VehicleMaster();
		$AdminUser 		= new AdminUser();
		$VehiDiff 		= new VehicleDifferenceMapping();
		$VDMTable 		= new VehicleDriverMappings();
		$DispDiff 		= new DispatchDifferenceMapping();
		$CustomerMst	= new CustomerMaster();
		$Cus 			= $CustomerMst->getTable();
		$vehicle 		= $vehicleMaster->getTable();
		$admin 			= $AdminUser->getTable();
		$VD 			= $VehiDiff->getTable();
		$DD 			= $DispDiff->getTable();
		$VDM 			= $VDMTable->getTable();
		$Table 			= (new static)->getTable();
		$result 		= array();
		$SQL 			= self::select("$Table.id",\DB::raw("MONTHNAME($Table.earning_date) AS EARNING_MONTH"),
						\DB::raw("CONCAT(adminuser.firstname,'',adminuser.lastname,' - ',$vehicle.vehicle_number) as Vehicle_Number"), 
						\DB::raw("$vehicle.vehicle_type"),
						\DB::raw("$vehicle.vehicle_cost"),
						\DB::raw("$Table.vehicle_id"),
						\DB::raw("SUM($Table.total_qty) AS TOTAL_QTY"),
						\DB::raw("SUM($Table.total_amount) AS TOTAL_AMOUNT"),
						\DB::raw("SUM($Table.deduction) AS TOTAL_DEDUCTION"),
						\DB::raw("SUM(CASE WHEN 1=1 THEN ( SELECT SUM(price) FROM $VD where $VD.earning_id = $Table.id) END) AS TOTAL_DIFF_MAPPING"),
						\DB::raw("SUM(CASE WHEN 1=1 THEN ( SELECT SUM(qty) FROM $DD where $DD.earning_id = $Table.id) END) AS TOTAL_DISPATCH_QTY"),
						\DB::raw("SUM(CASE WHEN 1=1 THEN ( SELECT SUM(price) FROM $DD where $DD.earning_id = $Table.id) END) AS TOTAL_DISPATCH_PRICE"))
		->JOIN("$vehicle","$Table.vehicle_id","=","$vehicle.vehicle_id")
		->LEFTJOIN("$VDM","$vehicle.vehicle_id","=","$VDM.vehicle_id")
		->LEFTJOIN("$admin","$VDM.collection_by","=","$admin.adminuserid")
		->whereBetween("$Table.earning_date",array($startDate,$endDate));
		if($vehicleId > 0){
			$SQL->where("$Table.vehicle_id",$vehicleId);
		}
		$SQL->groupBy("$vehicle.vehicle_id")->groupBy("EARNING_MONTH");
		$QUERY = LiveServices::toSqlWithBinding($SQL,true);
		$data  = $SQL->get()->toArray();

		if(!empty($data)){
			$FinalTotalQty 				= 0;
			$FinalTotalDispatchQty 		= 0;
			$FinalTotalCollectionQty 	= 0;
			$FinalTotalDiffMapping 		= 0;
			$FinalTotalDispatchPrice 	= 0;
			$FinalTotalDeduction 		= 0;
			$FinalTotalCollectionAmount	= 0;
			$FinalTotalGrossAmount 		= 0;
			$FinalTotalAmount 			= 0;
			foreach($data as $key => $value){
				$TotalAmount 				= ($value['TOTAL_AMOUNT'] > 0) 			? $value['TOTAL_AMOUNT'] 		: 0; 
				$TotalQty 					= ($value['TOTAL_QTY'] > 0) 			? $value['TOTAL_QTY'] 			: 0; 
				$TotalDispatchQty 			= ($value['TOTAL_DISPATCH_QTY'] > 0) 	? $value['TOTAL_DISPATCH_QTY'] 	: 0; 
				$TotalDispatchPrice 		= ($value['TOTAL_DISPATCH_PRICE'] > 0) 	? $value['TOTAL_DISPATCH_PRICE']: 0; 
				$TotalDiffMapping 			= ($value['TOTAL_DIFF_MAPPING'] > 0) 	? $value['TOTAL_DIFF_MAPPING'] 	: 0;
				$TotalDeduction 			= ($value['TOTAL_DEDUCTION'] > 0) 		? $value['TOTAL_DEDUCTION'] 	: 0; 
				$TotalCollectionQty 		= $TotalQty - $TotalDispatchQty;
				$VehicleType = $value['vehicle_type'];
				$VehicleCost = ($value['vehicle_cost'] > 0) ? $value['vehicle_cost'] : 0;
				if($VehicleType == VEHICLE_FIXED){
					$TotalCollectionAmount 	= ($TotalCollectionQty > 0) ? $VehicleCost : 0;
				}else{
					$TotalCollectionAmount 	= $TotalCollectionQty * $VehicleCost ;
				}
				$GrossAmount 				= _FormatNumberV2($TotalCollectionAmount + $TotalDispatchPrice + $TotalDiffMapping); 
				$FinalTotalQty 				= $FinalTotalQty + $TotalQty;
				$FinalTotalDispatchQty 		= $FinalTotalDispatchQty + $TotalDispatchQty;
				$FinalTotalCollectionQty 	= $FinalTotalCollectionQty + $TotalCollectionQty;
				$FinalTotalDiffMapping 		= $FinalTotalDiffMapping + $TotalDiffMapping;
				$FinalTotalDispatchPrice 	= $FinalTotalDispatchPrice + $TotalDispatchPrice;
				$FinalTotalDeduction 		= $FinalTotalDeduction + $TotalDeduction;
				$FinalTotalCollectionAmount	= $FinalTotalCollectionAmount + $TotalCollectionAmount;
				$FinalTotalGrossAmount 		= $FinalTotalGrossAmount + $GrossAmount;
				$FinalTotalAmount 			= _FormatNumberV2($FinalTotalAmount) + _FormatNumberV2($TotalAmount);


				$dispatch_difference_ids 	= self::whereBetween("earning_date",array($startDate,$endDate))->where("vehicle_id",$value['vehicle_id'])->pluck('id');
				/* DISPATCH DIFFERENCE DATA */
				$DispatchArr = array();
				if(!empty($dispatch_difference_ids)){
					$DispatchArr = DispatchDifferenceMapping::whereIn("earning_id",$dispatch_difference_ids)->get();
				}
				$VehicleDiffArr = array();
				if(!empty($dispatch_difference_ids)){
					$VehicleDiffArr = VehicleDifferenceMapping::select("$VD.*",\DB::raw("CONCAT(customer_master.first_name,' ',customer_master.last_name) as customer_name"))->whereIn("earning_id",$dispatch_difference_ids)
									->join("difference_mapping_master","$VD.customer_id","=","difference_mapping_master.id")
									->join("customer_master","difference_mapping_master.customer_id","=","customer_master.customer_id")
									->get();
				}

				/* VEHICLE DIFFERENCE DATA */
				$data[$key]['dispatch_difference'] 	= $DispatchArr;
				$data[$key]['vehicle_difference'] 	= $VehicleDiffArr;

				$data[$key]['TOTAL_COLLECTION_QTY'] 	= _FormatNumberV2($TotalCollectionQty);
				$data[$key]['TOTAL_COLLECTION_AMOUNT'] 	= _FormatNumberV2($TotalCollectionAmount);
				$data[$key]['GROSS_AMOUNT'] 			= _FormatNumberV2($GrossAmount);
			}

			$result['SUM_TOTAL_QTY'] 			= _FormatNumberV2($FinalTotalQty);
			$result['SUM_DISPATCH_QTY'] 		= _FormatNumberV2($FinalTotalDispatchQty);
			$result['SUM_COLLECTION_QTY'] 		= _FormatNumberV2($FinalTotalCollectionQty);
			$result['SUM_DIFF_MAPPING'] 		= _FormatNumberV2($FinalTotalDiffMapping);
			$result['SUM_DISPATCH_PRICE'] 		= _FormatNumberV2($FinalTotalDispatchPrice);
			$result['SUM_DEDUCTION'] 			= _FormatNumberV2($FinalTotalDeduction);
			$result['SUM_COLLECTION_AMOUNT'] 	= _FormatNumberV2($FinalTotalCollectionAmount);
			$result['SUM_GROSS_AMOUNT'] 		= _FormatNumberV2($FinalTotalGrossAmount);
			$result['SUM_TOTAL_AMOUNT'] 		= _FormatNumberV2(round($FinalTotalAmount));
			$result['summery'] = $data;
		}
		// dd($result);
		$result['query'] = $QUERY;
		return $result;
	}
}
