<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CCOFDetails extends Authenticatable
{
	protected $table = 'ccof_details';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded 	= ['id'];
	protected $fillable = ['ccof_master_id','ccof_data_json'];

	/*
	Use 	: saveRecord
	Date 	: 16 Mar 2022
	Author 	: Kalpak Prajapati
	*/
	public static function saveRecord($request,$ccof_master_id=0)
	{
		$data 			= array();
		if(!is_array($request->ccof_data_json)){
			$ccof_data_json = (isset($request->ccof_data_json) && (!empty($request->ccof_data_json)) ? $request->ccof_data_json : json_encode(array()));
		} else {
			$ccof_data_json = (isset($request->ccof_data_json) && (!empty($request->ccof_data_json)) ? json_encode($request->ccof_data_json) : json_encode(array()));
		}
		if (!empty($ccof_master_id)) {
			$ExistingRow 				= self::where("ccof_master_id",$ccof_master_id)->first();
			if (isset($ExistingRow->id) && !empty($ExistingRow->id)) {
				$ExistingRow->ccof_data_json = $ccof_data_json;
				$ExistingRow->save();
			} else {
				$NewRecord 					= new self;
				$NewRecord->ccof_master_id 	= $ccof_master_id;
				$NewRecord->ccof_data_json 	= $ccof_data_json;
				$NewRecord->save();
			}
		}
		return true;
	}
}

