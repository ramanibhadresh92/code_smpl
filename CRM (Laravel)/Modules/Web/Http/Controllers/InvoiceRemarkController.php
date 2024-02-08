<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\InvoiceRemarkMaster;
use App\Http\Requests\AddInvoiceRemark;
use App\Http\Requests\UpdateInvoiceRemark;
use Maatwebsite\Excel\Facades\Excel;
class InvoiceRemarkController extends LRBaseController
{
	/*
	Use     : Add Invoice Remark
	Author  : Axay Shah
	Date    : 17 November,2021
	*/
	public function AddInvoiceRemark(AddInvoiceRemark $request)
	{
		$data = InvoiceRemarkMaster::AddInvoiceRemark($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
	}

	/*
	Use     : Add Invoice Remark
	Author  : Axay Shah
	Date    : 17 November,2021
	*/
	public function UpdateInvoiceRemark(UpdateInvoiceRemark $request)
	{
		$data = InvoiceRemarkMaster::UpdateInvoiceRemark($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
	}

	/*
	Use     : Add Invoice Remark
	Author  : Axay Shah
	Date    : 17 November,2021
	*/
	public function RemarkById(Request $request)
	{
		$data = InvoiceRemarkMaster::RemarkById($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
	}

	/*
	Use     : Get Invoice Remarks
	Author  : Kalpak Prajapati
	Date    : 23 November,2021
	*/
	public function listInvoiceRemarks(Request $request)
	{
		$data = InvoiceRemarkMaster::listInvoiceRemarks($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
	}

	/*
	Use     : Get Invoice Remark Reasons
	Author  : Kalpak Prajapati
	Date    : 23 November,2021
	*/
	public function getInvoiceRemarkReasons(Request $request)
	{
		$data = InvoiceRemarkMaster::GetInvoiceRemarkReasonParameters($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
	}
}
