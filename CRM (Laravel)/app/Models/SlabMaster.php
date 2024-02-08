<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SlabRateCardMaster;
use App\Facades\LiveServices;
use Validator;
use DB;
use JWTAuth;
use Log;
class SlabMaster extends Model
{
    protected 	$table 		    = 'slab_master';
    public      $timestamps     = false;
    protected   $primaryKey     = 'id'; 
    protected   $guarded        = ['id']; 

    public static function getSlabMasterList($request)
    {
       
        $Today          = date('Y-m-d');
        $SlabTbl        = (new static)->getTable();
        $result =   SlabMaster::select(					
					\DB::raw("$SlabTbl.id as id"),
					\DB::raw("$SlabTbl.slab_md5 as slab_md5"),
					\DB::raw("$SlabTbl.slab_name as slab_name"),
					\DB::raw("$SlabTbl.base_fee as base_fee"),
					\DB::raw("$SlabTbl.status as status"),
					\DB::raw("DATE_FORMAT($SlabTbl.created_at,'%d-%m-%Y') as created_from"),
					\DB::raw("DATE_FORMAT($SlabTbl.created_at,'%d-%m-%Y') as created_to"));
        
        if($request->has('id') && !empty($request->input('id')))
        {
            $result->where("$SlabTbl.id",$request->input('id'));
        }
         if($request->has('slab_name') && !empty($request->input('slab_name')))
        {
            $result->where("$SlabTbl.slab_name",$request->input('slab_name'));
        }

       // $bindings= LiveServices::toSqlWithBinding($result);
       // print_r($bindings); 
        $SlabMasterData     = $result->get();
        $SlabRateCardMaster = new SlabRateCardMaster();
        $SlabRateCard_Array = array();
        $SlabRateCardTbl    = $SlabRateCardMaster->getTable();  
        foreach($SlabMasterData as $SlabMasterKey => $SlabMasterValue ){
           $slabRate_result =    SlabRateCardMaster::leftjoin("company_product_master","slab_rate_card_master.product_id","=","company_product_master.id")
                        ->leftjoin("company_product_quality_parameter","company_product_master.id","=","company_product_quality_parameter.product_id")
                        ->select(
                            "slab_rate_card_master.product_id",
                            "slab_rate_card_master.min_qty",
                            "slab_rate_card_master.max_qty",
                            "slab_rate_card_master.extra_charge",
                            \DB::raw("CONCAT(company_product_master.name) as product_name")
                        )
                        ->where("slab_rate_card_master.status",1)
                        ->where("slab_rate_card_master.slab_id",$SlabMasterValue->id)
                        ->where("company_product_master.para_status_id",PRODUCT_STATUS_ACTIVE)
                        ->get();
            $SlabMasterData[$SlabMasterKey]['slab_data'] = $slabRate_result;  
        }
        return $SlabMasterData;  
    }
    public static function getSlabRateCardList($request)
    {
        
        //$LocationMaster = new LocationMaster();
        //$LocationTbl    = $LocationMaster->getTable();   
        $result = SlabMaster::select(                   
                    \DB::raw("$SlabTbl.id as id"),
                    \DB::raw("$SlabTbl.slab_md5 as slab_md5"),
                    \DB::raw("$SlabTbl.slab_name as slab_name"),
                    \DB::raw("$SlabTbl.base_fee as base_fee"),
                    \DB::raw("$SlabTbl.status as status"),
                    \DB::raw("DATE_FORMAT($SlabTbl.created_at,'%d-%m-%Y') as created_from"),
                    \DB::raw("DATE_FORMAT($SlabTbl.created_at,'%d-%m-%Y') as created_to"));
        
        if($request->has('id') && !empty($request->input('id')))
        {
            $result->where("$SlabTbl.id",$request->input('id'));
        }
         if($request->has('slab_name') && !empty($request->input('slab_name')))
        {
            $result->where("$SlabTbl.slab_name",$request->input('slab_name'));
        }
    }
    public static function CreateSlab($request)
    {}
    public static function UpdateSlab($request)
    {
        $msg        = trans('message.NO_RECORD_FOUND');
        try{
            DB::beginTransaction();
            $SlabMasterData = self::find($request->id);
            if($SlabMasterData){
                $SlabMasterData->slab_name  = (isset($request->slab_name) 	&& !empty($request->slab_name)) ? $request->slab_name   :  0;
                $SlabMasterData->base_fee   = (isset($request->base_fee)   	&& !empty($request->base_fee))  ? $request->base_fee   	:  0;
                $SlabMasterData->status     = (isset($request->status)    	&& !empty($request->status))    ? $request->status      :  0;
                $SlabMasterData->updated_at = date('Y-m-d H:i:s');
                if($SlabMasterData->save()){
                    $msg        = trans('message.RECORD_UPDATED');
                }
            }      
            DB::commit();
            return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$SlabMasterData]);
        } catch (\Exception $e) {
            DB::rollback();
            // return $e;
            return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>$e->getMessage(),"data" =>""]);
        }        
    }

    public static function getById($id)
    {
        $id             = (isset($id) && !empty($id)) ? $id : 0;
        $SlabMasterData =  self::find($id);
        if($SlabMasterData){
            return $SlabMasterData;    
        }
    }
}
