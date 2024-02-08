<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionMasterCodes extends Model
{
    //
    protected 	$table 		=	'transaction_master_codes';
    protected 	$primaryKey =	'id'; // or null
    public      $timestamps =   false;
   
    /*
    Use     : Get Last Code Of Transaction Master
    Author  : Axay Shah
    Date    : 09 April,2020
    */
    public static function GetLastTrnCode($baseLocationId,$TrnType){
        $data = self::where("prefix_code",$TrnType)->where('base_location',$baseLocationId)->first();
        return $data;
    }

    /*
    Use     : Update Code of transaction master code table
    Author  : Axay Shah
    Date    : 09 April,2020
    */
    public static function UpdateTrnCode($baseLocationId,$TrnType,$code_no){
        $data = self::where("prefix_code",$TrnType)->where('base_location',$baseLocationId)->first();
        if($data){
            self::where("group_no",$data->group_no)
                ->where("prefix_code",$TrnType)
                ->update(["code_value"=>$code_no]);
            return true;
        }
        return false;
    }

    /*
    Use     : Get Trn Type prifix according to type
    Author  : Axay Shah
    Date    : 23 April,2020
    */
    public static function GetTrnType($Type=0){
        $TrnType = 0;
        if($Type > 0){
            switch ($Type) {
                case PARA_CORPORATE_SALES:
                    $TrnType = CORPORATE_SALES_TRANS ;
                    break;
                case PARA_RETAIL_SALES:
                    $TrnType = RETAIL_SALES_TRANS;
                    break;
                case PARA_RDF:
                    $TrnType = RDF_TRANS;
                    break;
                default:
                    $TrnType;
                    break;
            }
        }
        return $TrnType;
    }
}
