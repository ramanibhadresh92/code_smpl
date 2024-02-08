<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Classes\EwayBill;
class EwayBillController extends LRBaseController
{
    public function EwayAuth(Request $request){
        $ewayObj        = new EwayBill();
        $Authenticate   = $ewayObj->Authenticate();
        $data           = json_decode($Authenticate['response']);
        \Log::info("EWAYBILL AUTH RESPONSE :".print_r($Authenticate,true));
        \Log::info("EWAYBILL AUTH DECODED RESPONSE :".print_r($data,true));
        return response()->json(array("code" => SUCCESS,"msg"=>"","data"=> $data));
    }

    public function GenerateEwayBill(Request $request){
        
        $ewayObj        = new EwayBill();
        $EwayBill       = $ewayObj->GenerateEwayBill($request);
        $data           = json_decode($EwayBill['response']);
        \Log::info("EWAYBILL GENERATE RESPONSE :".print_r($EwayBill,true));
        \Log::info("EWAYBILL GENERATE DECODED RESPONSE :".print_r($data,true));
        return response()->json(array("code" => SUCCESS,"msg"=>"","data"=> $data));
    }
}
