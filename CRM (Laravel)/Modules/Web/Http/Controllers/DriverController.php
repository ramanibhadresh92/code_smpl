<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\AdminUser;
use App\Models\UserCityMpg;
use App\Models\AdminTransactionGroups;
use App\Models\AdminTransaction;
use Validator;
use Log;
use App\Http\Requests\UpdateDriver;
use App\Http\Requests\AddDriver;
class DriverController extends LRBaseController
{
     /*
    * Use    :  Display a list of the company user.
    * @return Response
    */
    public function list(Request $request)
    {
        $data       = [];
        $msg        = trans('message.RECORD_FOUND');
        $request->request->add(['usertype' => DRIVER_USER_TYPE]);
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

     /* 
    * Use    :  Add Master Admin User
    * @return Response
    * Author : Axay Shah
    */
    public function create(AddDriver $request){
        $request->request->add(['user_type' => DRIVER_USER_TYPE,"otp_login_on" => 0]);
        $input  = $request->all();

        return $addAdminUser    = AdminUser::addAdminUser($request); 
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
        $request->request->add(["params"=>array(
                                'usertype' => DRIVER_USER_TYPE)
        ]);
        $adminUser      = AdminUser::getUserById($request->adminuserid);
        if(!empty($adminUser)) {
            $assigned_city  = UserCityMpg::where('adminuserid',$request->adminuserid)->pluck('cityid')->toArray();
            $adminUser['assigned_city'] = $assigned_city;
            $assigned_city  = (!empty($adminUser->task_groups)) ? $adminUser['task_groups'] = explode('|', $adminUser->task_groups) : $adminUser['task_groups'] =array();
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $adminUser]);
    }
   
     /**
    * update data  of specified resource.
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    * Author : Axay Shah
    */
    public function update(UpdateDriver $request){
        return $addAdminUser = AdminUser::updateAdminUser($request); 
    }

    /*
    * Use       : List Driver DropDown
    * Author    : Axay Shah
    * Date      : 11 March,2020
    */
    public function ListUserByType(Request $request){
        $keyword    = (isset($request->keyword) && !empty($request->keyword)) ? $request->keyword : '';
        $type       = array(CLFS,FRU,GDU,CRU);
        $data       = AdminUser::ListUserByType($type,$keyword);
        $msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);    
    }
}
