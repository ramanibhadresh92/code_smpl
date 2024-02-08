<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\CategoryMaster;
use App\Models\AdminUser;
use Validator;
class CategoryMasterController extends LRBaseController
{
    /**
     * list
     *
     * Behaviour : Public
     *
     * @param : post size parameter for per page record, companyId, companyName, companyCode, companyEmail, contactNumber, status (Active/In-active), period (1,2,3), startDate, endDate 
     *
     * @defination : Display list of category
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
            return response()->json(["code" => INTERNAL_SERVER_ERROR , "msg" => $validation->messages(),"data" => ""
            ]);
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
     * add
     *
     * Behaviour : Public
     *
     * @param : post category_name, parent_id, description, status, co2_saved, normal_img, select_img parameters in order to add category
     *
     * @defination : Add category
     **/
    public function add(Request $request)
    {
        return CategoryMaster::addCategory($request->all());
    }
    /**
     * edit
     *
     * Behaviour : Public
     *
     * @param : post category_name, parent_id, description, status, co2_saved, normal_img, select_img, category_id, is_submit parameters in order to edit category
     *
     * @defination : edit category
     **/
    public function edit(Request $request)
    {
       return CategoryMaster::editCategory($request->all());
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
}
