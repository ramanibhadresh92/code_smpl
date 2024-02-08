<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\CollectionTags;
class CollectionTagController extends LRBaseController
{
    /**
     * Use      : Collection Tag List
     * Author   : Axay Shah
     * Date     : 11 Oct,2018 
     */
    public function list(Request $request)
    {
        $data = CollectionTags::collectionTagList($request);
        (count($data)>0) ?  $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }

    public function create(Request $request)
    {
        $msg    = trans('message.RECORD_INSERTED');
        $code   = SUCCESS;
        $data = CollectionTags::add($request);
        if($data == false){
            $msg    = trans('message.SOMETHING_WENT_WRONG');
            $code   = INTERNAL_SERVER_ERROR;
        }
        return response()->json(['code' => $code , "msg"=>$msg,"data"=>$data]);
    }
    public function updateRecord(Request $request)
    {
        $msg    = trans('message.RECORD_UPDATED');
        $code   = SUCCESS;
        $data = CollectionTags::updateRecord($request);
        if($data == false){
            $msg    = trans('message.SOMETHING_WENT_WRONG');
            $code   = INTERNAL_SERVER_ERROR;
        }
        return response()->json(['code' => $code , "msg"=>$msg,"data"=>$data]);
    }
    public function getById(Request $request)
    {
        $msg    = trans('message.RECORD_FOUND');
        $code   = SUCCESS;
        $data = CollectionTags::getById($request->id);
        if($data == false){
            $msg    = trans('message.SOMETHING_WENT_WRONG');
            $code   = INTERNAL_SERVER_ERROR;
        }
        return response()->json(['code' => $code , "msg"=>$msg,"data"=>$data]);
    }
}
