<?php
namespace App\Http\Controllers;
use App\Http\Controllers\LRBaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\WmClientPurchaseOrders;
use App\Models\WmClientPurchaseOrderPlanning;
use App\Http\Requests\AddPurchaseOrder;
use App\Http\Requests\UpdatePurchaseOrder;
use App\Http\Requests\ManagePurchaseOrderSchedule;
use Validator;
class ClientPurchaseOrdersController extends LRBaseController
{
	/*
	Use     : Get Client Purchase Orders
	Author  : Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public function getClientPurchaseOrder(Request $request)
	{
		$data 	= WmClientPurchaseOrders::GetClientPurchaseOrder($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get Client Purchase Order Details
	Author  : Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public function getClientPurchaseOrderDetails(Request $request)
	{
		$data 	= WmClientPurchaseOrders::GetPurchaseOrderDetails($request,true);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		$code 	= (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Add Client Purchase Order Details
	Author  : Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public function addPurchaseOrder(AddPurchaseOrder $request)
	{
		$data 	= WmClientPurchaseOrders::AddPurchaseOrder($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.VALIDATION_ERROR");
		$code 	= (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Client Purchase Order Details
	Author  : Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public function updatePurchaseOrder(UpdatePurchaseOrder $request)
	{
		$data 	= WmClientPurchaseOrders::UpdatePurchaseOrder($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.VALIDATION_ERROR");
		$code 	= (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Approve Client Purchase Order Details
	Author  : Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public function approvePurchaseOrder(Request $request)
	{
		$data 	= WmClientPurchaseOrders::ApprovePurchaseOrder($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.VALIDATION_ERROR");
		$code 	= (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Reject Client Purchase Order Details
	Author  : Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public function rejectPurchaseOrder(Request $request)
	{
		$data 	= WmClientPurchaseOrders::RejectPurchaseOrder($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.VALIDATION_ERROR");
		$code 	= (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Cancel Client Purchase Order Details
	Author  : Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public function cancelPurchaseOrder(Request $request)
	{
		$data 	= WmClientPurchaseOrders::CancelPurchaseOrder($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.VALIDATION_ERROR");
		$code 	= (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Stop Client Purchase Order Details
	Author  : Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public function stopPurchaseOrder(Request $request)
	{
		$data 	= WmClientPurchaseOrders::StopPurchaseOrder($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.VALIDATION_ERROR");
		$code 	= (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Restart Client Purchase Order Details
	Author  : Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public function restartPurchaseOrder(Request $request)
	{
		$data 	= WmClientPurchaseOrders::RestartPurchaseOrder($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.VALIDATION_ERROR");
		$code 	= (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Manage Purchase Order Schedule
	Author  : Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public function managePurchaseOrderSchedule(ManagePurchaseOrderSchedule $request)
	{
		$data 	= WmClientPurchaseOrderPlanning::ManagePurchaseOrderSchedule($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.VALIDATION_ERROR");
		$code 	= (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
}
