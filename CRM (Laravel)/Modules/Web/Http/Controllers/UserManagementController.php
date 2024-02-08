<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\AdminUser;
use App\Models\UserCityMpg;
use Validator;
use Log;
use App\Models\AdminTransactionGroups;
use App\Models\AdminTransaction;
use App\Http\Requests\UpdateAdminUser;
use App\Http\Requests\AddAdminUser;
use App\Models\AdminUserOtpInfo;
use App\Models\GroupMaster;
class UserManagementController extends LRBaseController
{

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
    * 
    * Use    :  add company user
    * @return Response
    */
    public function create(AddAdminUser $request){
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
        $adminUser      = AdminUser::getUserById($request->adminuserid);
        if(!empty($adminUser)) {
            $assigned_city  = UserCityMpg::where('adminuserid',$request->adminuserid)->pluck('cityid')->toArray();
            $adminUser['assigned_city'] = $assigned_city;
            $assigned_city  = (!empty($adminUser->task_groups)) ? $adminUser['task_groups'] = explode('|', $adminUser->task_groups) : $adminUser['task_groups'] =array();
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $adminUser]);
    }
     /**
    * get user detail using token
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    * Author : Axay Shah
    */
    public function userDetailUsingToken(Request $request)
    {
        $msg            = trans('message.RECORD_FOUND');
        $adminUser      = AdminUser::getUserById(Auth()->user()->adminuserid);
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
    public function update(UpdateAdminUser $request){
        return $addAdminUser = AdminUser::updateAdminUser($request); 
    }
    /**
    * Use       :   Show user password
    * Author    :   Axay Shah
    * Date      :   27 Aug,2018
    */
    public function showPassword(Request $request){
        $msg            = trans('message.RECORD_NOT_FOUND');
        $showPassword   = AdminUser::showPassword($request->all()); 
        if(!empty($showPassword)){
            $msg        = trans('message.RECORD_FOUND');
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $showPassword]);
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
            LR_Modules_Log_CompanyUserActionLog($request,$request->adminuserid);
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $changeStatus]);
    }

    /**
     * changePassword
     *
     * Behaviour : Public
     *
     * @param : Post old_password, password and password_confirmation parameters
     *
     * @defination : In order to change logged in person password.
     **/
    public static function changePassword(Request $request){
        $msg            = trans('message.RECORD_NOT_FOUND');
        $changePassword = AdminUser::changePassword($request->all()); 
        $msg            = $changePassword['msg'];
        if($changePassword['Success']==1){
            $msg        = trans('message.RECORD_UPDATED');
            LR_Modules_Log_CompanyUserActionLog($request,$request->adminuserid);
            return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" =>""]);
        }
        elseif(empty($msg))
        {
            $msg        = trans('message.OLD_PASSWORD_NOT_FOUND');
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" =>""]);
    }

    /**
     * changeProfile
     *
     * Behaviour : Public
     *
     * @param : Post  parameters firstname, lastname, zip, mobile, email, address1, address2
     *
     * @defination : In order to change profile of logged in person.
     **/
    public static function changeProfile(Request $request){
        $msg            = trans('message.RECORD_NOT_FOUND');
        $changeProfile  = AdminUser::changeProfile($request->all()); 
        $msg            = $changeProfile['msg'];
        if($changeProfile['Success']==1){
            $msg        = trans('message.RECORD_UPDATED');
            LR_Modules_Log_CompanyUserActionLog($request,Auth()->user()->adminuserid);
            return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" =>""]);
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" =>""]);
    }

    public static function resetPassword(Request $request)
    {
        $msg    = trans('message.PASSWORD_CHANGED_FAILED');
        $reset  = AdminUser::resetPassword($request);
        $code   = VALIDATION_ERROR;
        if($reset) {
            $code   = SUCCESS;
            $msg    = trans('message.PASSWORD_CHANGED');
        }
        return response()->json(["code" =>$code,"msg" =>$msg,"data" =>$reset]);
        
    }

    /**
     * checkToken
     * Behaviour : Public
     * @param : object $request
     * @defination : In order to verify token is expired or not.
     **/
    public function checkToken(Request $request)
    {
             $ip = \Request::ip();
        return response()->json(["code" =>SUCCESS,"msg" =>"ACTIVE_TOKEN","ip_address"=>$ip]);
    }

    /**
     * Function Name : getTypeWiseUserList
     * @param $input ($request)
     * @return json Array
     * @author Sachin Patel
     * @date 29 March, 2019
     */
    public static function getTypeWiseUserList(){
        $data       = [];
        $msg        = trans('message.RECORD_FOUND');
        $data       = AdminUser::getTypeWiseUserList($usertype="CRU,FRU,GDU",true,true,false);
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $data]);
    }

    /*
    Use     : Send Auth OTP for adminuser
    Author  : Axay Shah
    Date    : 16 Aug,2019
    */
    public function ResendAuthOTP(Request $request){
        $MOBILE = (isset($request->mobile) && !empty($request->mobile)) ?  $request->mobile : Auth()->user()->mobile; 
        $data   = AdminUserOtpInfo::sendAuthOTP($MOBILE);
        $msg    = ($data) ? trans("message.OTP_SUCCESS") : trans("message.OTP_FAILED");
        $code   = ($data) ?  SUCCESS : INTERNAL_SERVER_ERROR;
        return response()->json(["code" =>$code,"msg" =>$msg,"data" => $data]);
    }

    /*
    Use     : Verify OTP
    Author  : Axay Shah
    Date    : 16 Aug,2019
    */
    public function VerifyOTP(Request $request){
        $data   = AdminUserOtpInfo::VerifyOTP($request);
        $msg    = ($data) ? trans("message.USER_LOGIN_SUCCESS") : trans("message.OTP_VERIFICATION_FAILED");
        $code   = ($data) ?  SUCCESS : INTERNAL_SERVER_ERROR;
        return response()->json(["code" =>$code,"msg" =>$msg,"data" => $data]);
    }

    /*
    Use     : Verify Mobile No
    Author  : Axay Shah
    Date    : 20 Aug,2019
    */
    public function VerifyMobile(Request $request){
        $mobileNo  = (isset($request->mobile) && !empty($request->mobile)) ?  $request->mobile : 0; 
        $data      = AdminUser::VerifyMobile($mobileNo);
        return $data;
    }

    public function AddOrUpdateGroup(Request $request){
        $data   = GroupMaster::AddOrUpdateGroup($request->all());
        $msg    = ($data) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
        $code   = ($data) ?  SUCCESS : INTERNAL_SERVER_ERROR;
        return response()->json(["code" =>$code,"msg" =>$msg,"data" => $data]);
    }
    public function ListGropuMaster(Request $request){
        $data   = GroupMaster::ListGroupMaster($request);
        $msg    = ($data) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
        $code   = ($data) ?  SUCCESS : INTERNAL_SERVER_ERROR;
        return response()->json(["code" =>$code,"msg" =>$msg,"data" => $data]);
    }
}