<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\ViewRequestApproval;
use App\Models\RequestApproval;
use App\Http\Requests\RequestApprovalByAdmin;
use App\Models\CompanyProductPriceDetailsApproval;
use App\Models\ShiftTimingApprovalMaster;
class RequestApprovalController extends LRBaseController
{
    /*
    Use     : List request approval
    Author  : Axay Shah
    Date    : 02 Nov,2018
    */

    public function list(Request $request){
        $list = ViewRequestApproval::list($request);
        ($list) ?  $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$list]);
    }
    /*
    Use     : request Get By Id
    Author  : Axay Shah
    Date    : 02 Nov,2018
    */

    public function getById(Request $request){
        $list =  ViewRequestApproval::getById($request);
        ($list) ?  $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$list]);
    }

    /*
    Use     : Request approve by admin
    Author  : Axay Shah
    Date    : 06 Nov,2018
    */

    public function requestActionByAdmin(RequestApprovalByAdmin $request){
        return RequestApproval::requestActionByAdmin($request);
    }
    
    /*
    Use     : Request approve by Email
    Author  : Sachin Patel
    Date    : 04 May, 2019
    */

    public function requestActionByEmail(Request $request){
        return RequestApproval::requestActionByEmail($request);
    }

    /*
    Use     : List Request Approval for price group of customer
    Author  : Axay Shah
    Date    : 02 Nov,2018
    */

    public function ListPriceGroupApproval(Request $request){
        $list = CompanyProductPriceDetailsApproval::ListPriceGroupApproval($request);
        ($list) ?  $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$list]);
    }

    /*
    Use     : Get By Track Id
    Author  : Axay Shah
    Date    : 18 June 2019
    */

    public function getByTrackId(Request $request){
        $trackID    = (isset($request->track_id) && !empty($request->track_id)) ? $request->track_id : 0 ;  
        $list       = CompanyProductPriceDetailsApproval::getByTrackId($trackID);
        ($list) ?  $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$list]);
    }


    /*
    Use     : Approve Price Group 
    Author  : Axay Shah
    Date    : 19 June 2019
    */

    public function ApprovePriceGroup(Request $request){
        $isDefault      = "N";
        $TrackId        = (isset($request->track_id)            && !empty($request->track_id))              ? $request->track_id            : 0;
        $PriceGroupId   = (isset($request->para_waste_type_id)  && !empty($request->para_waste_type_id))    ? $request->para_waste_type_id  : 0;
        $ApproveStatus  = (isset($request->approve_status)      && !empty($request->approve_status))        ? $request->approve_status      : 0;
        if($TrackId > 0){
            $isDefaultData = CompanyProductPriceDetailsApproval::where("track_id",$TrackId)->first();
            if($isDefaultData){
                $isDefault = $isDefaultData->is_default;
            }
        }
        $list           = CompanyProductPriceDetailsApproval::ApprovePriceGroup($TrackId,$PriceGroupId,$ApproveStatus,$isDefault);
        $msg            = (!empty($list)) ?  trans('message.PRICE_GROUP_UPDATED') : trans('message.RECORD_NOT_FOUND');
        if(!empty($list)){
            LR_Modules_Log_CompanyUserActionLog($request,$PriceGroupId);   
        }
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$list]);
    }

    /*
    Use     : List Request Approval for price group of customer
    Author  : Axay Shah
    Date    : 04 May,2020
    */

    public function ShiftApprovalList(Request $request){
        $list = ShiftTimingApprovalMaster::ListShiftTimingApproval($request);
        ($list) ?  $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$list]);
    }

     /*
    Use     : Approve Price Group 
    Author  : Axay Shah
    Date    : 04 May,2020
    */

    public function ApproveShiftTiming(Request $request){
        $TrackId        = (isset($request->id) && !empty($request->id)) ? $request->id : 0;
        $ApproveStatus  = (isset($request->status) && !empty($request->status)) ? $request->status : 0;
        $list  = ShiftTimingApprovalMaster::ApproveShiftTiming($TrackId,$ApproveStatus);
        $msg   = ($list) ?  trans('message.SHIFT_APPROVED') : trans('message.RECORD_NOT_FOUND');
        if($list){
            LR_Modules_Log_CompanyUserActionLog($request,$request->id);
        }
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$list]);
    }
}
