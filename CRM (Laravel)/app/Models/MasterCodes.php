<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterCodes extends Model
{
    //
    protected 	$table 		=	'master_codes';
    //protected 	$guarded 	=	['adminuserid'];
    protected 	$primaryKey =	'code_id'; // or null
    /**
     * getLastCompanyCode
     *
     * Behaviour : Public
     *
     * @param : 
     *
     * @defination : Method is use fetch last company code from master code tables.
     **/
    public static function getLastCompanyCode()
    {
    	$company_code=self::select('*')->where('name','COMPANY')->first();
        return $company_code;
    }
     /**
     * updateMasterCode
     *
     * Behaviour : Public
     *
     * @param : 
     *
     * @defination : Method is use fetch update code value for perticular code name.
     **/
    public static function updateMasterCode($name,$value)
    {
        \DB::table('master_codes')->where('name', $name)->update(['code_value' => $value]);
    }

    /*
    USE     : get daynamic code value
    Author  : Axay Shah
    Date    : 05 Oct,2018
    */
    public static function getMasterCode($codeValue){
        return self::where('name',$codeValue)->first();
    }
}
