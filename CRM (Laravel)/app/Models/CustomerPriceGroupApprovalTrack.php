<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPriceGroupApprovalTrack extends Model
{
    protected 	$table 		=	'customer_price_group_approval_track';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    public 		$timestamps = 	true;

    /*
	Use 	:  Track Version of customer Price Group Approval table
	Author 	:  Axay Shah 
	Date 	:  18 June,2019
	*/
    public static function AddPriceGroupApprovalTrack($priceGroupId,$customerId){
    	$Id 	= 0;
    	$Track 	= new self();
    	$Track->price_group_id 	= $priceGroupId;
    	$Track->customer_id 	= $customerId;
    	$Track->created_by 		= Auth()->user()->adminuserid;
    	if($Track->save()){
    		$Id = $Track->id;
    	}
    	return $Id;
    }

}
