<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CompanyMaster;
class ModuleMaster extends Model
{
    //
    protected 	$table 		=	'module_master';
    //protected 	$guarded 	=	['adminuserid'];
    protected 	$primaryKey =	'module_id'; // or null
    /**
     * getTrnidFromPageurl
     *
     * Behaviour : Public
     *
     * @param : passed request api url in pageurl   :
     *
     * @defination : Method is use fetch trnid from admintransaction table where pageurl equal to passed pageurl
     **/
    public static function getAllModules()
    {
        $moudle_data        = array();
        if(Auth()->user()->is_superadmin == 0 && Auth()->user()->company_id <= 0){
            $moudle_data    = self::select('*')->get();
        }else{
            $moduleList     = CompanyMaster::find(Auth()->user()->company_id)->value('module_ids');
            
            if(!empty($moduleList)){
                $serilizeModule = unserialize($moduleList);
                $moudle_data    = self::select('*')->whereIn('module_id',$serilizeModule)->get();
            }
        }
    	
        return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_FOUND'),"data"=>$moudle_data]);
    }
}
