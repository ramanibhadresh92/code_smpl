<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\Parameter;
use App\Models\WmDepartment;
use App\Models\AdminUser;
use App\Models\ShiftProductEntryMaster;
use App\Models\ShiftTimingApprovalMaster;
use App\Facades\LiveServices;
use DateTime;
class ShiftTimingMaster extends Model implements Auditable
{
    protected 	$table 		=	'shift_timing_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;

	public function shiftRunningHours(){
		return $this->hasMany(ShiftRunningHoursMaster::class,"shift_timing_id");
	}

	public function ShiftProduct(){
		return $this->hasMany(ShiftProductEntryMaster::class,"shift_timing_id");
	}

	/*
	Use 	: Add Shift Timing 
	Author 	: Axay Shah
	Date  	: 01 April,2020
	*/
	public static function AddShiftTiming($request){
		try{
			$id 				= 0;
			$date 				= date("Y-m-d");
			$data 				= new self();
			$START_DATE 		= (isset($request->start_date) && !empty($request->start_date)) ?  date("Y-m-d",strtotime($request->start_date)) : "";
			$END_DATE 			= (isset($request->end_date) && !empty($request->end_date)) ?  date("Y-m-d",strtotime($request->end_date)) : "";

			$START_TIME 		= (isset($request->start_time) && !empty($request->start_time)) ?  date("H:i:s",strtotime($request->start_time)) : "";
			$END_TIME 			= (isset($request->end_time) && !empty($request->end_time)) ?  date("H:i:s",strtotime($request->end_time)) : "";
			$data->shift_id 		= (isset($request->shift_id) && !empty($request->shift_id)) ?  $request->shift_id : 0;
			$data->mrf_id 			= (isset($request->mrf_id) && !empty($request->mrf_id)) ?  $request->mrf_id : 0;
			$data->total_inward_qty = (isset($request->total_inward_qty) && !empty($request->total_inward_qty)) ?  $request->total_inward_qty : 0;
			$data->start_date 		= $START_DATE;
			$data->end_date 		= $END_DATE;
			$data->start_time 		= $START_TIME;
			$data->end_time 		= $END_TIME;
			$data->startdatetime 	= $START_DATE." ".$START_TIME;
			$data->enddatetime 		= $END_DATE." ".$END_TIME;
			$data->created_by 		= Auth()->user()->adminuserid;
			$data->company_id 		= Auth()->user()->company_id;
			if($data->save()){
				$id = $data->id;
				$DateBeforYesterday = date('Y-m-d',strtotime("-1 days"));
				if($START_DATE < $DateBeforYesterday){
					ShiftTimingApprovalMaster::AddShiftTimingApproval($id,$data->startdatetime,$data->enddatetime,$data->shift_id,$data->mrf_id);
				}
			}
			return $id;
		}catch(\Exception $e){
			dd($e);
		}
	}
	/*
	Use 	: Add Shift
	Author 	: Axay Shah
	Date  	: 22 May,2020
	*/
	public static function addShift($start_date,$end_date,$start_time,$end_time,$mrf_id=0,$shift_id=0,$companyId = 1,$createdBy = 0,$totalInward=0)
	{
		$id 					= 0;
		$data 					= new self();
		$data->shift_id 		= $shift_id;
		$data->mrf_id 			= $mrf_id;
		$data->total_inward_qty = $totalInward;
		$data->start_date 		= $start_date;
		$data->end_date 		= $end_date;
		$data->start_time 		= $start_time;
		$data->end_time 		= $end_time;
		$data->startdatetime 	= $start_date." ".$start_time;
		$data->enddatetime 		= $end_date." ".$end_time;
		$data->created_by 		= (isset(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : $createdBy;
		$data->company_id 		= $companyId;
		if($data->save()){
			$id = $data->id;
		}
		return $id;
	}


	/*
	Use 	: Update Shift Timing
	Author 	: Axay Shah
	Date  	: 01 April,2020
	*/
	public static function UpdateShiftTiming($request){
		try{
			$date 				= date("Y-m-d H:i:s");
			$ID 				= (isset($request->id) && !empty($request->id)) ?  $request->id : 0;
			$data 				= self::find($ID);
			if($data){
				$START_DATE 		= (isset($request->start_date) && !empty($request->start_date)) ?  date("Y-m-d",strtotime($request->start_date)) : "";
				$END_DATE 			= (isset($request->end_date) && !empty($request->end_date)) ?  date("Y-m-d",strtotime($request->end_date)) : "";

				$START_TIME 		= (isset($request->start_time) && !empty($request->start_time)) ?  date("H:i:s",strtotime($request->start_time)) : "";
				$END_TIME 			= (isset($request->end_time) && !empty($request->end_time)) ?  date("H:i:s",strtotime($request->end_time)) : "";
				$MRF_ID 			= (isset($request->mrf_id) && !empty($request->mrf_id)) ?  $request->mrf_id : 0;
				$SHIFT_ID 			= (isset($request->shift_id) && !empty($request->shift_id)) ?  $request->shift_id : 0;
				$data->total_inward_qty = (isset($request->total_inward_qty) && !empty($request->total_inward_qty)) ?  $request->total_inward_qty : 0;
				$PROCESS_TIME 		= (isset($request->process_time) && !empty($request->process_time)) ?  $request->process_time : "";
				$data->start_date 	= $START_DATE;
				$data->end_date 	= $END_DATE;
				$data->start_time 	= $START_TIME;
				$data->end_time 	= $END_TIME;
				$data->startdatetime = $START_DATE." ".$START_TIME;
				$data->enddatetime 	= $END_DATE." ".$END_TIME;
				$data->shift_id 	= $SHIFT_ID;
				$data->mrf_id 		= $MRF_ID;
				$data->updated_by 	= Auth()->user()->adminuserid;
				$data->company_id 	= Auth()->user()->company_id;	
				$STARTDATETIME 		= $START_DATE." ".$START_TIME;
				$ENDDATETIME 		= $END_DATE." ".$END_TIME;
				/*THERE IS NO NEED TO APPROVAL BECAUSE EDIT RIGHTS FOR SELECTED PERSON*/
				// if(($START_DATE != $data->START_DATE && $START_TIME != $data->start_time) || $request->shift_id != $data->shift_id || $request->mrf_id != $data->mrf_id){
				// 	ShiftTimingApprovalMaster::AddShiftTimingApproval($id,$STARTDATETIME,$ENDDATETIME,$SHIFT_ID,$MRF_ID,true);
				// 	return $ID;
				// }
				if($data->save()){
					ShiftRunningHoursMaster::where("shift_timing_id",$ID)->delete();
					$JSON_PROCESS_TIME = json_decode($request->process_time,true);
					if(!empty($JSON_PROCESS_TIME)){
						foreach($JSON_PROCESS_TIME AS $RAW){
							if(!empty($RAW['start_time']) && !empty($RAW['end_time'])){
								ShiftRunningHoursMaster::AddShiftRunningHours($SHIFT_ID,$ID,$MRF_ID,$RAW['start_time'],$RAW['end_time'],$RAW['start_date'],$RAW['end_date']);
							}
						}
						$TOTAL_PROCESS_TIME = self::GetProcessTime($ID);
						if(!empty($TOTAL_PROCESS_TIME)){
							self::where("id",$ID)->update(["total_process_time"=>$TOTAL_PROCESS_TIME]);
						}
					}
				}
				return $ID;
			}
			return 0;
		}catch(\Exception $e){
			dd($e);
		}
	}

		/*
	Use 	: List Shift Data
	Author 	: Axay Shah
	Date  	: 02 April,2020
	*/
	public static function ListShiftTiming($request,$fromMobile=false){

		$Parameter 		= new Parameter();
		$Department 	= new WmDepartment();
		$self 			= (new static)->getTable();
		$Admin 			= new AdminUser();
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy') && !empty($request->input('sortBy'))) ? $request->input('sortBy') : "id";
		$sortOrder      = ($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size') : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber'): '';
		$cityId         = GetBaseLocationCity();

		$data = self::select("$self.*",
					\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
					\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"),
					\DB::raw("PARA.para_value as shift_name"),
					\DB::raw("MRF.department_name")
				)->with(["shiftRunningHours","ShiftProduct" => function($q){
					$q->join("wm_product_master","shift_product_entry_master.product_id","=","wm_product_master.id");
					$q->select("wm_product_master.title",
								"wm_product_master.hsn_code",
								\DB::raw("shift_product_entry_master.id"),
								\DB::raw("shift_product_entry_master.shift_timing_id"),
								\DB::raw("shift_product_entry_master.qty as total_qty"),
								\DB::raw("shift_product_entry_master.product_id")
							);
					}])
		->join($Parameter->getTable()." as PARA","$self.shift_id","=","PARA.para_id")
		->join($Department->getTable()." as MRF","$self.mrf_id","=","MRF.id")
		->leftjoin($Admin->getTable()." as U1","$self.created_by","=","U1.adminuserid")
		->leftjoin($Admin->getTable()." as U2","$self.updated_by","=","U2.adminuserid");

		if($fromMobile){
			$ASSIGN_MRF_ID = (!empty(Auth()->user()->mrf_user_id)) ? Auth()->user()->mrf_user_id : 0;
			$data->where("$self.mrf_id",$ASSIGN_MRF_ID);
			$START_DATE = ($request->has('startDate') && !empty($request->input('startDate'))) ? date("Y-m-d",strtotime($request->input('startDate'))): "";
			$END_DATE 	= ($request->has('endDate') && !empty($request->input('endDate'))) ? date("Y-m-d", strtotime($request->input('startDate'))) : "";
			$SHIFT_ID 	= ($request->has('shift_id') && !empty($request->shift_id)) ? $request->input("shift_id") : 0;
		}else{
			$START_DATE = ($request->has('params.startDate') && !empty($request->input('params.startDate'))) ? date("Y-m-d",strtotime($request->input('params.startDate'))) : "";
			$END_DATE 	= ($request->has('params.endDate') && !empty($request->input('params.endDate'))) ? date("Y-m-d",strtotime($request->input('params.startDate'))) : "";
			$SHIFT_ID 	= ($request->has('params.shift_id') && !empty($request->input('params.shift_id'))) ? $request->input("params.shift_id") : ""; 
		}

		if($request->has('params.id') && !empty($request->input('params.id')))
		{
			$id 	= $request->input('params.id');
			if(!is_array($request->input('params.id'))){
				$id = explode(",",$request->input("params.id"));	
			}
			$data->where("$self.id",$id);
		}
		if(!empty($SHIFT_ID))
		{
			$data->where("$self.shift_id",$SHIFT_ID);
		}
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$data->where("$self.mrf_id",$request->input('params.mrf_id'));
		}
		if(!empty($START_DATE) && !empty($END_DATE))
		{
			 $data->where("$self.start_date",$START_DATE);
			 $data->orWhere("$self.end_date",$END_DATE);	
		}else if(!empty($START_DATE)){
		  
		    $data->where("$self.start_date",$START_DATE);
		}else if(!empty($END_DATE)){
		   $data->where("$self.end_date",$END_DATE);
		}
		$data->whereIn("MRF.location_id",$cityId);
		$data->where("$self.company_id",Auth()->user()->company_id);
		
		// LiveServices::toSqlWithBinding($data);
		$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage);

		if(!empty($result)){
			$toArray = $result->toArray();
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0){
				foreach($toArray['result'] as $key => $value){
					$APPROVAL_STATUS = ShiftTimingApprovalMaster::where("shift_timing_id",$value['id'])->orderBy('id','DESC')->first();
					$toArray['result'][$key]['approval_status'] 		= 1; 
					$toArray['result'][$key]['approval_status_name'] 	= 'Approved'; 
					if($APPROVAL_STATUS){
						if($APPROVAL_STATUS->status == 0 || $APPROVAL_STATUS->status == 2){
							$toArray['result'][$key]['approval_status'] = $APPROVAL_STATUS->status; 
							$toArray['result'][$key]['approval_status_name'] 	= ($APPROVAL_STATUS->status == 2) ? 'Rejected' : "Pending"; 
						}	
					}
				}
				$result = $toArray;
			}
		}
		return $result;
	}

	/*
	Use 	: Input Output Report
	Author 	: Axay Shah
	Date 	: 07 April 2020
	*/
	public static function ShiftInputOutputReport($request){
		$cityId          = GetBaseLocationCity();
		$ShiftProductTbl = new ShiftProductEntryMaster();
		$MRFTbl			 = new WmDepartment();
		$PARA  			 = new parameter();
		$AdminUser 		 = new AdminUser();
		$Product 		 = new WmProductMaster();
		$self 			 = (new static)->getTable();
		$result 		 = array();
		$Month     		 = !empty($request->input('month')) 	? $request->input('month')  : date('m' );
		$Year      		 = !empty($request->input('year')) 	?  $request->input('year')  : date('Y' );
		$MRF      		 = !empty($request->input('mrf_id')) 	?  $request->input('mrf_id')  	: "";
		$DATE     		 = !empty($request->input('date')) 	? date("Y-m-d",strtotime($request->input('date')))  : date('Y-m-d');
		$SHIFT_ID     	= !empty($request->input('shift_id')) 	? $request->input('shift_id')  : 0;
		$startDate 		 = $Year."-".$Month."-01 00:00:00";
		$endDate 		 = date("Y-m-t", strtotime($startDate))." 23:59:59";
		$RESU 			 = array();
		$TOTAL_ALL_QTY 	 = 0; 
		$array 			 = array();
		$SHIFT 			 = array();
		$DATA 			 = Parameter::where("para_parent_id",PARA_SHIFT_TYPE);
		if(!empty($SHIFT_ID)){
			$DATA->where("para_id",$SHIFT_ID);
		}
		$SHIFT = $DATA->orderBy("para_id")->get();
		if($SHIFT){
			foreach($SHIFT AS $key => $value){
				$array[$key]['shift_name'] 	= $value['para_value'];
				$array[$key]['shift_id'] 	= $value['para_id'];
				$productData 	= self::select(
					\DB::raw("SPEM.qty"),
					\DB::raw("$self.start_date"),
					\DB::raw("$self.start_time"),
					\DB::raw("$self.end_date"),
					\DB::raw("$self.end_time"),
					\DB::raw("$self.end_date"),
					\DB::raw("$self.total_process_time"),
					\DB::raw("$self.total_inward_qty"),
					\DB::raw("P.title"),
					\DB::raw("SPEM.product_id"),
					\DB::raw("MRF.department_name"),
					\DB::raw("$self.mrf_id")

				)
				->leftjoin($ShiftProductTbl->getTable()." as SPEM","SPEM.shift_timing_id","=","$self.id")
				->join($Product->getTable()." as P","SPEM.product_id","=","P.id")
				->join($MRFTbl->getTable()." as MRF","$self.mrf_id","=","MRF.id")
				->where("SPEM.shift_id",$value['para_id'])
				->where("$self.start_date",$DATE);
				if(!empty($MRF)){
					$productData->where("SPEM.mrf_id",$MRF);
				}
				$PRODUCT_ARR 		= $productData->get()->toArray();
				$TOTAL_QTY 			= 0;
				$TOTAL_INWARD_QTY 	= 0;
				if(!empty($PRODUCT_ARR)){
					foreach($PRODUCT_ARR AS $PRODUCT => $PRO){
						$TOTAL_INWARD_QTY 					= $PRO['total_inward_qty']; 
						$array[$key]['department_name'] 	= $PRO['department_name'];
						$array[$key]['mrf_id'] 				= $PRO['mrf_id'];
						$array[$key]['total_process_time'] 	= $PRO['total_process_time'];
						if(empty($PRO['total_process_time'])){
							$START_DATE_TIME 	= $PRO['start_date']." ".$PRO['start_time'];
							$END_DATE_TIME 	 	= $PRO['end_date']." ".$PRO['end_time'];
							$array[$key]['total_process_time'] = GetDiffInHoursMinite($START_DATE_TIME,$END_DATE_TIME);
						}
						$array[$key]['start_date'] 		= $PRO['start_date'];
						$array[$key]['start_time'] 		= $PRO['start_time'];
						$array[$key]['end_date'] 		= $PRO['end_date'];
						$array[$key]['end_time'] 		= $PRO['end_time'];

						$QTY 		 = (!empty($PRO['qty'])) ? $PRO['qty'] : 0;
						$TOTAL_QTY  += $QTY;
					}
					foreach($PRODUCT_ARR AS $RES => $RAW){
						$QTY_1 		 = (!empty($RAW['qty'])) ? $RAW['qty'] : 0;
						$PERCENT 	 = ($QTY_1 > 0) ? ($QTY_1 / $TOTAL_QTY ) * 100 : 0;
						$PRODUCT_ARR[$RES]['qty_percent'] = _FormatNumberV2($PERCENT);
					}
				}
				$array[$key]['shift_data'] 			= $PRODUCT_ARR;
				$array[$key]['TOTAL_QTY'] 			= _FormatNumberV2($TOTAL_QTY);
				$array[$key]['TOTAL_INWARD_QTY'] 	= _FormatNumberV2($TOTAL_INWARD_QTY);
				$TOTAL_ALL_QTY += $TOTAL_QTY;
			}
		}
		return $array;
	}

	/*
	Use 	: Get Total 
	Author 	: Axay Shah
	Date 	: 07 April 2020
	*/
	public static function GetProcessTime($timing_id){
		$ProcessTbl = new ShiftRunningHoursMaster();
		$self 		= (new static)->getTable();
		$TIMING 	= self::select(
			\DB::raw("DATE_FORMAT($self.start_time,'%Y-%m-%d') as start_date"), 
						    \DB::raw("TIMEDIFF(t2.end_time,t2.start_time) AS TotalTime"),
						    \DB::raw("SEC_TO_TIME(SUM(TIME_TO_SEC(t2.end_time) - TIME_TO_SEC(t2.start_time))) AS timediff"),
							\DB::raw("SEC_TO_TIME(TIME_TO_SEC($self.end_time) - TIME_TO_SEC($self.start_time)) AS shift_total_time"),
			\DB::raw("SEC_TO_TIME(TIME_TO_SEC($self.end_time) - TIME_TO_SEC($self.start_time) - SUM(TIME_TO_SEC(t2.end_time) - TIME_TO_SEC(t2.start_time))) as TOTAL_PROCESS_TIME")
		)
		->join($ProcessTbl->getTable()." as t2","$self.id","=","t2.shift_timing_id")
		->WHERE("t2.shift_timing_id",$timing_id)->groupBy("start_date");
		// LiveServices::toSqlWithBinding($TIMING);
		$DATA = $TIMING->get()->toArray();
		// dd($DATA);
		$TOTAL_PROCESS_TIME = (!empty($DATA)) ? $DATA['0']['TOTAL_PROCESS_TIME'] : "";
		return $TOTAL_PROCESS_TIME;
	}
	
	/*
	Use 	: Get Total qty of define date shift wise
	Author 	: Axay Shah
	Date 	: 17 April 2020
	*/
	public static function GetTotalInwardQty($mrf_id,$start_date,$shift_id){
		// InwardSegregationMaster::where
	}

	public static function CreateMrfShiftByCron($date = ""){
		$date = (!empty($date)) ?  date("Y-m-d",strtotime($date)) : date("Y-m-d");
		$LIST = MrfShiftTimingMaster::where("status",1)->orderBy("id","ASC")->get()->toArray();
		if(!empty($LIST)){
			foreach($LIST as $RAW){
				$start_time 	= date("H",strtotime($RAW['start_time']));
				$end_time 		= date("H",strtotime($RAW['end_time']));
				$currentDate 	= $date." ".$start_time;
				$endDate 		= $date;
				$nextDate 		= $endDate." ".$end_time;
				if($start_time > $end_time){
					$tomorrow 			= date("Y-m-d", strtotime($date." +1 day"));
					$nextDate 			= $tomorrow." ".$end_time;
					$endDate 			= $tomorrow;
				}
				$id = self::addShift($date,$endDate,$RAW['start_time'],$RAW['end_time'],$RAW['mrf_id'],$RAW['shift_id']);
				echo "shift Added";
			}
		}
	}

}
