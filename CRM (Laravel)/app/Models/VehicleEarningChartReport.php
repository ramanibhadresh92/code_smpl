<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\VehicleMaster;
use App\Models\Appoinment;
use DB;
use App\Facades\LiveServices;
class VehicleEarningChartReport extends Model
{
    protected 	$table 		=	'vehicle_earning_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;

	protected $casts = [
        'total_qty' => 'float',
        'total_amount' => 'float',
    ];

    public function scopeActive($query, $value)
    {
        return $query->where('active', $value);
    }

    /*
	Use 	: Vehicle Earning Chart
	Author 	: Axay Shah
	Date 	: 24 Dec,2019
	*/
	public static function VehicleEarningChart($vehicleId = '',$startDate = '',$endDate=''){
		$res 			= array();
		$CityId 		= GetBaseLocationCity();
		$self 			= (new static)->getTable();
		$vehicle 		= new VehicleMaster();
		$Weight 		= array();
		$Price 			= array();
		$avg_qty 		= 0;
		$avg_price 		= 0;
		$total_price 	= 0;
		$total_qty 		= 0;
		$data 			= self::select(DB::raw("SUM(total_qty) as total_qty"),DB::raw("SUM(total_amount) as total_amount"),"$self.vehicle_id","$self.earning_date","$self.vehicle_type","V.vehicle_number")
		->join($vehicle->getTable()." as V","$self.vehicle_id","=","V.vehicle_id")
		->whereIn("V.city_id",$CityId)
		->where("$self.company_id",Auth()->user()->company_id)
		->whereBetween("$self.earning_date",[$startDate,$endDate]);
		if(!empty($vehicleId)){
			$data->where("$self.vehicle_id",$vehicleId);
		}
		$result = $data->groupBy("$self.earning_date")->get()->toArray();
		$Days 	= count($result);
		if(!empty($result)){
			foreach($result as $key => $value){
				$total_price 	= $total_price + _FormatNumberV2($value['total_amount']);
				$total_qty 		= $total_qty + _FormatNumberV2($value['total_qty']);
				$result[$key]['total_qty'] 		= (float)_FormatNumberV2($value['total_qty']);
				$result[$key]['total_amount'] 	= (float)_FormatNumberV2($value['total_amount']);
			}
		}
		$avg_qty 				= ($total_qty > 0 ) 	? 	(float)_FormatNumberV2($total_qty / $Days) 	: 0;
		$avg_price 				= ($total_qty > 0 )   	?	(float)_FormatNumberV2($total_price / $Days): 0;
		$res['result'] 			= $result;
		$res['avg_qty'] 		= $avg_qty;
		$res['avg_price'] 		= $avg_price;
		return $res;
	}


	public static function VehicleEarningMonthWiseChart($vehicleId = '',$startDate = '',$endDate=''){
		$res 			= array();
		$CityId 		= GetBaseLocationCity();
		$self 			= (new static)->getTable();
		$vehicle 		= new VehicleMaster();
		$Weight 		= array();
		$Price 			= array();
		$avg_qty 		= 0;
		$avg_price 		= 0;
		$total_price 	= 0;
		$total_qty 		= 0;
		$CityId 		= (!empty($CityId)) ? implode(",",$CityId) : Auth()->user()->city_id;
		$VehicleQry 	= (!empty($vehicleId)) ? " AND vehicle_earning_master.vehicle_id = ".$vehicleId : "";

		$query = 	"SELECT
					SUM(total_qty) as total_qty,
					SUM(total_amount) as total_amount,
					vehicle_earning_master.vehicle_id,
					vehicle_earning_master.earning_date,
					vehicle_earning_master.vehicle_type,
					V.vehicle_number,
					DATE_FORMAT(vehicle_earning_master.earning_date,'%m') as month
					FROM vehicle_earning_master
					INNER JOIN vehicle_master as V
					ON vehicle_earning_master.vehicle_id = V.vehicle_id
					WHERE V.city_id in ($CityId) ".$VehicleQry." and vehicle_earning_master.company_id = ".Auth()->user()->company_id." and
  					vehicle_earning_master.earning_date between '".$startDate."' and '".$endDate."'
  		 			GROUP BY month";
  		$result = DB::select($query);
		$Days 	= count($result);
		if(!empty($result)){

			foreach($result as $key => $value){
				$total_price 	= $total_price + _FormatNumberV2($value->total_amount);
				$total_qty 		= $total_qty + _FormatNumberV2($value->total_qty);
				$result[$key]->total_qty 		= (float)_FormatNumberV2($value->total_qty);
				$result[$key]->total_amount 	= (float)_FormatNumberV2($value->total_amount);
				$result[$key]->month_name		= $month_name = date("F", mktime(0, 0, 0, $value->month, 10));
			}
		}
		$avg_qty 				= ($total_qty > 0 ) 	? 	(float)_FormatNumberV2($total_qty / $Days) 	: 0;
		$avg_price 				= ($total_qty > 0 )   	?	(float)_FormatNumberV2($total_price / $Days): 0;
		$res['result'] 			= $result;
		$res['avg_qty'] 		= $avg_qty;
		$res['avg_price'] 		= $avg_price;
		return $res;
	}


	/*
	Use 	: Vehicle % in total Earning
	Author 	: Axay Shah
	Date 	: 27 Dec,2019
	*/
	public static function VehicleTotalEarningInPercent($vehicleId = '',$startDate = '',$endDate=''){
		$res 			= array();
		$CityId 		= GetBaseLocationCity();
		$self 			= (new static)->getTable();
		$vehicle 		= new VehicleMaster();
		$Weight 		= array();
		$Price 			= array();
		$avg_qty 		= 0;
		$avg_price 		= 0;
		$total_price 	= 0;
		$total_qty 		= 0;
		$TOTAL_AMOUNT 	= 0;
		$TOTAL_QTY 		= 0;
		$TotalData 		= self::select(DB::raw("SUM(total_qty) as total_qty"),DB::raw("SUM(total_amount) as total_amount"),"$self.vehicle_id","$self.earning_date","$self.vehicle_type","V.vehicle_number")
		->join($vehicle->getTable()." as V","$self.vehicle_id","=","V.vehicle_id")
		->whereIn("V.city_id",$CityId)
		->where("$self.company_id",Auth()->user()->company_id)
		->whereBetween("$self.earning_date",[$startDate,$endDate])
		->get()
		->toArray();
		if(!empty($TotalData)){
			foreach($TotalData as $Total){
				$TOTAL_AMOUNT 	= $Total['total_amount'];
				$TOTAL_QTY 		= $Total['total_qty'];
			}
		}

		$data 			= self::select(DB::raw("SUM(total_qty) as total_qty"),DB::raw("SUM(total_amount) as total_amount"),"$self.vehicle_id","$self.earning_date","$self.vehicle_type","V.vehicle_number")
		->join($vehicle->getTable()." as V","$self.vehicle_id","=","V.vehicle_id")
		->whereIn("V.city_id",$CityId)
		->where("$self.company_id",Auth()->user()->company_id)
		->whereBetween("$self.earning_date",[$startDate,$endDate]);
		if(!empty($vehicleId)){
			$data->where("$self.vehicle_id",$vehicleId);
		}
		$result = $data->groupBy("$self.vehicle_id")
		->get()
		->toArray();

		if(!empty($result)){
			foreach($result as $key => $value){
				$total_price 		= _FormatNumberV2($value['total_amount']);
				$total_qty 			= _FormatNumberV2($value['total_qty']);

				$AmountInpercent 	= ($total_price * 100) / $TOTAL_AMOUNT;
				$QtyInPercent 		= ($total_qty * 100) / $TOTAL_QTY;


				$result[$key]['total_qty'] 		= ($QtyInPercent > 0 ) 		?(float)_FormatNumberV2($QtyInPercent)		: 0;
				$result[$key]['total_amount'] 	= ($AmountInpercent > 0 ) 	?(float)_FormatNumberV2($AmountInpercent)	: 0;
			}
		}

		$res['result'] 			= $result;
		$res['avg_qty'] 		= $avg_qty;
		$res['avg_price'] 		= $avg_price;
		return $res;
	}

	/*
	Use 	: Vehicle Attendance in percent
	Author 	: Axay Shah
	Date 	: 01 Jan,2020
	*/
	public static function VehicleAttendanceInPercent($vehicleId = '',$startDate = '',$endDate='')
	{
		$res 			= array();
		$CityId 		= GetBaseLocationCity();
		$Appoinment 	= new Appoinment();
		$self 			= $Appoinment->getTable();
		$vehicle 		= new VehicleMaster();
		$Weight 		= array();
		$Price 			= array();
		$Attendance 	= 0;
		$data 			= Appoinment::select(DB::raw('COUNT(0) as count_raw'),
											DB::raw('DATE_FORMAT("$self.app_date_time","%Y-%m-%d") as app_date'),
											"$self.vehicle_id",
											"V.vehicle_number")
		->join($vehicle->getTable()." as V","$self.vehicle_id","=","V.vehicle_id")
		->whereIn("$self.city_id",$CityId)
		->where("$self.company_id",Auth()->user()->company_id)
		->where("$self.para_status_id",APPOINTMENT_COMPLETED)
		->whereBetween("$self.app_date_time",[$startDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME]);
		if(!empty($vehicleId)) {
			$data->where("$self.vehicle_id",$vehicleId);
		}
		$result = $data->groupBy("$self.vehicle_id")->groupBy("app_date")->get()->toArray();
		$Days 	= date("d",strtotime($endDate));
		if(!empty($result)){
			foreach($result as $key => $value) {
				$Attendance 				= ($value['count_raw'] > 0) ? ($value['count_raw'] * 100) / $Days : 0;
				$Attendance 				= ($Attendance > 100)?100:$Attendance;
				$result[$key]['attendance'] = _FormatNumberV2($Attendance);
			}
		}
		$res['result'] 			= $result;
		return $res;
	}
}