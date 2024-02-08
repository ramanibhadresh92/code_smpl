<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Validator;
use App\Models\AdminUser;
use App\Models\Company;
use App\Models\AdminTransactionGroups;
use App\Models\AdminTransaction;
use App\Models\AdminUserRights;
use App\Models\GroupMaster;
use App\Models\GroupRightsTransaction;
use App\Models\AdminTransactionTrainingDetail;
use App\Facades\LiveServices;
use DB;
use Mail;
class AdminTransactionTrainingController extends LRBaseController
{
	/* 
    *   Use     :   List Training Media List
    *   Author  :   Hardyesh Gupta
    *   Date    :   22 Sep,2023
    */
    public function AdminTransactionList(Request $request)
    { 
        $data   = AdminTransaction::AdminTransactionList($request);
        $msg    = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);     
    }

    /* 
    *   Use     :   List For Admin Transaction Menu For Training Detail
    *   Author  :   Hardyesh Gupta
    *   Date    :   22 Sep,2023
    */
    public function AdminTransactionTrainingMenuList(Request $request)
    { 
        $data   = AdminTransactionTrainingDetail::AdminTransactionTrainingMenuList($request);
        $msg    = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);     
    }

    /* 
    *   Use     :   Add/Insert Training Detail
    *   Author  :   Hardyesh Gupta
    *   Date    :   22 Sep,2023
    */
    public function AddTrainingData(Request $request)
    { 
        $data   = AdminTransactionTrainingDetail::AddTrainingData($request);
        $msg    = (($data == true)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);     
    }

    /* 
    *   Use     :   List Training Media List
    *   Author  :   Hardyesh Gupta
    *   Date    :   22 Sep,2023
    */
    public function AdminTransactionTrainingMediaList(Request $request)
    { 
        $data   = AdminTransactionTrainingDetail::AdminTransactionTrainingMediaList($request);
        $msg    = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);     
    }

}
