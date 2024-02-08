<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Config;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use JWTAuth;
use App\Models\CustomerLoginDetail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Facades\LiveServices;
class CustomerLoginDetail extends Authenticatable implements JWTSubject
{
    protected 	$table 		=	'customer_login_detail';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    public 		$timestamps = 	false;

	/**
    * Function Name : UpdateDeviceID
    * @return
    * @author Sachin Patel
    * @date 17 April, 2019
    */
    public function UpdateDeviceID($device_id)
    {
         return $this->update(['device_id' => $device_id]);
    }


    /**
	* Function Name : SaveGCMID
	* @return
	* @author Sachin Patel
 	* @date 17 April, 2019
	*/
	public function SaveGCMID($GCM_ID="")
	{
		return $this->update(['registration_id' => $GCM_ID]);
    }

    /**
    * Function Name : UpdateDeviceType
    * @return
    * @author Sachin Patel
    * @date 01 May, 2019
    */
    public function UpdateDeviceType($device_type="")
    {
        return $this->update(['device_type' => $device_type]);
    }

    /**
    * Function Name : customer_login
    * @param
    * @return
    * @author Sachin Patel
    */
   	public function customer_login($request) 
    {
       	$browser = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
       	if (!empty($request->header('x-device-id'))){
       	    $browser = "Login from Device :: ".$request->header('x-device-id');
       	}
        
		$this->update(['last_login_date' => Carbon::now(),'browser_type'=>DBVarConv(serialize($browser))]);
        $APP_CUSTOMER_TYPE_FILTER = str_replace('"',"",APP_CUSTOMER_TYPE_FILTER );
        $APP_CUSTOMER_TYPE_FILTER = explode(",",$APP_CUSTOMER_TYPE_FILTER);

		$CUSTUMERS_ID 	= '';
		$data 			= 	self::select('customer_login_detail.id as cid','customer_login_detail.name', 'customer_login_detail.email', 'customer_login_detail.mobile',
								'customer_login_detail.last_login_date','customer_login_detail.profile_photo as profile_photo','customer_master.customer_id as cmid')
							->leftJoin('customer_contact_details','customer_login_detail.mobile','=','customer_contact_details.mobile')
							->leftJoin('customer_master','customer_master.customer_id','=','customer_contact_details.customer_id')
							->where('customer_login_detail.mobile',$this->mobile)
							->whereIn('customer_master.ctype',$APP_CUSTOMER_TYPE_FILTER)
                            ->get()->toArray();
        $user_detail = array();
		foreach ($data as $key => $row) {
				$CUSTUMERS_ID .= $row['cmid'].',';
				$Profile_Picture 	= "";
				$SERVER_IMAGE_NAME 	= public_path()."/images/customer/".$row['cid']."/".$row['profile_photo'];
				if (file_exists($SERVER_IMAGE_NAME)) {
					$Profile_Picture = asset('/')."images/customer/".$row['cid']."/".$row['profile_photo'];
				}

				$user_detail	= array("cid"             =>$row['cid'],
										"name"            =>$row['name'],
										"email"           =>$row['email'],
										"mobile"          =>$row['mobile'],
										"profile_picture" =>$Profile_Picture,
										"last_login_date" =>$row['last_login_date']);
		    	
		    }    

	    if (!empty($this->device_id)) {
			return array("customer_id"=>$CUSTUMERS_ID,"user_detail"=>$user_detail);
		}
    }	

    /**
    * Function Name : saveLoginDetail
    * @param
    * @return
    * @author Sachin Patel
    */
    public static function saveLoginDetail($request){
    	$browser 	= 	isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
    	$data 		= 	self::create([
			    			'name' 			=> DBVarConv($request->clscustomer_first_name),
			    			'email' 		=> DBVarConv($request->clscustomer_email),
			    			'mobile' 		=> DBVarConv($request->clscustomer_mobile),
			    			'city' 			=> DBVarConv($request->clscustomer_city),
			    			'zipcode' 		=> DBVarConv($request->clscustomer_zipcode),
			    			'password' 		=> DBVarConv(passencrypt($request->clscustomer_password)),
			    			'browser_type' 	=> DBVarConv($browser),
			    			'device_id' 	=> ($request->header('x-device-id') != "") ? DBVarConv($request->header('x-device-id')) : "",
                            'create_date'   => date("Y-m-d H:i:s"),
    					]);
    	return $data;
    }

    /**
    * Function Name : forgotPassword
    * Sending email and text message on register mobile no
    * @param
    * @return
    * @author Sachin Patel
    */
    public static function forgotPassword($request){
    	$query = self::select();

    	if(isset($request->clscustomer_mobile) && $request->clscustomer_mobile !=""){
    		$query->where('mobile',$request->clscustomer_mobile);
    	}

    	if(isset($request->clscustomer_email) && $request->clscustomer_email !=""){
    		$query->where('email',$request->clscustomer_email);
    	}

    	$data = $query->first();
    	if(!$data){
    		return false;
    	}

    	if(isset($request->clscustomer_email) && $request->clscustomer_email !=""){
    		$email['subject'] 	= TITLE.' Login Info';
    		$email['from']		= GENERAL_FROM_EMAIL;
			$email['to']		= $data->email;

			$emaildata['title'] 	= TITLE;
			$emaildata['mobile'] 	= $data->mobile;
			$emaildata['password'] 	= passdecrypt($data->password);		

			\Mail::send('email-template.corporate.forgotpassword', ['emaildata'=>$emaildata], function ($message) use ($email) {
                    $message->from(GENERAL_FROM_EMAIL, TITLE);
                    $message->to($email['to']);
                    $message->subject($email['subject']);
                });
			return true;
    	}

    	if(isset($request->clscustomer_mobile) && $request->clscustomer_mobile !=""){
    		$message = 'Your Login Password is '.passdecrypt($data->password);
    		\App\Classes\SendSMS::sendMessage($data->mobile, $message);
    		return true;
    	}
    }

    /**
    * Function Name : changepassword
    * For Corporate App
    * @return
    * @author Sachin Patel
    * @date 23 April,2019
    */
    public static function changepassword($request){
        $browser        = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
        $data           = self::find(Auth::user()->id)->update([
            'browser_type'  => $browser,
            'device_id'     => $request->header('x-device-id'),
            'update_date'   => Carbon::now(),
            'password'      => DBVarConv(passencrypt($request->clscustomer_new_password))
        ]);

        if($data){
            return true;
        }else{
            return false;
        }
    }

    /**
    * Function Name : validateChangePassword
    * For Corporate App
    * @return
    * @author Sachin Patel
    * @date 23 April,2019
    */
    public static function validateChangePassword($request){
        $error = array();
        $data = self::find(Auth::user()->id);
        if($data){
            if($data->password != passencrypt($request->clscustomer_old_password)){
                $error['message'] = 'Current password is not matched with database.';
            }else if($data->password  == passencrypt($request->clscustomer_new_password)){
                $error['message'] = 'Current and new password cannot be same.';
            }
        }else{
             $error['message'] = 'Invalid Customer information.';
        }
        
        return $error;
    }

    /**
    * Function Name : getJWTIdentifier
    * For Corporate App
    * @purpose Required for JWT Auth
    * @return
    * @author Sachin Patel
    * @date 24 April,2019
    */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
    * Function Name : getJWTCustomClaims
    * For Corporate App
    * @purpose Required for JWT Auth
    * @return
    * @author Sachin Patel
    * @date 24 April,2019
    */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
    * Function Name : UpdateCustomerProfile
    * For Corporate App
    * @return
    * @author Sachin Patel
    * @date 24 April,2019
    */
    public static function UpdateCustomerProfile($request){
        if($request->header('x-device-id') !=""){
            $data = self::find(auth()->user()->id)->update([
                    'name'          => $request->clscustomer_first_name,
                    'email'         => $request->clscustomer_email,
                    'city'          => $request->clscustomer_city,
                    'zipcode'       => $request->clscustomer_zipcode,
                    'device_id'     => $request->header('x-device-id'),
                    'update_date'   => Carbon::now(),
                ]);
        }

        if(isset($request->clscustomer_profile_photo) && !empty($request->clscustomer_profile_photo)){
            self::UploadProfilePhoto($request,auth()->user()->id); 
        }

        return true;
    }

    /**
    * Function Name : UploadProfilePhoto
    * For Corporate App
    * @return
    * @author Sachin Patel
    * @date 24 April,2019
    */
    public static function UploadProfilePhoto($request, $customerId){
        $fieldName  = 'clscustomer_profile_photo';
        $path       = public_path(PATH_IMAGE.'/').'corporate/customer/'. $customerId;
        
        if($request->hasFile($fieldName)) {
            $file = $request->file($fieldName);
            
            if(!is_dir($path)) {
                mkdir($path,0777,true);
            }
            $customer = CustomerLoginDetail::find($customerId);
            if($customer->profile_photo){
               @unlink($path.'/'.$customer->profile_photo);
            }
            

            $orignalImg     = $fieldName."_".time().'.'.$file->getClientOriginalExtension();
            /*move orignal file*/
            $file->move($path.'/', $orignalImg);
            
            $customer->profile_photo = $orignalImg;
            $customer->save();
        }
    }
}
