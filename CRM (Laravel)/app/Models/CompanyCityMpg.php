<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
class CompanyCityMpg extends Authenticatable
{
    //
    protected 	$table 		=	'company_city_mpg';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    /**
     * removeCompanyCity
     *
     * Behaviour : Public
     *
     * @param : pass company_id
     *
     * @defination : Remove all record for perticular company
     **/
    public static function removeCompanyCity($company_id){
    	return self::where('company_id',$company_id)->delete();
    }
	/**
     * addCompanyCity
     *
     * Behaviour : Public
     *
     * @param : pass company_id and city_id
     *
     * @defination : Add city for perticular company
     **/
    public static function addCompanyCity($company_id,$cityId){
        self::create(["company_id"=>$company_id,"city_id"=>$cityId]);
    }
    /**
     *  Use      : get company city and state list
     *  Author   : Axay Shah
     *  Date     : 4 Sep,2018
     **/
    public static function getCompanyCityState(){
        return  self::select('company_city_mpg.city_id','lm.location_id','lm.city','lm.state_id','sm.state_id','sm.state_name as state','sm.country_id')
                ->join('location_master as lm', 'company_city_mpg.city_id', '=', 'lm.location_id')
                ->join('state_master as sm', 'sm.state_id', '=', 'lm.state_id')
                ->where('company_city_mpg.company_id', '=', Auth()->user()->company_id)
                ->orderBy('lm.city','ASC')
                ->get();
    }

    /*
        Use     : Delete company all
        Author  : Axay Shah
        Date    : 12 Sep,2018
    */
    public static function deleteCompanyAllCity($companyId){
        self::where("company_id",$companyId)->delete();
    }


    /*
        Use     : Delete company perticular
        Author  : Axay Shah
        Date    : 12 Sep,2018
    */
    public static function deleteCompanyCity($companyId,$cityId){
        self::where("company_id",$companyId)->where("city_id",$cityId)->delete();
    }

}
