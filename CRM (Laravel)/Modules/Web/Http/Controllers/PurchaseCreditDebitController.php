<?php
namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\PurchaseCreditDebitNoteMaster;
use App\Http\Requests\PurchaseCreditDebitNote;
use Validator;
use PDF;
use Excel;
class PurchaseCreditDebitController extends LRBaseController
{

	/*
	Use     : getSaleProductByPurchaseProduct
	Author  : Axay Shah
	Date 	: 29 May,2019
	*/
	public function List(Request $request){
		$data 		= PurchaseCreditDebitNoteMaster::PurchaseCreditDebitNoteList($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get By ID
	Author  : Axay Shah
	Date 	: 26 June,2019
	*/
	public function GetById(Request $request){
		$id 		= (isset($request->id) && !empty($request->id)) ? $request->id : 0 ;
		$data 		= PurchaseCreditDebitNoteMaster::GetById($id,true);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : List Client for sales module & serach filter
	Author  : Axay Shah
	Date 	:
	*/
	public function Create(PurchaseCreditDebitNote $request){
		$data 	= PurchaseCreditDebitNoteMaster::GeneratePurchaseCreditDebitNote($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : List Client for sales module
	Author  : Axay Shah
	Date 	:
	*/
	public function ChangeInDropDown(Request $request){
		$data 	= APPOINTMENT_COLLECTION_CREDIT_DEBIT;
		$result = array();
		if(!empty($data)){
			$i = 0;
			foreach($data as $key=>$value){
				$result[$i]["value"] = $key;
				$result[$i]["label"] = $value;
				$i++;
			}
		}
		$msg 	= (!empty($result)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$result]);
	}

	/*
	Use     : Approve Credit Note
	Author  : Hasmukhi
	Date    : 10 June,2021
	*/
	public function ApprovePurchaseCreditDebitNote(Request $request){
		$data       = PurchaseCreditDebitNoteMaster::ApproveCreditDebitNote($request);
		$msg        = (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.NO_RECORD_FOUND");
		$code       = (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Approve Credit Note
	Author  : Axay Shah
	Date    : 03 Feb,2021
	*/
	public function GenerateCreditDebitInvoice(Request $request){
		//try{
			$creditNoteId 	= (isset($request->credit_note_id) && !empty($request->credit_note_id)) ?  passdecrypt($request->credit_note_id) : 0 ;
			$batchId 		= (isset($request->batch_id) && !empty($request->batch_id)) ?  passdecrypt($request->batch_id) : 0 ;
			$data  			= PurchaseCreditDebitNoteMaster::GenerateCreditDebitInvoice($creditNoteId,$batchId);
			$msg   			= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.NO_RECORD_FOUND");
			$pdf        	= PDF::loadView('pdf.PurchaseCreditNoteInvoice',compact('data'));
			$pdf->setPaper("A4", "potrait");
			$timeStemp  	= date("Y-m-d")."_".time().".pdf";
			$pdf->stream("PurchaseCreditNoteInvoice");
			return $pdf->stream("credit_invoice.pdf",array("Attachment" => false));
		/*}catch(\Exception $e){
			prd($e->getMessage());
		}*/
	}

	/*
	Use     : Credit Debit note report
	Author  : Hasmukhi
	Date    : 05 Oct,2021
	*/
	public function CreditDebitNoteReport(Request $request){
		$data       = PurchaseCreditDebitNoteMaster::CreditDebitNoteReport($request);
		$msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Credit Debit note report
	Author  : Kalpak Prajapati
	Date    : 22 March,2023
	*/
	public function BulkApproveCreditDebitNote(Request $request)
	{
		$data       = PurchaseCreditDebitNoteMaster::BulkApproveCreditDebitNote($request);
		$msg        = (!empty($data)) ? "Selected records approved successfully." : trans("message.SOMETHING_WENT_WRONG");
		$code       = (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
}
