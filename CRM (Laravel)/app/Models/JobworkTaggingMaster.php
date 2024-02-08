<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobworkTaggingMaster extends Model
{
    protected $table 		= 'jobwork_tagging_master';
    protected $primaryKey 	= 'id';
  	public    $timestamps =   true;


	public static function InsertJobworkType($jobworkid=0,$jobwork_type_id=0)
	{
		$id    = 0; 
		$data  = new self();
		$data->jobwork_id	  	 = $jobworkid;
		$data->jobwork_type_id	 = $jobwork_type_id;
   		$data->created_by 		 = Auth()->user()->adminuserid;
   		if($data->save())
		{
			$id = $data->id ;
		}
		return $id;
	}
}
