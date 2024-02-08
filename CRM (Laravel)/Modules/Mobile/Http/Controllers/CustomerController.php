<?php

namespace Modules\Mobile\Http\Controllers;
use Modules\Mobile\Http\Controllers\LRBaseController;

use App\Classes\AwsOperation;
use App\Models\AdminUser;
use App\Models\AppointmentCollectionDetail;
use App\Models\AppointmentTimeReport;
use App\Models\InertDeduction;
use App\Models\LocationMaster;
use App\Models\MasterCodes;
use App\Models\RequestApproval;
use App\Models\ViewCustomerMaster;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\AppointmentImages;
use App\Models\Appoinment;
use App\Models\AppointmentCollection;
use App\Models\CustomerMaster;
use App\Models\CustomerAddress;
use App\Models\StateMaster;
use Illuminate\Support\Facades\Auth;
use Modules\Mobile\Http\Requests\AddCustomer;
use Modules\Mobile\Http\Requests\UpdateCustomer;
use App\Http\Requests\AddCustomerAddress;
use App\Http\Requests\UpdateCustomerAddress;
use Log;
use JWTAuth;

class CustomerController extends LRBaseController
{
/*
Use     : Save Appointment Images
Author  : Axay Shah
Date    : 08 Feb,2019
*/

	public function saveAppointmentImages(Request $request){
		try{
			$data = AppointmentImages::saveAppointmentImages($request);
			return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
		}catch(\Exception $e){
			return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
		}
	}
/*
Use     : search customer
Author  : Axay Shah
Date    : 08 Feb,2019
*/

	public function searchCustomer(Request $request){
		try{
			$data 	= ViewCustomerMaster::searchCustomerData($request);
			$msg 	= (!empty($data))? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
			$code 	= (!empty($data)) ?  SUCCESS : ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
		}catch(\Exception $e){
			return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
		}
	}
	/*
	Use     : search customer
	Author  : Axay Shah
	Date    : 08 Feb,2019
	*/

	public function saveCollection(Request $request){
		try{
			if(isset($request->action) && !empty($request->action)){
				switch($request->action){
					case "finalize_collection" :
						if(isset($request->appointment_id) && !empty($request->appointment_id)){
							$appointment    = Appoinment::find($request->appointment_id);
							if($appointment && $appointment->customer_id > 0){
								$collection     = AppointmentCollection::retrieveCollectionByAppointment($appointment->appointment_id);
								$request->request->add($appointment->toArray());
								$request->request->add($collection->toArray());
								AppointmentCollection::FinalizeCollection($request);
								$appointment->lattitude = $request->lattitude;
								$appointment->longitude = $request->longitude;
								$appointment->save();
								/** SEND NOTIFICATION EMAIL TO CUSTOMER FOR COLLECTION COMPLETED
								 * commented as per old code */
								// CustomerMaster::sendCollectionNotificationEmail($request->collection_id);
								return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $appointment));
							}else {
								return response()->json(array("code" => SUCCESS,"msg"=>trans("message.ERROR_SAVE_COLLECTION"),"data"=> ""));
							}

						}
						break;
					case "finalize_appointment_collection" :
						$apporintIdRequest = $request->appointment_id;
						if(Appoinment::saveCollectionRequestData($request)){
							$ErrorFlag				= false;
							return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"cust_id"=> $request->customer_id,'appointment_id'=>$apporintIdRequest));
						}else{
							return response()->json(array("code" => SUCCESS,"msg"=>trans("message.ERROR_SAVE_COLLECTION"),"data"=> ""));
						}
					break;

					case "save_mediator_appointment" :
						$request->reach				= APP_STATUS_REACH;
						$request->reach_time        = (isset($request->reached_time) && !empty($request->reached_time)) ? $request->reached_time : "";
						$request->app_mediator_id	= (isset($request->appointment_id) && !empty($request->appointment_id)) ? $request->appointment_id : "";
						$request->updated_by	    = (isset($request->collection_by) && !empty($request->collection_by)) ? $request->collection_by : "";
						$requestResult              = AppoinmentMediator::updateAppointmentMediatorReachStatus($request);
						if($requestResult){
							return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $requestResult));
						}else{
							return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
						}
					break;
				}
			}
		}catch(\Exception $e){
			return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
		}
	}

	public static function addCustomer(AddCustomer $request){
		try{
			$customer       			= new CustomerMaster();
			$lastCusCode    			= MasterCodes::getMasterCode(MASTER_CODE_CUSTOMER);
			$newCreatedCode 			= $lastCusCode->code_value + 1;
			$customer->code 			= $lastCusCode->prefix.''.$newCreatedCode;
			$cityId     				= (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
			$location   				= LocationMaster::find($cityId);
			$customer->para_status_id   = CUSTOMER_STATUS_PENDING;
			$customer->email            = isset($request->email) ? $request->email : "";
			$customer->first_name       = isset($request->first_name) ? $request->first_name : "";
			$customer->mobile_no        = isset($request->mobile_no) ? $request->mobile_no : "";
			$customer->address1         = isset($request->address1) ? $request->address1 : "";
			$customer->landmark         = isset($request->landmark) ? $request->landmark : "";
			$customer->state            = (isset($location) && isset($location->state_id)) ? $location->state_id : '';
			$customer->city             = $cityId;
			$customer->country          = isset($location->country) ? $location->country : CUSTOMER_DEFAULT_COUNTRY_ID;
			$customer->zipcode          = isset($request->zipcode) ? $request->zipcode : "";
			$customer->longitude        = isset($request->longitude) ? $request->longitude : "";
			$customer->lattitude        = isset($request->lattitude) ? $request->lattitude : "";
			$customer->price_group      = isset($request->price_group) ? $request->price_group : "";
			$customer->cust_group       = isset($request->cust_group) ? $request->cust_group : "";
			$customer->ctype            = isset($request->ctype) ? $request->ctype : "";
			$customer->created_by       = Auth::user()->adminuserid;
			$customer->updated_by       = Auth::user()->adminuserid;
			$customer->company_id       = Auth::user()->company_id;

			if($customer->save()){
				if(isset($request->address1) || !empty($request->address1)){
					$InsertCustomerAddressID = CustomerAddress::insertGetId(['customer_id'=> $customer->customer_id,'address1'=> $request->address1,'landmark'=> $customer->landmark,'city'=> $customer->city,'state' => $customer->state,'country'=> $customer->country,'zipcode'=>$customer->zipcode,'gst_no' => $customer->gst_no,'longitude' => $customer->longitude,'lattitude' => $customer->lattitude,'status' => 1,'created_at'=>date('Y-m-d H:i:s')]);	
				}
				if($request->hasfile('profile_picture')) {
					$awsResponse    = AwsOperation::AddFaceByImage($request->file('profile_picture'),$customer->code,env('AWS_COLLECTION_ID'));
					if($awsResponse && isset($awsResponse['FaceRecords'][0]['Face']['FaceId'])){
						$faceId     =  $awsResponse['FaceRecords'][0]['Face']['FaceId'];
						$customer->update(['face_id'=>$faceId]);
					}

					$profile_pic    = $customer->verifyAndStoreImage($request,'profile_picture',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_PROFILE,$cityId);
					$customer->update(['profile_picture'=>$profile_pic->id]);
				}
				MasterCodes::updateMasterCode(MASTER_CODE_CUSTOMER,$newCreatedCode);
				RequestApproval::saveDataChangeRequest(FORM_CUSTOMER_ID,FILED_NAME_CUSTOMER,$customer->customer_id,$customer,$cityId);
				return response()->json(array("code" => SUCCESS,"msg"=>trans("message.CUSTOMER_ADDED"),"data"=> $customer));
			}else{
				return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
			}
		}catch (\Exception $e){
			dd($e);
		}
	}

	 /**
	Use     : Search Customer
	Author  : Axay Shah
	Date    : 23 Jan,2020
	*/
	public function searchCustomerName(Request $request){
		try{
			$msg        = trans("message.RECORD_NOT_FOUND");
			$code       = SUCCESS;
			$data       = CustomerMaster::searchCustomer($request,$authComplete=true);

		}catch(\Exeption $e){
			$msg            = trans("message.SOMETHING_WENT_WRONG");
			$code           = ERROR;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);

	}

	/**
	Use     : Search Customer
	Author  : Axay Shah
	Date    : 23 Jan,2020
	*/
	public function UpdateCustomerKYC(Request $request){
		try{
			$code       = SUCCESS;
			$data       = CustomerMaster::UpdateCustomerKYC($request);
			$msg        = ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		}catch(\Exeption $e){
			$data           = "";
			$msg            = trans("message.SOMETHING_WENT_WRONG");
			$code           = ERROR;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);

	}

	/**
	Use     : Customer List For LR Mobile Side
	Author  : Hardyesh Gupta
	Date    : 12-01-2024
	*/
	public function customerList(Request $request){
		try{
			$msg        = trans("message.RECORD_NOT_FOUND");
			$code       = SUCCESS;
			$data       = CustomerMaster::customerList($request,true);

		}catch(\Exeption $e){
			$msg            = trans("message.SOMETHING_WENT_WRONG");
			$code           = ERROR;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/**
     * Function Name : List Customer Address
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 9 Jan, 2023
     */
	public function CustomerAddresslist(Request $request)
    {
        $data       = [];
        $msg        = trans('message.RECORD_FOUND');
        try {
            $data = CustomerAddress::getCustomerAddressList($request);
            /*
            if($data->isEmpty()){
                $msg = trans('message.RECORD_NOT_FOUND');
            }
            */
        }
        catch (\Exception $e) {
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
        }
            return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }

    /**
     * Function Name : CustomerWise Customer Address
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 9 Jan, 2023
     */
    public function CustomerWiseAddresslist(Request $request)
    {
        $msg     = trans('message.RECORD_FOUND');
        $data    = CustomerAddress::getCustomerWiseAddresslist($request);
        if($data) {
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => $data]);
        }else{
            $msg     = trans('message.RECORD_NOT_FOUND');
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => '']);
        }
    }
    /**
     * Function Name : Create Customer Address
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 8 May, 2023
     */
    
    // public function createCustomerAddress(Request $request){
    public function createCustomerAddress(AddCustomerAddress $request){
         return CustomerAddress::CreateCustomerAddress($request);
        /*
        try{
            $data   =   CustomerAddress::CreateCustomerAddress($request);
            $msg    =   ($data == true) ?  trans('message.RECORD_INSERTED') : trans('message.SOMETHING_WENT_WRONG');
            $code   =   ($data == true) ?  SUCCESS : INTERNAL_SERVER_ERROR;
        }catch(\Exception $e){
            \Log::error($e->getMessage()." ".$e->getLine().$e->getTraceAsString());
            $data   = "";
            $msg    = trans('message.SOMETHING_WENT_WRONT');
            $code   =  INTERNAL_SERVER_ERROR;
        }
        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
        */
    }

    /**
     * Function Name : Edit Customer
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 8 May, 2023
     */
    public function edit(Request $request)
    {
        $msg     = trans('message.RECORD_FOUND');
        $data    = CustomerAddress::find($request->id);
        if($data) {
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => $data]);
        }else{
            $msg     = trans('message.RECORD_NOT_FOUND');
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => '']);
        }
    }

    /**
     * Function Name : Update Customer Address
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 8 May, 2023
     */
    public function updateCustomerAddress(UpdateCustomerAddress $request){
        return CustomerAddress::UpdateCustomerAddress($request);
        /*
        try{
            // $data 	= CustomerAddress::updateCustomerAddress($request);
            $data 	= CustomerAddress::UpdateCustomerAddress($request);
            $msg    = ($data == true) ?  trans('message.RECORD_UPDATED') : trans('message.SOMETHING_WENT_WRONG');
            $code   =  ($data == true) ?  SUCCESS : INTERNAL_SERVER_ERROR;
        }catch(\Exception $e){
           \Log::error($e->getMessage()." ".$e->getLine().$e->getTraceAsString());
            $data   = $e->getMessage()." ".$e->getLine().$e->getFile();
            $msg    = trans('message.SOMETHING_WENT_WRONG');
            $code   =  INTERNAL_SERVER_ERROR;
        }
        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
        */
    }

    /**
     * Function Name : GetById Customer Address
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 8 May, 2023
     */
    public function AddressGetById(Request $request)
    {
        $msg     = trans('message.RECORD_FOUND');
        $data    = CustomerAddress::getById($request->id);
        if($data) {
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => $data]);
        }else{
            $msg     = trans('message.RECORD_NOT_FOUND');
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => '']);
        }
    }

    /**
     * Function Name : Search Customer Multiple Address
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 23 June, 2023
     */
    public function CustomerAddressDropDown(Request $request){
        try{
            $msg        = trans("message.RECORD_FOUND");
            $code       = SUCCESS;
            $data = CustomerAddress::CustomerAddressDropDown($request);
        }catch(\Exeption $e){
            $msg            = trans("message.SOMETHING_WENT_WRONG");
            $code           = ERROR;
        }
        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);

    }

    /**
     * Function Name : StateList
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 11 Jan, 2023
     */
    public function GetStateList(Request $request){
        try{
            $msg        = trans("message.RECORD_FOUND");
            $code       = SUCCESS;
            $data = StateMaster::GetStateList();
        }catch(\Exeption $e){
            $msg            = trans("message.SOMETHING_WENT_WRONG");
            $code           = ERROR;
        }
        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);

    }

    /**
     * Function Name : City List
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 11 Jan, 2023
     */
    public function GetCityList(Request $request){
        try{
            $msg        = trans("message.RECORD_FOUND");
            $code       = SUCCESS;
            $data 		= LocationMaster::CityList($request);
        }catch(\Exeption $e){
            $msg            = trans("message.SOMETHING_WENT_WRONG");
            $code           = ERROR;
        }
        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);

    }
}
