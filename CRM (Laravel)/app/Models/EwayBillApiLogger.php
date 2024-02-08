<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EwayBillApiLogger extends Model
{
    protected 	$table 		= 'eway_bill_api_logger';
    protected   $primaryKey =   'id'; // or null
    protected   $guarded    =   ['id'];
    public      $timestamps =   true;
    
    public static function AddEwayBillLogger($request){
        $ID                 = 0;
        $LOG                = new self();
        $LOG->ip            = $request->ip();
        $LOG->marchant_id   = (isset($request->mearchant_key) && !empty($request->mearchant_key)) ? $request->mearchant_key : '';
        $LOG->input         = (!empty($request->all())) ? json_encode($request->all()) : "";
        $LOG->created_at    = date("Y-m-d H:i:s");
        if($LOG->save()){
            $ID =  $LOG->id;
        }
        return $ID;
    }
    
}
