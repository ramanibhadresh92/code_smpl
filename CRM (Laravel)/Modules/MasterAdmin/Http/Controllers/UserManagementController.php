<?php

namespace Modules\MasterAdmin\Http\Controllers;
use Modules\MasterAdmin\Http\Controllers\LRBaseController;
use Illuminate\Http\Request; 
use Illuminate\Http\Response;
use App\Models\AdminUser;
use App\Models\UserCityMpg;
use Validator;
use Log;
use App\Models\AdminTransactionGroups;
use App\Models\AdminTransaction;

class UserManagementController extends LRBaseController
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('masteradmin::index');
    }
     /*
    * Use    :  Display a list of the company user.
    * @return Response
    */
    public function list(Request $request)
    {
        
        $data       = [];
        $msg        = trans('message.RECORD_FOUND');
        $validation = Validator::make($request->all(), [
            'size'      => 'sometimes',  
        ]);
        if ($validation->fails()) {
            return response()->json(["code" => VALIDATION_ERROR , "msg" => $validation->messages(),"data" => ""
            ]);
        }
        try {
            $data = AdminUser::getCompanyUser($request->size,$request);
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
    * Show the data editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    * Author : Axay Shah
    */
    public function edit(Request $request)
    {
        $msg            = trans('message.RECORD_FOUND');
        $adminUser      = AdminUser::getUserById($request->adminuserid);
        if(!empty($adminUser)) {
            $assigned_city  = UserCityMpg::where('adminuserid',$request->adminuserid)->pluck('cityid')->toArray();
            $adminUser['assigned_city'] = $assigned_city;
            $assigned_city  = (!empty($adminUser->task_groups)) ? $adminUser['task_groups'] = explode('|', $adminUser->task_groups) : $adminUser['task_groups'] =array();
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $adminUser]);
    }
    /* 
    * Use    :  Add Master Admin User
    * @return Response
    * Author : Axay Shah
    */
    public function create(Request $request){
        $input  = $request->all();
        return $addAdminUser    = AdminUser::addAdminUser($request); 
    }
     /**
    * update data  of specified resource.
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    * Author : Axay Shah
    */
    public function update(Request $request){
        return $addAdminUser = AdminUser::updateAdminUser($request); 
    }
    

    /**
    * Use       :   Change user status
    * Author    :   Axay Shah
    * Date      :   04 Sep,2018
    */
    public function changeStatus(Request $request){
        $msg            = trans('message.STATUS_CHANGED');
        $changeStatus   = AdminUser::changeStatus($request->adminuserid,$request->status); 
        if(!empty($changeStatus)){
            $msg        = trans('message.STATUS_CHANGED');
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $changeStatus]);
    }
}
