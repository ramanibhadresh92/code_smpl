<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class JobWorkerMrfMapping extends Model
{
	protected $table 		=	'job_worker_mrf_mapping';
	protected $primaryKey	=	'id';
	public 	  $timestamps = 	true;
	/*
	Use 	: Add MRF wise Party Mapping
	Method 	: Axay Shah
	Date 	: 26 May 2020
	*/
	public static function CreateMrfPartyMapping($MRF_ID,$PARTY_ID){
		$ADD = new self();
		$ADD->mrf_id 		= $MRF_ID;
		$ADD->jobworker_id 	= $PARTY_ID;
		$ADD->created_by 	= Auth()->user()->adminuserid;
		$ADD->save();
	}
}
