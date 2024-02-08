<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Http\UploadedFile;
use Image,File;
use Log;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class VehicleDriverMapping extends Model implements Auditable
{
    protected 	$table 		=	'vehicle_driver_mapping';
    public $timestamps      = false;
   use AuditableTrait;

    public static function saveVehicleDriverMapping($request){
    print_r($request->toArray());
    exit;
        if(!empty($request->vehicle_id) && !empty($request->collection_by)) {
          
            $query 	= DB::select("INSERT INTO vehicle_driver_mapping (vehicle_id, collection_by) 
					VALUES (".$request->vehicle_id.", ".$request->collection_by.") 
					ON DUPLICATE KEY UPDATE vehicle_id = ".$request->vehicle_id.", collection_by = ".$request->collection_by);
            return $query;
            dd($query);
		}else{
            echo "asdfdsaf";
            exit;
        }
    }
    
    
    // public static function GetVehicleUnMappedUserList(){
        
    // }
}
