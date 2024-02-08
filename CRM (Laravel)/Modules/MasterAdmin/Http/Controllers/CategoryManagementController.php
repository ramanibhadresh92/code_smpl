<?php

namespace Modules\MasterAdmin\Http\Controllers;
use Modules\MasterAdmin\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\CategoryMaster;
use App\Models\AdminUser;
use Validator;
use App\Http\Requests\categoryAddRequest;
use App\Http\Requests\categoryEditRequest;
class CategoryManagementController extends LRBaseController
{
    /**
     * Use      :   List master category list 
     * Author   :   Axay Shah
     * Date     :   12 Sep,2018
     **/
    public function list(Request $request)
    {
        $data       = [];
        $msg        = trans('message.RECORD_FOUND');
        $validation = Validator::make($request->all(), [
            'companyId' => 'sometimes',
            'size'      => 'sometimes',  
        ]);
        if ($validation->fails()) {
            return response()->json(["code" =>VALIDATION_ERROR,"msg" =>$validation->messages(),"data"=>""]);
        }
        try {
            $data = CategoryMaster::getCategoryList($request);
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
     * @param : post category_name, parent_id, description, status, co2_saved, normal_img, select_img parameters in order to add category
     * Use    : Add Master category 
     **/
    public function create(categoryAddRequest $request)
    {
        return CategoryMaster::addCategory($request);
    }
    
    /**
     * @param : post category_name, parent_id, description, status, co2_saved, normal_img, select_img, category_id, is_submit parameters in order to edit category
     * Use    : Update category 
     **/
    public function update(categoryEditRequest $request)
    {
       return CategoryMaster::editCategory($request);
    }
    /**
     * @param : post category_name, parent_id, description, status, co2_saved, normal_img, select_img, category_id, is_submit parameters in order to edit category
     * Use    : Get category by its id
     **/
    public function getById(Request $request)
    {
        $msg      =  trans('message.RECORD_NOT_FOUND');
        $category = CategoryMaster::getCategoryDetails($request->category_id);
        if(!$category){
            $msg =  trans('message.RECORD_FOUND');
       }
       return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$category]);
    }

    /**
    * changeOrder
    *
    * Behaviour : Public
    *
    * @param : category_id, move_flag parameters in order to category order up and down pass move_flag=up for up and pass move_flag=dn for down.
    *
    * @defination : change sort order of category
    **/
    public function changeOrder(Request $request)
    {
       return CategoryMaster::changeOrder($request->all());
    }
    /**
     * dropdown
     *
     * Behaviour : Public
     *
     * @param : 
     *
     * @defination : list of category for parent category selection
     **/
    public function dropdown(Request $request)
    {
       return CategoryMaster::dropdown($request->all());
    }
    /**
    * Use       :   Change category status
    * Author    :   Axay Shah
    * Date      :   11 Jan,20119
    */
    public function changeStatus(Request $request){
        $msg            = trans('message.RECORD_NOT_FOUND');
        $changeStatus   = "";
        if(isset($request->category_id) && isset($request->status)){
            $changeStatus   = CategoryMaster::changeStatus($request->category_id,$request->status); 
            if(!empty($changeStatus)){
                $msg        = trans('message.STATUS_CHANGED');
            }
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $changeStatus]);
    }
}
