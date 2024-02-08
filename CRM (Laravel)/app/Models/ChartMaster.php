<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartMaster extends Model
{
    protected 	$table              = 'chart_master';
    protected 	$guarded            = ['id'];
    protected 	$primaryKey         = 'id'; // or null

    public function ChartProperty(){
    	return $this->hasMany(ChartPropertyMaster::class,'chart_id');
    }
    
    /*
	Use 	: Get Chart Listing
	Author 	: Axay Shah
	Date 	: 31 July 2019
	*/
	public static function ListChart($request){
		$result = array();
		$data = self::select("chart_master.*","chart_property_master.chart_id","chart_property_master.id as chart_prpo_id","chart_property_master.chart_type")->join("chart_property_master","chart_master.id","=","chart_property_master.chart_id")
		->where("chart_master.status","A")
		->where('chart_property_master.user_id',Auth()->user()->adminuserid)
		->orderBy('chart_property_master.id','DESC')
		->get();
		foreach($data as $key=>$value){
			$data[$key]['chart_property'] = ChartPropertyMaster::where("id",$value->chart_prpo_id)->get();
		}
		return $data;
	}

	/*
	Use 	: List Default Chart
	Author 	: Axay Shah
	Date 	: 13 Aug 2019
	*/
	public static function GetDefaultChart(){
		return $data = self::where("status","A")->where("is_custom",0)->orderBy('id','ASC')->get();
	}
}
