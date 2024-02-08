<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupRightsTransaction extends Model
{
    //
     //
    protected 	$table 		=	'grouprights_trn';
    protected 	$primaryKey =	'auto_id'; // or null
    protected 	$guarded 	=	['auto_id'];
    public 		$timestamps = 	false;

    /*
		Use 	:	Get user rights by group vise - 17 Aug,2018
		Author 	: 	Axay Shah
	*/
    public static function getTransectionIdByGroup($groupIds){
    	return self::whereIn('group_id',array($groupIds))->get();
    }



}
