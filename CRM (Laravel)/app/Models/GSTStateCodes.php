<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StateMaster;
class GSTStateCodes extends Model
{
    protected 	$table 		=	'GST_STATE_CODES';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    public 		$timestamps = 	false;

    /*
	Use 	: Get GST code from state id
	Author 	: Axay Shah
	Date 	: 12 June,2019
	*/
    public static function GetGSTCodeByStateId($stateId=0){
    	$StateMaster 	= new StateMaster();
    	$State 			= $StateMaster->getTable();
    	$stateData 		= self::leftjoin($State,"GST_STATE_CODES.state_name","=","$State.state_name")
    	->where("$State.state_id",$stateId)
    	->first();
    	return $stateData;
    }


     /*
    Use     : Get GST state code
    Author  : Axay Shah
    Date    : 03 July,2019
    */
    public static function GetGSTStateCode(){
        $stateData      = self::all();
        return $stateData;
    }

     /*
    Use     : Get By ID
    Author  : Axay Shah
    Date    : 10 April,2020
    */
    public static function GetById($id){
        $stateData      = self::find($id);
        return $stateData;
    }
}
