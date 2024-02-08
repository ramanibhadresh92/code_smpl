<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmBatchMaster;
use App\Models\HelperAttendance;
use App\Facades\LiveServices;
class RatingMaster extends Model implements Auditable
{
    protected 	$table 		=	'rating_master';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public      $timestamps =   true;
    use AuditableTrait;
    /*
	Use 	: List Quality Rating List DROPDOWN
	Author 	: Axay Shah
	Date 	: 14 Feb 2020
    */
	public static function GetRatingMasterList(){
		return self::where("status",STATUS_ACTIVE)->get()->makeHidden(['created_at','updated_at']);
	}
	
	
	/*
	Use 	: Get Avg Rating of Driver
	Author 	: Axay Shah
	Date 	: 26 Feb 2020
    */
	public static function GetAvgRating($userId,$startDate,$endDate){

		$MONTH_DAYS 	=  date("t",strtotime($startDate));
		$TOTAL_RATING 	=  WmBatchMaster::WhereBetween('created_date',[$startDate,$endDate])->where("collection_by",$userId)->sum('rating');
		$AVG_RATING		=  0;	
		$INCENTIVE_AMT  =  0;
		
		if($TOTAL_RATING > 0 && $MONTH_DAYS > 0){
			$AVG_RATING 	=  round((int)$TOTAL_RATING / $MONTH_DAYS);
			$AVG_RATING 	=  ($AVG_RATING >= 5) ? 5 : $AVG_RATING;
			$INCENTIVE_AMT 	=  self::where("rating_value",$AVG_RATING)->where("status",1)->where("company_id",Auth()->user()->company_id)->value('amount');
			
		}
		$data['avg_rating'] 	= (int)$AVG_RATING;
		$data['rating_amount'] 	= (float)_FormatNumberV2($INCENTIVE_AMT);
		return $data;
		
	}

	/*
	Use 	: Get Avg Rating of Helper
	Author 	: Axay Shah
	Date 	: 03 Mar 2020
    */
	public static function GetHelperAvgRating($userId,$startDate,$endDate,$type="H"){

		$MONTH_DAYS 	=  date("t",strtotime($startDate));
		$BatchIDs 		=  HelperAttendance::WhereBetween('attendance_date',[$startDate,$endDate])->where("adminuserid",$userId)->where("type",$type)->pluck('batch_id');
		$TOTAL_RATING 	= 0;
		if(!empty($BatchIDs)){
			$TOTAL_RATING 	=  WmBatchMaster::WhereIn('batch_id',$BatchIDs)->sum('rating');	
		}
		
		$AVG_RATING		=  0;	
		$INCENTIVE_AMT  =  0;
		
		if($TOTAL_RATING > 0 && $MONTH_DAYS > 0){
			$AVG_RATING 	=  round((int)$TOTAL_RATING / $MONTH_DAYS);
			$AVG_RATING 	=  ($AVG_RATING >= 5) ? 5 : $AVG_RATING;
			$INCENTIVE_AMT 	=  self::where("rating_value",$AVG_RATING)->where("status",1)->where("company_id",Auth()->user()->company_id)->value('amount');
			
		}
		$data['avg_rating'] 	= (int)$AVG_RATING;
		$data['rating_amount'] 	= (float)_FormatNumberV2($INCENTIVE_AMT);
		return $data;
		
	}

}
