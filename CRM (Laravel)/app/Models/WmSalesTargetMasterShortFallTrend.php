<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WmSalesTargetMasterShortFallTrend extends Model
{
	protected 	$table 		= 'wm_sales_target_short_fall_trend';
	/*
	Use 	: save Short Fall Detail
	Author 	: Kalpak Prajapati
	Date 	: 08 March 2022
	*/
	public static function saveShortFallDetail($MRF_ID=0,$TODAY="",$MRF_PER_DAY_TARGET=0,$BASELOCATION=0,$PAID=0)
	{
		$Count = self::select("id")->where('mrf_id',$MRF_ID)->where('date',$TODAY)->where('is_base_location',$BASELOCATION)->where('is_paid',$PAID)->count();
		if ($Count == 0) {
			$Add 					= new self();
			$Add->month 			= date("m",strtotime($TODAY));
			$Add->year 				= date("Y",strtotime($TODAY));
			$Add->date 				= $TODAY;
			$Add->mrf_id 			= $MRF_ID;
			$Add->is_base_location 	= $BASELOCATION;
			$Add->is_paid 			= $PAID;
			$Add->short_fall 		= round($MRF_PER_DAY_TARGET,2);
			$Add->save();
		}
	}
}