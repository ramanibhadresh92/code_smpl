<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Log;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class VehicleDriverMappings extends Model implements Auditable
{
    protected 	$table 		= 'vehicle_driver_mapping';
    public $timestamps      = false;
    protected $primaryKey   = 'id';
    protected 	$guarded 	=	['id'];
    use AuditableTrait;
    
    
    public static function saveVehicleMapping($request){
        $userId = Auth()->user()->adminuserid;
        $date   = date("Y-m-d H:i:s");
        $flight = array();
        try{
            if(isset($request->vehicle_id) && !empty($request->vehicle_id)) {
                $driver = self::where("collection_by",$request->collection_by)->first();
                if($driver){
                    if($driver->collection_by != $request->vehicle_id){
                        $driver->delete();
                    }
                }
                    $flight = self::updateOrCreate(
                        ['vehicle_id' => $request->vehicle_id], ['collection_by' => $request->collection_by,"updated_by"=>$userId,"updated_date"=>$date]
                    );
                LR_Modules_Log_CompanyUserActionLog($request,$request->id);    
            }
            return $flight;
        }catch(\Exception $e){
            return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
        }
    }


    public static function getVehicleUnMappedUserList($request){
        $query =  array();
        $City   = GetBaseLocationCity();
		$CityId = 0;
		if(!empty($City)){
			$CityId = implode(",",$City);
		}
        if(isset($request->vehicle_id) && !empty($request->vehicle_id)){
			$collectionBy   = self::where(["vehicle_id"=>$request->vehicle_id])->select('collection_by')->first();
			(!empty($collectionBy)) ? $collect = $collectionBy->collection_by : $collect = 0;
			$COND           =  'and adminuserid NOT IN (SELECT collection_by FROM vehicle_driver_mapping WHERE collection_by !="'.$collect.'")'; 
			$query  =  DB::select('select user_id,full_name from view_adminuser_with_city_user_type WHERE ( group_code="'.CRU.'" OR group_code="'.FRU.'" OR group_code="'.GDU.'" OR group_code="'.CLFS.'" OR group_code="'.SUPV.'" OR group_code="'.CLAG.'") AND visible="1" AND status="A" and usercitys in ('.$CityId.') and company_id = '.Auth()->user()->company_id.'
            '.$COND.' ORDER BY firstname ASC');
		}
        return $query;
    }

    public static function getVehicleMappedCollectionBy($vehicle_id){
        return self::where('vehicle_id',$vehicle_id)->value('collection_by');
    }
    public static function getCollectionByMappedVehicle($collection_by){
        return self::where('collection_by',$collection_by)->value('vehicle_id');
    }
}
