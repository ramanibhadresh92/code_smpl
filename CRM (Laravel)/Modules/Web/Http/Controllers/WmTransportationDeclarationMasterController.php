<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests\AddTransportationDeclaration;
use App\Models\WmTransportationDeclarationMaster;
use JWTFactory;
use JWTAuth;
use File;
use Storage;
use Input;
use DB;
use Excel;
use PDF;
class WmTransportationDeclarationMasterController extends LRBaseController
{
	/*
	Use     : List Transportation Declaration Master 
	Author  : Hardyesh Gupta
	Date    : 10 Feb,2021
	*/
	public function ListTransportationDeclarationMaster(Request $request){
		$data 	= WmTransportationDeclarationMaster::getTransportationMasterList($request);
		$msg  	= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		$code 	= SUCCESS;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Create Transporration Declaration
	Author  : Hardyesh Gupta
	Date    : 10 Feb,2021
	*/
	public function createTransportationDeclaration(AddTransportationDeclaration $request){
		$data 		= WmTransportationDeclarationMaster::createTransportationDeclarationMaster($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$code 		= (!empty($data)) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Generate PDF for BillT Declaration
	Author  : Hardyesh Gupta
	Date    : 10 Feb,2021
	*/
	public function generateTransportationBillTDeclarationPDF(Request $request)
	{
		$id = (isset($request->id) && !empty($request->id)) ?  decode($request->id) : 0 ;
		$data 	= WmTransportationDeclarationMaster::generateBillTDeclarationPDF($id);
		$PDF 	= PDF::loadView('pdf.declaration-billt',$data);
		$PDF->setPaper("letter","A4");
		return $PDF->stream("declaration.pdf");
	}
}
