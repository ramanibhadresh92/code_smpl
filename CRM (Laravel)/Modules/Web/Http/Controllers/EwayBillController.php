<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Classes\EwayBill;
use App\Models\WmDispatch;
use App\Models\WmTransferMaster;
use App\Models\JobWorkMaster;
use App\Models\WmInvoices;
use App\Http\Requests\EwayBillRequest;
use PDF;
class EwayBillController extends LRBaseController
{
    /*
    Use     : Generate Eway Bill from Dispatch id
    Author  : Axay Shah
    Date    : 10 December,2020
    */
    // public function GenerateEwaybillFromDispatch(Request $request){
    //     $id         = (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : "" ;
    //     $data       = WmDispatch::GenerateEwayBillFromDispatch($id);
    //     return response()->json($data);
    // }
    /*
    Use     : Generate Eway Bill from Dispatch id
    Author  : Axay Shah
    Date    : 10 December,2020
    */
    // public function GenerateEwaybillFromDispatch(Request $request){
    //     $id         = (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : "" ;
    //     $Result     = WmDispatch::GenerateEwayBillFromDispatch($id);
    //     if($Result){
    //         $invoice_id = WmInvoices::where("dispatch_id",$id)->value("id");
    //         if($invoice_id > 0){
    //             $data       = WmInvoices::GetById($invoice_id);
    //             $pdf        = PDF::loadView('pdf.one',compact('data'));
    //             $pdf->setPaper("A4", "potrait");
    //             $timeStemp  = date("Y-m-d")."_".time().".pdf";
    //             $pdf->stream("one");
    //             if(DIGITAL_SIGNATURE_FLAG == 1){
    //                 $partialPath    = PATH_DISPATCH."/".$invoice_id;
    //                 $fullPath       = public_path(PATH_IMAGE.'/').$partialPath;
    //                 $url            = url('/')."/".PATH_IMAGE.'/'.$partialPath."/invoice_".$invoice_id.".pdf";
    //                 $output         = $pdf->output();
    //                 if(!is_dir($fullPath)) {
    //                     mkdir($fullPath,0777,true);
    //                 }
    //                 file_put_contents($fullPath."/invoice_".$invoice_id.".pdf",$output);
    //                 WmDispatch::DigitalSignature($fullPath."/invoice_".$invoice_id.".pdf",$fullPath,"invoice_".$invoice_id.".pdf");
    //             }
    //         }
    //     }
    //     return response()->json($Result);
    // }
    public function GenerateEwaybillFromDispatch(Request $request){
        $id         = (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : "" ;
        $Result     = WmDispatch::GenerateEwayBillFromDispatch($id);
        if($Result){
            $invoice_id = WmInvoices::where("dispatch_id",$id)->value("id");
            if($invoice_id > 0){
                $data       = WmInvoices::GetById($invoice_id);
                $pdf        = PDF::loadView('pdf.one',compact('data'));
                $pdf->setPaper("A4", "potrait");
                $timeStemp  = date("Y-m-d")."_".time().".pdf";
                $pdf->stream("one");
                if(DIGITAL_SIGNATURE_FLAG == 1){
                    $fileName       = "invoice_".$invoice_id.".pdf";
                    $partialPath    = PATH_DISPATCH;
                    $fullPath       = public_path(PATH_IMAGE.'/'.PATH_COMPANY."/".$data['company_id']."/").$partialPath;
                    $url            = url("/".PATH_IMAGE.'/'.PATH_COMPANY."/".$data['company_id'])."/".$partialPath."/".$fileName;
                    $output         = $pdf->output();
                    if(!is_dir($fullPath)){
                        // mkdir($fullPath,0777,true);
                    }
                    file_put_contents($fullPath."/".$fileName,$output);
                    WmDispatch::DigitalSignature($fullPath."/".$fileName,$fullPath,$fileName);
                }
            }
        }
        return response()->json($Result);
    }
    /*
    Use     : Generate Eway Bill from Dispatch id
    Author  : Axay Shah
    Date    : 11 December,2020
    */
    public function CancelEwayBillResons(Request $request){
        $data       = WmDispatch::CancelEwayBillResons();
        $msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
        $code       = (!empty($data)) ?  SUCCESS : INTERNAL_SERVER_ERROR;

        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    }

    /*
    Use     : Generate Eway Bill from Dispatch id
    Author  : Axay Shah
    Date    : 11 December,2020
    */
    public function CancelEwayBill(Request $request){
        $data       = WmDispatch::CancelEwayBill($request->all());
        return response()->json($data);
    }
    /*
    Use     : Update Transpoter
    Author  : Axay Shah
    Date    : 11 December,2020
    */
    public function UpdateTranspoterData(Request $request){
        $data       = WmDispatch::UpdateTranspoterData($request->all());
        return response()->json($data);
    }
    /*
    Use     : Update Eway Bill Distance
    Author  : Axay Shah
    Date    : 10 feb,2021
    */
    public function UpdateTransDistance(Request $request){
        $DISPATCH_ID    = (isset($request->dispatch_id) && !empty($request->dispatch_id)) ?  $request->dispatch_id : 0;
        $KM             = (isset($request->trans_distance) && !empty($request->trans_distance)) ?  $request->trans_distance : 0;
        $data           = WmDispatch::UpdateTransDistance($DISPATCH_ID,$KM);
        $msg            = ($data) ? trans("message.RECORD_UPDATED") : trans("message.SOMETHING_WENT_WRONG");
        $code           = ($data) ?  SUCCESS : INTERNAL_SERVER_ERROR;
        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    }
    /*
    Use     : Generate Eway Bill from Transfer
    Author  : Axay Shah
    Date    : 19 Feb,2021
    */
    public function GenerateEwaybillFromTransfer(Request $request){
        $id         = (isset($request->transfer_id) && !empty($request->transfer_id)) ? $request->transfer_id : "" ;
        $data       = WmTransferMaster::GenerateTransferEwayBill($id);
        return response()->json($data);
    }

    /*
    Use     : Cancel Eway Bill in Transfer
    Author  : Axay Shah
    Date    : 22 Feb,2021
    */
    public function CancelTransferEwayBill(Request $request){
        $data  = WmTransferMaster::CancelEwayBill($request->all());
        return response()->json($data);
    }

    /*
    Use     : Generate Jobwork Eway Bill
    Author  : Axay Shah
    Date    : 25 Feb,2021
    */
    public function GenerateJobworkEwaybill(Request $request){
        $id    = (isset($request->jobwork_id) && !empty($request->jobwork_id)) ? $request->jobwork_id : "" ;
        $data  = JobWorkMaster::GenerateJobworkEwayBill($id);
        if($data['code']=="SUCCESS")
        {
            LR_Modules_Log_CompanyUserActionLog($request,$request->jobwork_id);
        }
        return response()->json($data);
    }

    /*
    Use     : Cancel Eway Bill in Jobwork
    Author  : Axay Shah
    Date    : 22 Feb,2021
    */
    public function CancelJobworkEwayBill(Request $request){
        $data  = JobWorkMaster::CancelJobworkEwayBill($request->all());
        return response()->json($data);
    }

    /*
    Use     : Update Eway Bill Distance for Transfer and Jobwork
    Author  : Axay Shah
    Date    : 26 feb,2021
    */
    public function UpdateTransDistanceInTransferAndJobwork(Request $request){
        $ID   = (isset($request->id) && !empty($request->id)) ?  $request->id : 0;
        $KM   = (isset($request->trans_distance) && !empty($request->trans_distance)) ?  $request->trans_distance : 0;
        $TYPE = (isset($request->module_name) && !empty($request->module_name)) ?  $request->module_name : 0;
        $data = false;
        if(!empty($ID) && $KM > 0){
            $condition = ["trans_distance"=>$KM];
            if($TYPE == MODULE_TYPE_JOBWORK){
                $update = JobWorkMaster::where("id",$ID)->update($condition);
                $data   = true;
            }elseif($TYPE == MODULE_TYPE_TRANSFER){
                $update = WmTransferMaster::where("id",$ID)->update($condition);
                $data   =  true;
            }
        }
        $msg   = ($data) ? trans("message.RECORD_UPDATED") : trans("message.SOMETHING_WENT_WRONG");
        $code  = ($data) ?  SUCCESS : INTERNAL_SERVER_ERROR;
        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    }
}
