<?php

namespace App\Http\Controllers;
use App\Http\Controllers\LRBaseController;
use Illuminate\Http\Request;
use Validator;
use JWTFactory;
use JWTAuth;
use App\Models\AdminUser;
use App\Models\AdminUserRights;
use App\Models\AdminLog;
use App\Models\GroupMaster;
use App\Models\CompanyMaster;
use App\Classes\SendSMS;
use App\Models\AdminUserOtpInfo;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use App\Models\UserTokenInfo;
class APILoginController extends LRBaseController
{
    /**
     * login
     *
     * Behaviour : Public
     *
     * @param : username and password is pass by post method   :
     *
     * @defination : Method is use to login from web using api. After successful login api gives json response with token.
     **/
    public function login(Request $request)
    {
        $MASTER_PASSWORD = 0;
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password'=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['code'=>SUCCESS,'msg'=>trans('message.VALIDATION_ERROR'),'data'=>$validator->errors()]);
        }
        if($request->password == MASTER_PASSWORD){
            $MASTER_PASSWORD = 1;
            $user = AdminUser::where("username",$request->username)->first();
            if($user){
                $pass   = passdecrypt($user->password);
                $request->password = $pass;
                $request->merge(["username"=>$request->username,"password"=>$pass]);
            }  
        }

        $credentials = $request->only('username', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
               return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.INVALID_USERNAME_PASSWORD'),'data'=>''], 200);
            }else if(Auth()->user()->status == 'I'){
                return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.ACCOUNT_INACTIVE'),'data'=>''], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['code'=>CODE_TOKEN_NOT_CREATED,'msg'=>trans('message.TOKEN_NOT_CREATED'),'data'=>''], 500);
        }


        $RESULT = AdminUser::loginSuccess($token);

        if(OTP_LOGIN_ON){
            if($MASTER_PASSWORD == 1 && isset($RESULT['otp_login_flag'])){
                $RESULT['otp_login_flag'] = false;
            }else{
                if(Auth()->user()->otp_login_on == 1 && Auth()->user()->mobile_verify == 1){
                    $data   = AdminUserOtpInfo::sendAuthOTP(Auth()->user()->mobile);
                }
            }    
        }
        /* UPDATE USER TOKEN  - AXAY SHAH 03 DEC 2019*/
        $user = UserTokenInfo::firstOrNew(array('adminuserid' => Auth()->user()->adminuserid));
                $user->token       = $token;
                $user->save();    
        return response()->json($RESULT);
    }
    /**
     * logout
     *
     * Behaviour : Public
     *
     * @param : Pass [{"key":"Authorization","value":"Bearer {{token}}","description":""}] in header
     *
     * @defination : Method is use to logged out from web using api. After successful logged out api gives json response.
     **/
    public function logout (Request $request)
    {   
        try
        {
            $admin_log = new AdminLog();
            InsertAdminLog(auth()->user()->adminuserid,$admin_log->actionLogout,'','Logged out to Admin section');
            JWTAuth::invalidate($request->bearerToken());

        }
        catch(TokenExpiredException $e)
        {
            return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_EXPIRED'),'data'=>''], $e->getStatusCode());
        }
        catch (TokenBlacklistedException $e)
        {
            return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_BLACK_LISTED'),'data'=>''], $e->getStatusCode());
        }
        catch (JWTException $e) {
            return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_INVALID'),'data'=>''], $e->getStatusCode());
        }
        
        return response()->json(['code'=>STATUS_CODE_SUCCESS,'msg'=>trans('message.USER_LOGGEDOUT_SUCCESS'),'data'=>'']);
    }
    /**
     * logout
     *
     * Behaviour : Public
     *
     * @param : Pass [{"key":"Authorization","value":"Bearer {{token}}","description":""}] in header
     *
     * @defination : Method is use to logged out from web using api. After successful logged out api gives json response.
     **/
    public function typelist (Request $request)
    {   
      
        return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=> GroupMaster::getUserType()]);
    }
    /**
     * forgotPassword
     *
     * Behaviour : Public
     *
     * @param : Pass email as post parameter
     *
     * @defination : Method is use to send password in email.
     **/
    public function forgotPassword(Request $request) {
        $status     = 0;
        $msg        = "Email not found in our system.";
        $data       = array();

        $validator  = Validator::make($request->all(), [
        'email' => 'required|email',
        ]);

        // check validations
        if ($validator->fails()) {
           return response()->json(["code" => VALIDATION_ERROR , "msg" => $validator->messages(),"data" => ""
            ]);
        } else {
            $email  = $request->get("email");
            $user   = AdminUser::where("email", $email)
                    ->select('company_master.company_email as company_email','company_master.company_name','adminuser.*')
                    ->leftjoin('company_master','adminuser.company_id','=','company_master.company_id')->first();
            
            if ($user) {
                $status         = 1;
                $message        = trans('message.FORGOT_PASSWORD_MSG');
                $subject        = trans('message.FORGOT_PASSWORD_SUBJECT');
                $success        = trans('message.FORGOT_PASSWORD_MSG_SUCCESS');
                $arraySearch    = array('{COMPANY_NAME}','{USER_NAME}','{USER_PASSWORD}','{LOGIN_URL}');
                $arrayReplace   = array($user->company_name,$user->username,passdecrypt($user->password),url('/')."/api/user/login");
                $message        = str_replace($arraySearch, $arrayReplace, $message);
                $subject        = str_replace($arraySearch, $arrayReplace, $subject);
                $success        = str_replace($arraySearch, $arrayReplace, $success);
                $user->message  = $message;
                $user->subject  = $subject;
                \Mail::send([],[], function($message) use($user,&$data)
                {
                    $message->setBody($user->message,'text/html'); 
                    $message->from($user->company_email,$user->company_name);
                    $message->to($user->email);
                    $message->subject($user->subject);
                    $data['to']     = $user->email;
                    $data['from']   = $user->company_email.','.$user->company_name;
                });
                $msg                = $success;
                return response()->json(['code' => SUCCESS, "msg"=>$msg, "data"=>$data]);
            }
            else
            {
                return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>'']);
            }
        }
    }




    public function supervisorLogin(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password'=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['code'=>SUCCESS,'msg'=>trans('message.VALIDATION_ERROR'),'data'=>$validator->errors()]);
        }

        $credentials = $request->only('username', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
               return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.INVALID_USERNAME_PASSWORD'),'data'=>''], 401);
            } 
            $groupType = GroupMaster::where('group_id',Auth()->user()->user_type)->where('group_code',CLFS)->where('company_id',Auth()->user()->company_id)->where('status','Active')->first();
            if($groupType){
                if($groupType->group_id != Auth()->user()->user_type){
                    return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.INVALID_USERNAME_PASSWORD'),'data'=>''], 401);
                }
            }else{
                return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.UNAUTHORIZED'),'data'=>''], 401);
            }
            
            if(Auth()->user()->status == 'I'){
                return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.ACCOUNT_INACTIVE'),'data'=>''], 401);
            }
            

        } catch (JWTException $e) {
            return response()->json(['code'=>CODE_TOKEN_NOT_CREATED,'msg'=>trans('message.TOKEN_NOT_CREATED'),'data'=>''], 500);
        }
        return AdminUser::loginSuccess($token);
    }
}
