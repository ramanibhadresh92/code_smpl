<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\CompanyCategoryMaster;
use App\Http\Requests\companyCategoryAddRequest;
use App\Http\Requests\companyCategoryEditRequest;
class CompanyCategoryMasterController extends LRBaseController
{
     /**
     * Use      :   List company category list 
     * Author   :   Axay Shah
     * Date     :   25 Sep,2018
     **/
    public function list(Request $request)
    {
        $data       = [];
        $msg        = trans('message.RECORD_FOUND');
        try {
            $data = CompanyCategoryMaster::getCategoryList($request);
            if($data->isEmpty()){
                $msg = trans('message.RECORD_NOT_FOUND');
            }
        }
        catch (\Exception $e) {
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
        }
        
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
     /**
     * Use      :   List company category list 
     * Author   :   Axay Shah
     * Date     :   25 Sep,2018
     **/
    public function create(companyCategoryAddRequest $request)
    {
        
        try{
            $data = CompanyCategoryMaster::addCategory($request);
            return response()->json(['code' => SUCCESS , "msg"=>trans("message.RECORD_INSERTED"),"data"=>$data]);
        }catch(\Exception $e){
            
            Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
        }
        
    }
    
    /**
     * Use      :   List company category list 
     * Author   :   Axay Shah
     * Date     :   25 Sep,2018
     **/
    public function update(companyCategoryEditRequest $request)
    {
       return CompanyCategoryMaster::editCategory($request);
    }
     /**
     * Use      :   List company category list 
     * Author   :   Axay Shah
     * Date     :   25 Sep,2018
     **/
    public function getById(Request $request)
    {
        $msg      =  trans('message.RECORD_NOT_FOUND');
        $category = CompanyCategoryMaster::getCategoryDetails($request->id);
        if($category){
            $msg =  trans('message.RECORD_FOUND');
       }
       return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$category]);
    }

     /**
     * Use      :   List company category list 
     * Author   :   Axay Shah
     * Date     :   25 Sep,2018
     **/
    public function changeOrder(Request $request)
    {
       return CompanyCategoryMaster::changeOrder($request->all());
    }
    /**
    * Use      :   List company category list 
    * Author   :   Axay Shah
    * Date     :   25 Sep,2018
    **/
    public function dropdown(Request $request)
    {
       return CompanyCategoryMaster::dropdown($request->all());
    }

    /**
    * Use      :   get All category with daynamic select cluse
    * Author   :   Axay Shah
    * Date     :   19 Nov,2018
    **/
    public function getAllCategoryList(Request $request)
    {
       return CompanyCategoryMaster::getAllCategoryList(array('id','category_name'));
    }

    /**
    * Use       :   Change category status
    * Author    :   Axay Shah
    * Date      :   29 Sep,2018
    */
    public function changeStatus(Request $request){
        $msg            = trans('message.RECORD_NOT_FOUND');
        $changeStatus   = "";
        if(isset($request->id) && isset($request->status)){
            $changeStatus   = CompanyCategoryMaster::changeStatus($request->id,$request->status); 
            if(!empty($changeStatus)){
                $msg        = trans('message.STATUS_CHANGED');
                LR_Modules_Log_CompanyUserActionLog($request,$request->id);
            }
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $changeStatus]);
    }
}
