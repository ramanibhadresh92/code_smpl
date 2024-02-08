<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StateMaster;
class RtoStateCodes extends Model
{
    protected 	$table 		=	'RTO_STATE_CODES';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    public 		$timestamps = 	false;

    /*
	Use 	: RTO state code list
	Author 	: Axay Shah
	Date 	: 01 Feb,2020
	*/
    public static function GetRtoStateCodeData(){
    	$data = self::all();
    	return $data;
    }



}
