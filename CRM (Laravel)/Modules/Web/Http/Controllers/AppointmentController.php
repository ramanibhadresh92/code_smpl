<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use App\Models\AppointmentCollection;
use App\Models\VehicleDriverMappings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests\AppoinmentAddRequest;
use App\Http\Requests\AppoinmentUpdateRequest;
use App\Http\Requests\AppoinmentAutoAddRequest;
use App\Http\Requests\AppointmentInvoiceUpdate;
use App\Http\Requests\FocChangeVehicle;
use App\Models\Appoinment;
use App\Models\ViewAppointmentList;
use App\Models\FocAppointment;
use App\Models\AppointmentImages;
use App\Models\CompanyProductPriceDetail;
use App\Models\CustomerMaster;
use App\Models\LocationMaster;
use App\Models\VehicleMaster;
use App\Models\AdminUser;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductQualityParameter;
use Modules\Web\Http\Requests\SaveFocAppointment;
use Log,DB;
class AppointmentController extends LRBaseController
{
	/**
	 * Use      : Add customer Appointment
	 * Author   : Axay Shah
	 * Date     : 15 Nov,2018
	*/
	public function create(AppoinmentAddRequest $request)
	{
		try{
			$data = Appoinment::validateEditAppointment($request,true);
			if($data == true){
				$data = Appoinment::setAppointmentValidation($request);
				if(isset($data['code']) && $data['code'] == VALIDATION_ERROR){
					$data   = array("code" => VALIDATION_ERROR,"msg"=>$data['msg'],"data"=>
						"");
				}else{
					Appoinment::saveAppointment($request);
					$data = array("code" => SUCCESS,"msg"=>trans("message.RECORD_INSERTED"),"data"=> "");
				}
			}
			return response()->json($data);
		}catch(\Exception $e){
			Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
			return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
		}
	}
	/**
	 * Use      : List Appointment
	 * Author   : Axay Shah
	 * Date     : 16 Nov,2018
	*/
	public function searchAppointment(Request $request){
		try{
		$data = ViewAppointmentList::searchAppointment($request,true);
		return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
		}catch(\Exception $e){

			Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine());

			return response()->json(array("code" => SUCCESS,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
		}
	}
	/**
	 * Use      : List Appointment
	 * Author   : Axay Shah
	 * Date     : 16 Nov,2018
	*/

	public function updateAppointment(AppoinmentUpdateRequest $request)
	{
		try{
			DB::beginTransaction();
			$data = Appoinment::validateEditAppointment($request,true);
			if($data == true){
				$data = Appoinment::setAppointmentValidation($request);
				if(isset($data['code']) && $data['code'] == VALIDATION_ERROR){
					$data   = array("code" => VALIDATION_ERROR,"msg"=>$data['msg'],"data"=>
						"");
				}else{
					Appoinment::updateAppointment($request);
					$data   = array("code" => SUCCESS,"msg"=>trans("message.RECORD_UPDATED"),"data"=> "");
				}
			}
			DB::commit();
			return response()->json($data);

		}catch(\Exception $e){

			DB::rollback();
			Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine());
			return response()->json(array("code" => SUCCESS,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
		}
	}
	 /**
	 * Use      : List FOC Appointment
	 * Author   : Axay Shah
	 * Date     : 11 Dec,2018
	*/

	public function searchFocAppointment(Request $request){
		$data = FocAppointment::searchFocAppointment($request);
		return response()->json(['code' => SUCCESS , "msg"=>trans("message.RECORD_FOUND"),"data"=>$data]);
	}

	/*
	Use     : save FOC appointment
	Author  : Axay Shah
	Date    : 11 Dec,2018
	*/

	public function saveFocAppointment(SaveFocAppointment $request){
		try{
			$data = FocAppointment::saveFocAppointment($request);
			if(isset($data['code']) && $data['code'] == VALIDATION_ERROR){
				return response()->json(array("code" => VALIDATION_ERROR,"msg"=>$data['msg'],"data"=>
						""));
			}
			return response()->json(['code' => SUCCESS , "msg"=>trans("message.RECORD_INSERTED"),"data"=>$data]);
		}catch(\Exception $e){
			dd($e);
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=>$e]);
		}
	}
	/*
	Use     : update FOC appointment
	Author  : Axay Shah
	Date    : 12 Dec,2018
	*/

	public function updateFocAppointment(SaveFocAppointment $request){
		try{
			$data = FocAppointment::updateFocAppointment($request);
			if(isset($data['code']) && $data['code'] == VALIDATION_ERROR){
				return response()->json(array("code" => VALIDATION_ERROR,"msg"=>$data['msg'],"data"=>
						""));
			}
			return response()->json(['code' => SUCCESS , "msg"=>trans("message.RECORD_UPDATED"),"data"=>$data]);
		}catch(\Exception $e){
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=>$e]);
		}
	}
	/*
	Use     : Get By Id
	Author  : Axay Shah
	Date    : 12 Dec,2018
	*/

	public function getById(Request $request){
		try{
			$data =  FocAppointment::retrieveFOCAppointment($request->appointment_id);
			return response()->json(['code' => SUCCESS , "msg"=>trans("message.RECORD_FOUND"),"data"=>$data]);
		}catch(\Exception $e){
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=>$e]);
		}
	}
	/*
	Use     : Cancle FOC Appointment
	Author  : Axay Shah
	Date    : 12 Dec,2018
	*/
	public function cancelFOCAppointment(Request $request){
		$data = FocAppointment::cancelFOCAppointment($request);
		($data) ? $msg = trans("message.FOC_APPOINTMENT_DELETED") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/*
	Use     : Update schedular
	Author  : Axay Shah
	Date    : 12 Dec,2018
	*/
	public function updateSchedular(Request $request){
		$data = FocAppointment::cancelFOCAppointment($request);
		($data) ? $msg = trans("message.FOC_APPOINTMENT_DELETED") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}
	 /*
	Use     : reopen appointment
	Author  : Axay Shah
	Date    : 19 Dec,2018
	*/
	public function reopenAppointment(Request $request){
		$data = Appoinment::reopenAppointment($request->appointment_id);
		if($data['code'] == "SUCCESS" && $data['msg'] == "Appointment Re-open successfully"){
			LR_Modules_Log_CompanyUserActionLog($request,$request->appointment_id);
		}
		return response()->json($data);
	}
	/*
	Use     : get Appointment Images
	Author  : Axay Shah
	Date    : 19 Dec,2018
	*/
	public function getAppointmentImage(Request $request){
		$data = AppointmentImages::getAppointmentImage($request);
		return response()->json(['code' => SUCCESS , "msg"=>trans("message.RECORD_FOUND"),"data"=>$data]);
	}
	/*
	Use     : save and Send appointment email to customer contact
	Author  : Axay Shah
	Date    : 19 Dec,2018
	*/
	public function saveAppointmentImageEmailDetail(Request $request){
		$data = AppointmentImages::saveAppointmentImageEmailDetail($request);
		return response()->json(['code' => SUCCESS , "msg"=>trans("message.RECORD_INSERTED"),"data"=>$data]);
	}

	/*
	Use     : listFocCustomer
	Author  : Axay Shah
	Date    : 24 Dec,2018
	*/
	public function listFocCustomer(Request $request){
		$data = array();
		$data = FocAppointment::searchFOCAppointmentCustomer($request);
		return response()->json(['code' => SUCCESS , "msg"=>trans("message.RECORD_FOUND"),"data"=>$data]);
	}

	/**
	 * Use      : List Free appointment for waybridge approval
	 * Author   : Axay Shah
	 * Date     : 02 April,2019
	*/
	public function focAppointmentApprovalList(Request $request){
		try{
			$params = $request->params;
			$params['earn_type']        = EARN_TYPE_FREE;
			$params['para_status_id']   = APPOINTMENT_COMPLETED;
			$params['is_approval']      = "Y";
			$request->request->add(["params"=>$params]);
			$data   = ViewAppointmentList::searchAppointment($request,true);
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
		}catch(\Exception $e){
			Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine());
			return response()->json(array("code" => SUCCESS,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
		}
	}
	/**
	 * Use      : Approv foc appointment
	 * Author   : Axay Shah
	 * Date     : 02 April,2019
	*/
	public function updateStatusFocAppointment(Request $request){
		try{
		$comment    = (isset($request->comment)) ? $request->comment : " ";
		$appointment= (isset($request->appointment_id)) ? $request->appointment_id : " ";
		$status     = (isset($request->status))  ? $request->status : " ";
		$data       = Appoinment::updateStatusFoc($appointment,$status,$comment);
		return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
		}catch(\Exception $e){
			Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine());
			return response()->json(array("code" => SUCCESS,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
		}
	}

	/**
	 * Use      : cancelBulkAppointment
	 * For      : Web
	 * Author   : Sachin Patel
	 * Date     : 11 April,2019
	 */
	public function cancelBulkAppointment(Request $request){
		try{
			if(isset($request->appointment_id) && is_array($request->appointment_id)){
				$comment = (isset($request->cancel_reason) && !empty($request->cancel_reason)) ? $request->cancel_reason : "";
				Appoinment::whereIn('appointment_id',$request->appointment_id)->update(['para_status_id'=>APPOINTMENT_CANCELLED,"cancel_reason"=>$comment,"updated_by"=>Auth()->user()->adminuserid]);
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_UPDATED'),"data" => '']);
			}else{
				return response()->json(["code" => 422 , "msg" =>trans('message.SOMETHING_WENT_WRONG'),"data" => '']);
			}

		}catch(\Exception $e){
			Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine());
			return response()->json(array("code" => SUCCESS,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
		}
	}


	/**
	 * Use      : cancelFocBulkAppointment
	 * For      : Web
	 * Author   : Sachin Patel
	 * Date     : 11 April,2019
	 */
	public function cancelFocBulkAppointment(Request $request){
		try{
			if(isset($request->appointment_id) && is_array($request->appointment_id)){
				$comment = (isset($request->cancel_reason) && !empty($request->cancel_reason)) ? $request->cancel_reason : "";
				FocAppointment::whereIn('appointment_id',$request->appointment_id)->update(['complete'=>FOC_APPOINTMENT_CANCEL,"cancel_reason"=>$comment]);
				$map_appointment_id = FocAppointment::whereIn('appointment_id',$request->appointment_id)->pluck('map_appointment_id')->toArray();
				Appoinment::whereIn('appointment_id',$map_appointment_id)->update(['para_status_id'=>APPOINTMENT_CANCELLED,
					"cancel_reason"=>$comment]);
				return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_UPDATED'),"data" => '']);
			}else{
				return response()->json(["code" => 422 , "msg" =>trans('message.SOMETHING_WENT_WRONG'),"data" => '']);
			}

		}catch(\Exception $e){
			Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine());
			return response()->json(array("code" => SUCCESS,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
		}
	}

/**
 * Use      : changeAppointmentCollectionBy
 * For      : Web
 * Author   : Sachin Patel
 * Date     : 11 April,2019
 */
	public function changeAppointmentCollectionBy(Request $request){
		try{
			if((isset($request->vehicle_id) && $request->vehicle_id != "") && isset($request->appointment_id) && is_array($request->appointment_id)){
				$CollectionBy = VehicleDriverMappings::getVehicleMappedCollectionBy($request->vehicle_id);
				if($CollectionBy) {
					Appoinment::whereIn('appointment_id', $request->appointment_id)->update(['vehicle_id' => $request->vehicle_id,'collection_by'=>$CollectionBy]);
					AppointmentCollection::whereIn('appointment_id', $request->appointment_id)->update(['vehicle_id' => $request->vehicle_id,'collection_by'=>$CollectionBy]);
					return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_UPDATED'),"data" => '']);
				}else{
					return response()->json(["code" => VALIDATION_ERROR , "msg" =>trans('message.NO_COLLECTION_BY_VEHICLE'),"data" => '']);
				}
			}else{
				return response()->json(array("code" => SUCCESS,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
			}

		}catch(\Exception $e){
			Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine());
			return response()->json(array("code" => SUCCESS,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
		}
	}

	/**
	 * Use      : changeAppointmentCollectionBy
	 * For      : Web
	 * Author   : Sachin Patel
	 * Date     : 11 April,2019
	 */
	public function changeFocAppointmentCollectionBy(Request $request){
		try{
			if((isset($request->vehicle_id) && $request->vehicle_id != "") && isset($request->appointment_id) && is_array($request->appointment_id)){
				$CollectionBy = VehicleDriverMappings::getVehicleMappedCollectionBy($request->vehicle_id);
				if($CollectionBy) {
					FocAppointment::whereIn('appointment_id',$request->appointment_id)->update(['vehicle_id' => $request->vehicle_id,'collection_by'=>$CollectionBy]);
					$map_appointment_id = FocAppointment::whereIn('appointment_id',$request->appointment_id)->pluck('map_appointment_id')->toArray();
					Appoinment::whereIn('appointment_id', $map_appointment_id)->update(['vehicle_id' => $request->vehicle_id,'collection_by'=>$CollectionBy]);
					AppointmentCollection::whereIn('appointment_id', $map_appointment_id)->update(['vehicle_id' => $request->vehicle_id,'collection_by'=>$CollectionBy]);
					$validation_msg['collection_by'] = array(trans('message.RECORD_UPDATED'));
					return response()->json(["code" => SUCCESS , "msg" =>$validation_msg,"data" => '']);
				}else{
					$validation_msg['collection_by'] = array(trans('message.NO_COLLECTION_BY_VEHICLE'));
					return response()->json(["code" => 422 , "msg" =>$validation_msg,"data" => '']);
				}
			}

		}catch(\Exception $e){
			Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine());
			return response()->json(array("code" => SUCCESS,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
		}
	}

	/*
	Use     : Auto Complete Appointment
	Date    : 01 Jan,2019
	Author  : Axay Shah
	*/
	
	/*
	Use     : Auto Complete Appointment
	Date    : 01 Jan,2019
	Author  : Axay Shah
	*/
	public function AutoCompleteAppointmentOld(AppoinmentAutoAddRequest $request)
	{
		

		try{
			$collectionResult   = "";
			$FINALIZE   = "";
			$DATA               = $request->all();
			$COLLECTION_ID      = 0;
			$APPOINTMENT_ID     = 0;
			$GIVENAMOUNT        = 0;
			$APPOINTMENT_ID     = Appoinment::saveAppointment($request);
			if($APPOINTMENT_ID > 0){
				if(!empty($DATA['product'])){
					$COLLECTION = AppointmentCollection::where("appointment_id",$APPOINTMENT_ID)->first();
					if($COLLECTION){
						$COLLECTION_ID      = $COLLECTION->collection_id;
						$obj_json_format    = json_decode(json_encode($DATA['product']));
						if(!empty($obj_json_format)){
							foreach($obj_json_format as $value){
								$PRICE      = 0;
								$PRICEGROUP = CustomerMaster::where("customer_id",$request->customer_id)->value('price_group');
								if($PRICEGROUP){
									$PRICE  = CompanyProductPriceDetail::where("product_id",$value->product_id)->where('para_waste_type_id',$PRICEGROUP)->value("price");
								}
								$value->para_quality_price      = $PRICE;

								$PRICE                          = (float)$value->actual_coll_quantity * (float)$PRICE;
								$value->appointment_id          = $APPOINTMENT_ID;
								$value->collection_id           = $COLLECTION_ID;
								$value->collection_details_id   = 0;
								$GIVENAMOUNT                    = $GIVENAMOUNT + (float)$PRICE;
								$collectionResult               = AppointmentCollection::updateCollection($value,true);
							}
							// $request->request->add([

							//     'actual_coll_quantity'          => '',
							//     'appointment_id'                => $APPOINTMENT_ID,
							//     'category_id'                   => '',
							//     'city_id'                       => $COLLECTION->city_id,
							//     'collection_by'                 => $COLLECTION->collection_by,
							//     "collection_id"                 => $COLLECTION->collection_id,
							//     'company_product_quality_id'    => '',
							//     'given_amount'                  => $GIVENAMOUNT,
							//     'product_id'                    => '',
							//     'product_inert_percentage'      => '',
							//     'quantity'                      => '',
							//     'recoverable_quantity'          => '',
							//     'vehicle_id'                    => $COLLECTION->vehicle_id
							// ]);

							$REQUESTDATA['actual_coll_quantity']        = '';
							$REQUESTDATA['appointment_id']              = $APPOINTMENT_ID;
							$REQUESTDATA['category_id']                 = '';
							$REQUESTDATA['city_id']                     = $COLLECTION->city_id;
							$REQUESTDATA['collection_by']               = $COLLECTION->collection_by;
							$REQUESTDATA['collection_id']               = $COLLECTION->collection_id;
							$REQUESTDATA['company_product_quality_id']  = '';
							$REQUESTDATA['given_amount']                = $GIVENAMOUNT;
							$REQUESTDATA['product_id']                  = '';
							$REQUESTDATA['product_inert_percentage']    = '';
							$REQUESTDATA['quantity']                    = '';
							$REQUESTDATA['recoverable_quantity']        = '';
							$REQUESTDATA['vehicle_id']                  = $COLLECTION->vehicle_id;

							// $REQUESTDATA = $request->only('actual_coll_quantity', 'appointment_id', 'category_id', 'city_id','collection_by','collection_id','company_product_quality_id','given_amount','product_id','product_inert_percentage','quantity','recoverable_quantity','vehicle_id');
							$REQUESTDATA = (object)$REQUESTDATA;
							$FINALIZE   = AppointmentCollection::FinalizeCollection($REQUESTDATA,true);

						}
					}
				}
			}
			$result           = array("code" => SUCCESS,"msg"=>trans("message.RECORD_INSERTED"),"data"=> $FINALIZE);
			return response()->json($result);
		}catch(\Exception $e){


			$msg = $e->getLine()." ".$e->getMessage()." ".$e->getFile();
			Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
			return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $msg));
		}
	}
	public function AutoCompleteAppointment(Request $request)
	{
		try{
			$collectionResult   = "";
			$FINALIZE           = "";
			$customer_ons_code  = (isset($request->customer_ons_code) && !empty($request->customer_ons_code)) ? $request->customer_ons_code : "";
			$customer_code  	= (isset($request->customer_code) && !empty($request->customer_code)) ? $request->customer_code : "";
			$driver_ons_code    = (isset($request->driver_ons_code) && !empty($request->driver_ons_code)) ? $request->driver_ons_code : "";
			$city_name          = (isset($request->city_name) && !empty($request->city_name)) ? $request->city_name : "";
			$vehicle_no         = (isset($request->vehicle_no) && !empty($request->vehicle_no)) ? $request->vehicle_no : "";
			$city_id            = LocationMaster::where("city",strtolower($city_name))->value("location_id");
			$vehicle_id         = VehicleMaster::where("prge_vehicle_number",str_replace(" ","",str_replace("-"," ",$vehicle_no)))->value("vehicle_id");
			$collection_by      = AdminUser::where("net_suit_code",$driver_ons_code)->value("adminuserid");
			$customer_id        = CustomerMaster::where("code",$customer_code)->value("customer_id");
			$request->request->add(['customer_id' => $customer_id,"vehicle_id"=>$vehicle_id,"collection_by"=>$collection_by,"para_status_id"=>APPOINTMENT_COMPLETED]);
			$DATA               = $request->all();
			$COLLECTION_ID      = 0;
			$APPOINTMENT_ID     = 0;
			$GIVENAMOUNT        = 0;
			$APPOINTMENT_ID     = Appoinment::saveAppointment($request);
			if($APPOINTMENT_ID > 0){
				if(!empty($DATA['product'])){
					$COLLECTION = AppointmentCollection::where("appointment_id",$APPOINTMENT_ID)->first();
					if($COLLECTION){
						$COLLECTION_ID      = $COLLECTION->collection_id;
						$obj_json_format    = json_decode(json_encode($DATA['product']));
						if(!empty($obj_json_format)){
							foreach($obj_json_format as $value){
								$product_net_suit_code = (isset($value->product_net_suit_code) && !empty($value->product_net_suit_code)) ? $value->product_net_suit_code : "";
								$PRICE     	= 0;
								$product_id_data = CompanyProductMaster::where("net_suit_code",$product_net_suit_code)->first();
								$product_id = (isset($product_id_data->id) ? $product_id_data->id : 0);
								$category_id = (isset($product_id_data->category_id) ? $product_id_data->category_id : 0);
								$company_product_quality_id = CompanyProductQualityParameter::where("product_id",$product_id)->value("company_product_quality_id");
								$PRICEGROUP = CustomerMaster::where("customer_id",$request->customer_id)->value('price_group');
								if($PRICEGROUP){
									$PRICE  = CompanyProductPriceDetail::where("product_id",$product_id)->where('para_waste_type_id',$PRICEGROUP)->value("price");
								}
								$PRICE 							= (isset($value->price) && !empty($value->price)) ? $value->price : 0;
								$value->para_quality_price      = $PRICE;
								$value->rate      				= $PRICE;
								$PRICE                          = (float)$value->actual_coll_quantity * (float)$PRICE;
								$value->appointment_id          = $APPOINTMENT_ID;
								$value->product_id              = $product_id;
								$value->category_id             = $category_id;
								$value->company_product_quality_id = $company_product_quality_id;
								$value->collection_id           = $COLLECTION_ID;
								$value->collection_detail_id   	= 0;
								$GIVENAMOUNT                    = $GIVENAMOUNT + (float)$PRICE;
								$collectionResult               = AppointmentCollection::updateCollection($value,true);
							}
							$REQUESTDATA['price']        				= $PRICE;
							$REQUESTDATA['actual_coll_quantity']        = '';
							$REQUESTDATA['appointment_id']              = $APPOINTMENT_ID;
							$REQUESTDATA['category_id']                 = '';
							$REQUESTDATA['city_id']                     = $COLLECTION->city_id;
							$REQUESTDATA['collection_by']               = $COLLECTION->collection_by;
							$REQUESTDATA['collection_id']               = $COLLECTION->collection_id;
							$REQUESTDATA['company_product_quality_id']  = '';
							$REQUESTDATA['given_amount']                = $GIVENAMOUNT;
							$REQUESTDATA['product_id']                  = '';
							$REQUESTDATA['product_inert_percentage']    = '';
							$REQUESTDATA['quantity']                    = '';
							$REQUESTDATA['recoverable_quantity']        = '';
							$REQUESTDATA['vehicle_id']                  = $COLLECTION->vehicle_id;
							$REQUESTDATA                                = (object)$REQUESTDATA;

							$FINALIZE   = AppointmentCollection::FinalizeCollection($REQUESTDATA,true);
						}
					}
				}
			}
			$result           = array("code" => SUCCESS,"msg"=>trans("message.RECORD_INSERTED"),"data"=> $APPOINTMENT_ID);
			return response()->json($result);
		}catch(\Exception $e){


			$msg = $e->getLine()." ".$e->getMessage()." ".$e->getFile();
			Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
			return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> $msg));
		}
	}
	/*
	Use     : Update invoice details in appointment
	Date    : 13 Jan,2021
	Author  : Axay Shah
	*/
	public function UpdateAppointmentInvoiceDetails(AppointmentInvoiceUpdate $request)
	{
		$data   = Appoinment::UpdateAppointmentInvoiceDetails($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_UPDATED"),"data"=> $data));
	}
	/*
	Use     : Change Vehicle For Foc APpointment
	Date    : 14 Sepetember,2021
	Author  : Axay Shah
	*/
	public function ChangeVehicleFocAppointment(FocChangeVehicle $request){
		try{
			$data = FocAppointment::ChangeVehicleFocAppointment($request);
			if(isset($data['code']) && $data['code'] == VALIDATION_ERROR){
				return response()->json(array("code" => VALIDATION_ERROR,"msg"=>$data['msg'],"data"=>
						""));
			}
			return response()->json(['code' => SUCCESS , "msg"=>trans("message.RECORD_INSERTED"),"data"=>$data]);
		}catch(\Exception $e){
			dd($e);
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=>$e]);
		}
	}

	/*
	Use     : Mark As Paid/UnPaid Appointment
	Author  : Kalpak Prajapati
	Date    : 26 July,2022
	*/
	public function MarkAsPaidUnPaid(Request $request)
	{
		try {
			$appointment_id = isset($request->appointment_id)?$request->appointment_id:0;
			$is_paid 		= isset($request->is_paid)?$request->is_paid:0;
			$data 			= Appoinment::MarkAsPaidUnPaid($appointment_id,$is_paid);
			return response()->json(['code'=>$data['code'],"msg"=>$data['msg'],"data"=>$data['data']]);
		} catch(\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=>$e]);
		}
	}
}
