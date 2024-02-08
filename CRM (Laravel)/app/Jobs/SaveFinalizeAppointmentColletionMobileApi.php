<?php

namespace App\Jobs;

use App\Models\AdminUser;
use App\Models\Appoinment;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use App\Models\AppointmentTimeReport;
use App\Models\CustomerMaster;
use App\Models\InertDeduction;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Auth;
use JWTAuth;

class SaveFinalizeAppointmentColletionMobileApi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $request;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ErrorFlag				= false;
        $request                = new Request();
        $pendingCollRequestArr = Appoinment::getPendingCollectionRequest();
        if($pendingCollRequestArr){
            foreach ($pendingCollRequestArr as $requestId => $requestData){
                $status             = 2;
                $request_respose    = '';
                Appoinment::updateCollectionRequestStatus($status,$request_respose,$requestData->id);
                $result = json_decode($requestData->request_data);
                $adminuser = AdminUser::find($result->adminuserid);

                if($adminuser) {
                    Auth::guard('web')->login($adminuser);
                }

                $request->appointment_id = $result->appointment_id;
                /*UPDATE APPOINTMENT MEDIATOR COMPLETE STATUS*/
                if(isset($result->app_type) && ($result->app_type == APP_TYPE_GODOWN || $result->app_type == APP_TYPE_CUSTOMER_GROUP)) {
                    $request->app_mediator_id   = $result->appointment_id;
                    $request->updated_by        = (Auth::check()) ? Auth::user()->adminuserid : 1;
                    $requestResult              = Appoinment::updateAppointmentMediatorCompleteStatus($request);
                    if($requestResult){
                        $status = 1;
                        $request_respose = 'success';
                        Appoinment::updateCollectionRequestStatus($status,$request_respose,$requestData->id);
                        continue;
                    }else{
                        $status = 0;
                        $request_respose = 'Error';
                        Appoinment::updateCollectionRequestStatus($status,$request_respose,$requestData->id);
                        continue;
                    }
                }

                /*UPDATE APPOINTMENT MEDIATOR COMPLETE STATUS*/
                $resultArray = json_decode($result->Data);
                if (isset($resultArray) && count($resultArray) > 0){
                    $appointment = Appoinment::find($result->appointment_id);

                    if($appointment->para_status_id == APPOINTMENT_SCHEDULED || $appointment->para_status_id == APPOINTMENT_RESCHEDULED){
                        $appointmentCollection = AppointmentCollection::retrieveCollectionByAppointment($appointment->appointment_id);

                        $requestResult = Appoinment::validateSaveCollectionRequest($requestData->appointment_id);

                        /*Save Collection Request*/
                        Appoinment::saveCollectionRequest($request);
                        /*Save Collection Request*/

                        foreach ($resultArray as $collectionRow){
                            if($collectionRow->adminuserid){
                                $adminuser = AdminUser::find($collectionRow->adminuserid);
                                if($adminuser) {
                                    Auth::guard('web')->login($adminuser);
                                }
                            }
                            $request->collection_id              = $appointmentCollection->collection_id;
                            $request->created_by                 = isset($collectionRow->adminuserid) ? $collectionRow->adminuserid : Auth::user()->adminuserid;
                            $request->updated_by                 = isset($collectionRow->adminuserid) ? $collectionRow->adminuserid : Auth::user()->adminuserid;
                            $request->quantity                   = isset($collectionRow->quantity) ? $collectionRow->quantity : '';
                            $request->actual_coll_quantity       = isset($collectionRow->actual_coll_quantity) ? $collectionRow->actual_coll_quantity : '';
                            $request->no_of_bag                  = isset($collectionRow->no_of_bag) ? $collectionRow->no_of_bag : '';
                            $request->category_id                = isset($collectionRow->category_id) ? $collectionRow->category_id : '';
                            $request->product_id                 = isset($collectionRow->product_id) ? $collectionRow->product_id : '';
                            $request->product_quality_para_id    = isset($collectionRow->product_quality_para_id) ? $collectionRow->product_quality_para_id : '';
                            $request->company_product_quality_id = isset($collectionRow->product_quality_para_id) ? $collectionRow->product_quality_para_id : '';
                            $request->collection_log_date        = isset($collectionRow->collection_log_date) ? $collectionRow->collection_log_date : '';
                            $request->product_collection_date    = isset($collectionRow->product_collection_date) ? $collectionRow->product_collection_date : '';
                            $request->collection_detail_id       = 0;
                            $validate = AppointmentCollectionDetail::validateEditCollectionRequest($request);

                            if(empty($validate)){
                                $collectionDetail        = AppointmentCollectionDetail::saveCollectionDetails($request);
                                $request->amount         = ($request->actual_coll_quantity * $request->para_quality_price);
                                $request->given_amount   = isset($result->given_amount) ? $result->given_amount : 0;
                                AppointmentCollection::UpdateCollectionTotal($request);

                            } else {
                                $error 		= $validate[0];
                                $ErrorFlag	= true;
                            }
                        }


                    }else{

                        $status = 1;
                        $request_respose = 'success';
                        Appoinment::updateCollectionRequestStatus($status,$request_respose,$requestData->id);
                        continue;
                    }

                    if ($ErrorFlag == false) {
                        $inert_deducted_amount 	= isset($result->inert_deducted_amount)?$result->inert_deducted_amount:0;
                        $reached_time 			= isset($result->reached_time)?$result->reached_time:trim(isset($result->collection_dt)?$result->collection_dt:"0000-00-00 00:00:00");
                        AppointmentCollection::SaveCustomerBalanceAmount($appointmentCollection->collection_id,$result->customer_id,$inert_deducted_amount,$request);
                        InertDeduction::UpdateInertDeductionAmount($request->appointment_id,$result->customer_id,$inert_deducted_amount,$request);
                        AppointmentCollection::FinalizeCollection($request);

                        //UPDATE APPOINTMENT LATTITUDE & LONGITUDE
                        $request->longitude = trim(isset($result->app_lon)?$result->app_lon:"");
                        $request->lattitude = trim(isset($result->app_lat)?$result->app_lat:"");
                        Appoinment::updateAppointmentLatLong($request);

                        AppointmentTimeReport::AppointmentCollectionDone($request->appointment_id);

                        $request->starttime = isset($result->reached_time) ? $result->reached_time : "0000-00-00 00:00:00";
                        AppointmentTimeReport::updateAppointmentReachedTime($request);

                        $customer = CustomerMaster::find($result->customer_id);
                        $status = 1;
                        $request_respose = 'Success';
                        Appoinment::updateCollectionRequestStatus($status,$request_respose,$requestData->id);
                    }

                }else{
                    return response()->json(array("code" => ERROR,"msg"=>trans("message.ERROR_SAVE_COLLECTION"),"data"=>''));
                }

                if ($ErrorFlag) {
                    $request_status = 0;
                    $request_respose = 'Error';
                    Appoinment::updateCollectionRequestStatus($request_status,$request_respose,$requestData->id);
                }
            }
        }



    }
}
