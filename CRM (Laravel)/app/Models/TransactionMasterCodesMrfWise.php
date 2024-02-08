<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionMasterCodesMrfWise extends Model
{
    //
    protected 	$table 		=	'transaction_master_codes_mrf_wise';
    protected 	$primaryKey =	'id'; // or null
    public      $timestamps =   false;

    /*
    Use     : Get Last Code Of Transaction Master
    Author  : Axay Shah
    Date    : 24 March,2021
    */
    public static function GetLastTrnCode($mrf_id,$TrnType){
        $data = self::select("*",\DB::raw("CONCAT(group_no,prefix) as group_prefix"))->where("prefix_code",$TrnType)->where('mrf_id',$mrf_id)->first();
        return $data;
    }

    /*
    Use     : Update Code of transaction master code in table
    Author  : Axay Shah
    Date    : 24 March,2021
    */
    public static function UpdateTrnCode($mrf_id,$TrnType,$code_no){
        $data = self::where("prefix_code",$TrnType)->where('mrf_id',$mrf_id)->first();
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
    Date    : 24 March,2021
    */
    public static function GetTrnType($Type=0){
        $TrnType = 0;
        if($Type > 0){
            switch ($Type) {
                case RECYCLEBLE_TYPE:
                    $TrnType = RECYCLABLE_TYPE_TRANS ;
                    break;
                case NON_RECYCLEBLE_TYPE:
                    $TrnType = NON_RECYCLABLE_TYPE_TRANS;
                    break;
                default:
                    $TrnType;
                    break;
            }
        }
        return $TrnType;
    }
}
