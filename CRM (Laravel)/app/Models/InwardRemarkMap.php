<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\Parameter;
class InwardRemarkMap extends Model implements Auditable
{
    protected 	$table 		=	'inward_remark_map';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;

	public static function GetRemark($InwardId = 0){
		$data 			= array();
		$parameter  	= new Parameter();
		$self 			= (new static)->getTable();
		$remark 		= array();
		$remarkName		= array();
		$list = self::select("$self.*","PARAM.para_value as remark_name")
		->join($parameter->getTable()." as PARAM","$self.remark_id","=","PARAM.para_id")
		->where("$self.inward_plant_id",$InwardId)
		->get()->toArray();
		if(!empty($list)){
			foreach($list as $raw){
				array_push($remark,(int)$raw['remark_id']);
				array_push($remarkName,$raw['remark_name']);
			}
		}
		$data['remark_id'] 		=  $remark;
		$data['remark_name'] 	=  $remarkName;
		return $data;
	}

	
	
	public static function GetRemarkCommaSeprated($InwardId = 0){
		$data 			= array();
		$parameter  	= new Parameter();
		$self 			= (new static)->getTable();
		$remark 		= array();
		$remarkName		= array();
		$list = self::select(\DB::raw("GROUP_CONCAT(PARAM.para_value) as remark_name"))
		->join($parameter->getTable()." as PARAM","$self.remark_id","=","PARAM.para_id")
		->where("$self.inward_plant_id",$InwardId)
		->groupBy("$self.inward_plant_id")
		->get()
		->toArray();
		return $list;
	}

}
