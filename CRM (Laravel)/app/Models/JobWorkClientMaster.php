<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class JobWorkClientMaster extends Model
{
    protected $table 		=	'jobwork_client_master';
    protected $primaryKey	=	'id';

    /*
    Use 	:	Add JobWork Client Details
    Author 	:	Upasana
    Date 	:	4/2/2020
    */

    public static function AddClientDetails($clientname = "",$status = 1)
    {
    	$result					= 	new JobWorkClientMaster();
    	$result->client_name 	=	$clientname;
    	$result->status 		=	$status;
    	$result->created_by 	=	(isset(Auth::user()->adminuserid) && !empty(Auth::user()->adminuserid) ? Auth::user()->adminuserid : 0);
    	$result->company_id 	=	(isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id) ? Auth()->user()->company_id: "");
		if($result->save())
		{
			$id = $result->id;
		}
    	
    	return $result;
    }

	/*
    Use 	:	JobWork Client Details List
    Author 	:	Upasana
    Date 	:	4/2/2020
    */    

    public static function ClientList()
    {
    	$result 	=	JobWorkClientMaster::where('status',1)->get();
    	return $result;
    }
}
