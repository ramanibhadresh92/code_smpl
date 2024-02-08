<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
class QuickLinksAccessLog extends Model
{
	//
	protected 	$table 		=	'quick_links_access_log';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	false;
	
	/*
	Use 	: Get Latest Access Log of Menu User Wise
	Author 	: Axay Shah
	Date 	: 16 May 2022
	*/
	public static function GetAccessLogByUser($userId){
		$data = self::select(
						\DB::raw("count(quick_links_access_log.id) as cnt"),
						"admintransaction.menutitle",
						"admintransaction.trnid",
						"quick_links_access_log.menu_url")
		->join("admintransaction","quick_links_access_log.trn_id","=","admintransaction.trnid")
		->where("user_id",$userId)
		->whereNotNull("menu_url")
		->where(["admintransaction.showtrnflg"=>"Y","admintransaction.insubmenu"=>"Y"])
		->groupBy("quick_links_access_log.trn_id")
		->orderBy("cnt","DESC")
		->limit(10)->get()->toArray();
		return $data;
	}
	
}
