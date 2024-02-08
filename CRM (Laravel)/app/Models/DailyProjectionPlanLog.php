<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use DB;
use Log;

class DailyProjectionPlanLog extends Model
{
	protected 	$table 		= 'wm_daily_projection_plan_log';
	protected 	$guarded 	= ['id'];
	public      $timestamps = true;

	/*
	Use     : Save Approved Projection Plans
	Author  : Kalpak Prajapati
	Date    : 30 Aug 2022
	*/
	public static function saveRecord($wm_daily_projection_plan_id=0)
	{
		$NewRecord 								= new self;
		$NewRecord->wm_daily_projection_plan_id = $wm_daily_projection_plan_id;
		$NewRecord->processed 					= 0;
		$NewRecord->save();
	}
}