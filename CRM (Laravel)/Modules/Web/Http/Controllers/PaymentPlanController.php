<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;


use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\PurchaseInvoicePaymentPlanMaster;
use App\Models\PurchaseInvoicePaymentPlanDetailMaster;
use App\Models\Parameter;
use Log,DB;
use App\Exports\PaymentPlanCSV;
use App\Exports\HdfcPaymentPlanCSV;
use Excel;
use ZipArchive;
use File;
class PaymentPlanController extends LRBaseController
{
    /**
     * Use      : add payment plan
     * Author   : Axay Shah
     * Date     : 14 Sepetember,2021
    */
    public function AddPurchaseInvoicePaymentPlan(Request $request)
    {
        $msg    =   trans('message.RECORD_UPDATED');
        $data   = PurchaseInvoicePaymentPlanMaster::AddPurchaseInvoicePaymentPlan($request);
        return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => $data]);  
    }

     /**
     * Use      : payment plan list
     * Author   : Axay Shah
     * Date     : 18 Sepetember,2021
    */
    public function ListPaymentPlan(Request $request)
    {
        $msg    =   trans('message.RECORD_UPDATED');
        $data   = PurchaseInvoicePaymentPlanMaster::ListPaymentPlan($request);
        return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => $data]);  
    }
     /**
     * Use      : payment plan list
     * Author   : Axay Shah
     * Date     : 18 Sepetember,2021
    */
    public function PaymentPlanPrioriyDropDown(Request $request)
    {
        $msg    =  trans('message.RECORD_UPDATED');
        $data   = Parameter::parentDropDown(PARA_PAYMENT_PLAN_PRIORITY)->get();
        return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => $data]);  
    }
    /**
     * Use      : payment plan list
     * Author   : Axay Shah
     * Date     : 18 Sepetember,2021
    */
    public function UpdatePaymentPlanPriority(Request $request)
    {
         $msg =  trans('message.RECORD_UPDATED');
        $data = PurchaseInvoicePaymentPlanDetailMaster::UpdatePaymentPlanPriority($request);
        return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => $data]);  
    }
     /**
     * Use      : payment plan list
     * Author   : Axay Shah
     * Date     : 18 Sepetember,2021
    */
    public function GeneratePaymentPlanCSV(Request $request)
    {
        $data = PurchaseInvoicePaymentPlanDetailMaster::GeneratePaymentPlanCSV($request);
         $msg =  trans('message.RECORD_UPDATED');
        return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => $data]);  
    }
    /**
     * Use      : payment plan list
     * Author   : Axay Shah
     * Date     : 18 Sepetember,2021
    */
    // public function DownloadPaymentPlanCSV($id)
    // {
    //     $id     = passdecrypt($id);
    //     if($id > 0){
    //         $priorityArray  = Parameter::parentDropDown(PARA_PAYMENT_PLAN_PRIORITY)->get();
    //         if(!empty($priorityArray)){
    //             $fileNameArray  = array();
    //             $ZipFileName    = $id."_".date("Y-m-d")."_".date("H:i:s").".zip";
    //             $ZipFilePath    = "/payment_plan";
    //             $ZipURL         = "";
    //             if(!file_exists(public_path("/".$ZipFilePath))) {
    //                 mkdir(public_path("/".$ZipFilePath));
    //             }
    //             foreach($priorityArray as $key => $value){
    //                 $data  = PurchaseInvoicePaymentPlanDetailMaster::DownloadPaymentPlanCSV($id,$value['para_id']);
    //                 if(!empty($data)){
    //                     $GetData = PurchaseInvoicePaymentPlanMaster::where("process_no",$id)->first();
    //                     if($GetData){
    //                         $GetData->file_name = $ZipFileName;
    //                         $GetData->file_path = $ZipFilePath;
    //                         if($GetData->save()){
    //                             $ZipURL =  url("/")."/".$ZipFilePath."/".$ZipFileName;
    //                         }
    //                     }
    //                     PurchaseInvoicePaymentPlanMaster::where("process_no",$id)->update(["file_name" => $ZipFileName,"file_path"=>$ZipFilePath]);

    //                     $zip    = new ZipArchive;
    //                     if ($zip->open(public_path($ZipFilePath."/".$ZipFileName), ZipArchive::CREATE) === TRUE)
    //                     {
    //                         $FileName   = $id."_".$value['para_value']."_Payment_Plan.xlsx";
    //                         $ExcelPath  = "payment_plan/".$FileName;
    //                         array_push($fileNameArray,$ExcelPath);
    //                         Excel::store(new PaymentPlanCSV($data),$FileName,'payment_plan');
    //                         $zip->addFile(public_path($ZipFilePath."/".$FileName),$FileName);
    //                     }
    //                     $zip->close();
    //                 }
    //             }
    //             if(!empty($fileNameArray)){
    //                 foreach($fileNameArray as $key => $value){
    //                     if(File::exists(public_path("/".$value))){
    //                        File::delete(public_path("/".$value));
    //                     }
    //                 }
    //             }
    //         } 
    //     } 
    //     $msg =  "Payment Plan Generated Successfully";
        
    //     return response()->json(['code' => (int)SUCCESS, 'msg' => $msg, 'data' => $ZipURL]);  
    // }
    public function DownloadPaymentPlanCSV($id)
    {
        $id     = passdecrypt($id);
        if($id > 0){
            $priorityArray  = Parameter::parentDropDown(PARA_PAYMENT_PLAN_PRIORITY)->get();
            if(!empty($priorityArray)){
                $fileNameArray  = array();
                $ZipFileName    = $id."_".date("Y-m-d")."_".date("H:i:s").".zip";
                $ZipFilePath    = "/payment_plan";
                $ZipURL         = "";
                if(!file_exists(public_path("/".$ZipFilePath))) {
                    mkdir(public_path("/".$ZipFilePath));
                }
                foreach($priorityArray as $key => $value){
                    $data  = PurchaseInvoicePaymentPlanDetailMaster::DownloadPaymentPlanCSV($id,$value['para_id']);
                    if(!empty($data)){
                        $GetData    = PurchaseInvoicePaymentPlanMaster::where("process_no",$id)->first();
                        $bankId     = 0;
                        if($GetData){
                            $bankId             = $GetData->bank_id;
                            $GetData->file_name = $ZipFileName;
                            $GetData->file_path = $ZipFilePath;
                            if($GetData->save()){
                                $ZipURL =  url("/")."/".$ZipFilePath."/".$ZipFileName;
                            }
                        }
                        PurchaseInvoicePaymentPlanMaster::where("process_no",$id)->update(["file_name" => $ZipFileName,"file_path"=>$ZipFilePath]);

                        $zip    = new ZipArchive;
                        if ($zip->open(public_path($ZipFilePath."/".$ZipFileName), ZipArchive::CREATE) === TRUE)
                        {
                            $FileName   = $id."_".$value['para_value']."_Payment_Plan.xlsx";
                            $ExcelPath  = "payment_plan/".$FileName;
                            array_push($fileNameArray,$ExcelPath);
                           
                            switch($bankId > 0){
                                case 1 : 
                                    Excel::store(new PaymentPlanCSV($data),$FileName,'payment_plan');
                                    break;
                                case 2 : 
                                    Excel::store(new HdfcPaymentPlanCSV($data),$FileName,'payment_plan');
                                    break;
                                default : 
                                    Excel::store(new HdfcPaymentPlanCSV($data),$FileName,'payment_plan');
                                    break;
                            }
                            Excel::store(new HdfcPaymentPlanCSV($data),$FileName,'payment_plan');
                            $zip->addFile(public_path($ZipFilePath."/".$FileName),$FileName);
                        }
                        $zip->close();
                    }
                }
                if(!empty($fileNameArray)){
                    foreach($fileNameArray as $key => $value){
                        if(File::exists(public_path("/".$value))){
                           File::delete(public_path("/".$value));
                        }
                    }
                }
            } 
        } 
        $msg =  "Payment Plan Generated Successfully";
        
        return response()->json(['code' => (int)SUCCESS, 'msg' => $msg, 'data' => $ZipURL]);  
    }
     /**
     * Use      : Paid or Approved payment Plan Report
     * Author   : Axay Shah
     * Date     : 14 Sepetember,2021
    */
    public function GetPaymentPlanReport(Request $request)
    {
        $msg =  trans('message.RECORD_UPDATED');
        $data = PurchaseInvoicePaymentPlanDetailMaster::GetPaymentPlanReport($request);
        return response()->json(['code' => SUCCESS, 'msg' => $msg, 'data' => $data]);  
    }
    
}



