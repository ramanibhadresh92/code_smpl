<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmInvoices;
use App\Models\WmDispatch;
use App\Models\WmInvoicesCreditDebitNotes;
use App\Http\Requests\GenerateCreditDebitNote;
use PDF;
class InvoiceCreditDebitNotesController extends LRBaseController
{
	/*
	Use     : List Invoice Data
	Author  : Axay Shah
	Date    : 19 Octomber,2020
	*/
	public function GenerateCreditDebitNotes(GenerateCreditDebitNote $request){
		$data       = WmInvoicesCreditDebitNotes::GenerateCreditDebitNotes($request->all());
		$msg        = ($data > 0) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data > 0) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Credit Debit Report
	Author  : Axay Shah
	Date    : 19 Octomber,2020
	*/
	public function CreditDebitNoteReport(Request $request){
		$userID 	= Auth()->user()->adminuserid;
		$data       = WmInvoicesCreditDebitNotes::CreditDebitNoteReportV1($request);
		$msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Credit Debit Report
	Author  : Axay Shah
	Date    : 03 Feb,2021
	*/
	public function ListCreditNotes(Request $request){
		$data       = WmInvoicesCreditDebitNotes::ListCreditNotes($request);
		$msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Approve Credit Note
	Author  : Axay Shah
	Date    : 03 Feb,2021
	*/
	public function ApproveCreditNote(Request $request){
		$data       = WmInvoicesCreditDebitNotes::ApproveCreditNote($request);
		$msg        = (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.NO_RECORD_FOUND");
		$code       = (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Approve Credit Note
	Author  : Axay Shah
	Date    : 03 Feb,2021
	*/
	public function GenerateCreditInvoice(Request $request){
		try{
			$creditNoteId 	= (isset($request->credit_note_id) && !empty($request->credit_note_id)) ?  passdecrypt($request->credit_note_id) : 0 ;
			$invoiceId 		= (isset($request->invoice_id) && !empty($request->invoice_id)) ?  passdecrypt($request->invoice_id) : 0 ;
			$data  			= WmInvoicesCreditDebitNotes::GenerateCreditInvoice($creditNoteId,$invoiceId);
			$msg   			= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.NO_RECORD_FOUND");
			$pdf        	= PDF::loadView('pdf.CreditNoteInvoice',compact('data'));
			$pdf->setPaper("A4", "potrait");
			$timeStemp  	= date("Y-m-d")."_".time().".pdf";
			$pdf->stream("CreditNoteInvoice");
			return $pdf->stream("credit_invoice.pdf",array("Attachment" => false));
		}catch(\Exception $e){
			prd($e->getMessage());
		}
	}
	/*
	Use     : Update e invoice no
	Author  : Axay Shah
	Date 	: 06 May,2021
	*/
	public function UpdateEinvoiceNo(Request $request){
		$id 		= (isset($request->id) && !empty($request->id)) ? $request->id : "";
		$irn 		= (isset($request->irn) && !empty($request->irn)) ? $request->irn : "";
		$ack_date 	= (isset($request->ack_date) && !empty($request->ack_date)) ? $request->ack_date : "";
		$ack_no 	= (isset($request->ack_no) && !empty($request->ack_no)) ? $request->ack_no : "";
		$data 		= WmInvoicesCreditDebitNotes::UpdateEinvoiceNo($id,$irn,$ack_no,$ack_date);
		$msg 		= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		$code 		= ($data) ? SUCCESS : SUCCESS;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Approve Credit Note
	Author  : Axay Shah
	Date    : 03 Feb,2021
	*/
	public function GetFirstLevelApprovalUserList(Request $request){
		$mrf_id 		= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		$from_purchase 	= (isset($request->from_purchase) && !empty($request->from_purchase)) ? $request->from_purchase : 0;
		$dispatch_id 	= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : 0;
		if(!empty($dispatch_id)){
			$mrf_id = WmDispatch::where("id",$dispatch_id)->value("bill_from_mrf_id");
		}
		$data       	= WmInvoicesCreditDebitNotes::GetFirstLevelApprovalUserList($mrf_id,$from_purchase);
		$msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.NO_RECORD_FOUND");
		$code       = (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
}
