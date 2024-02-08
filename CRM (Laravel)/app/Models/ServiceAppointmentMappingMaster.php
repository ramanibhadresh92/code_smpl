<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\ServiceAppointmentMappingMaster;
use App\Facades\LiveServices;
use Validator;
use DB;
use JWTAuth;
use Log;
class ServiceAppointmentMappingMaster extends Model
{
	protected   $table          = 'service_appointment_mapping_master';
	public      $timestamps     = false;
	protected   $primaryKey     = 'id'; 
	protected   $guarded        = ['id']; 

	public static function saveData($request, $service_id = 0)
	{
		$id           = 0;
		$mapping_list = "";
		if(isset($request->mapping_list) && !empty($request->mapping_list)){
			if(is_array($request->product_list)){
				$mapping_list = $request->mapping_list;	
			}else{
				if(is_object($request->mapping_list)){
					$mapping_list = json_encode($request->mapping_list);
				}else{
					$mapping_list = $request->mapping_list;
				}
					$mapping_list = json_decode($mapping_list,true);								
			}
		}
		if(!empty($mapping_list) && is_array($mapping_list))
		{
			foreach ($mapping_list as $key => $value)
	 		{
				$appointment_id 		= (isset($value["appointment_id"]) && !empty($value["appointment_id"])) ? $value["appointment_id"] : 0;	
				$data 					= new self();
				$data->service_id 		= $service_id;
				$data->appointment_id 	= $appointment_id;
				$data->created_at 		= date('Y-m-d H:i:s');
				if($data->save()) {
					$id =  $data->id;
				}
			}
		}
		return $id;
	} 
}
