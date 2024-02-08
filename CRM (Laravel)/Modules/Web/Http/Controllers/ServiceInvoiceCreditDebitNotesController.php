<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmServiceInvoicesCreditDebitNotes;
use App\Http\Requests\ServiceGenerateCreditDebitNote;
use PDF;
class ServiceInvoiceCreditDebitNotesController extends LRBaseController
{
	/*
	Use     : Generate credit debit notes
	Author  : Hasmukhi
	Date    : 09 June,2021
	*/
	public function GenerateCreditDebitNotes(ServiceGenerateCreditDebitNote $request){
		$data       = WmServiceInvoicesCreditDebitNotes::GenerateCreditDebitNotes($request->all());
		$msg        = ($data > 0) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data > 0) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : List credit notes
	Author  : Hasmukhi
	Date    : 09 June,2021
	*/
	public function ListCreditNotes(Request $request){
		$data       = WmServiceInvoicesCreditDebitNotes::ListCreditNotes($request);
		$msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Generate credit invoice
	Author  : Hasmukhi
	Date    : 10 June,2021
	*/
	public function GenerateCreditServiceInvoice(Request $request){
		//try{
			/*$creditNoteId 	= (isset($request->credit_note_id) && !empty($request->credit_note_id)) ?  passencrypt($request->credit_note_id) : 0 ;
			$serviceId 		= (isset($request->service_id) && !empty($request->service_id)) ?  passencrypt($request->service_id) : 0 ;*/
			$creditNoteId 	= (isset($request->credit_note_id) && !empty($request->credit_note_id)) ?  passdecrypt($request->credit_note_id) : 0 ;
			$serviceId 		= (isset($request->service_id) && !empty($request->service_id)) ?  passdecrypt($request->service_id) : 0 ;
			/*print_r($creditNoteId);
			echo "<br/>";
			print_r($serviceId);*/
			$data  			= WmServiceInvoicesCreditDebitNotes::GenerateCreditInvoice($creditNoteId,$serviceId);
			$msg   			= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.NO_RECORD_FOUND");
			$pdf        	= PDF::loadView('pdf.CreditNoteServiceInvoice',compact('data'));
			$pdf->setPaper("A4", "potrait");
			$timeStemp  	= date("Y-m-d")."_".time().".pdf";
			$pdf->stream("CreditNoteServiceInvoice");
			return $pdf->stream("credit_service_invoice.pdf",array("Attachment" => false));
		/*}catch(\Exception $e){
			prd($e->getMessage());
		}*/
	}

	/*
	Use     : Approve Credit Note
	Author  : Hasmukhi
	Date    : 10 June,2021
	*/
	public function ApproveCreditNote(Request $request){
		$data       = WmServiceInvoicesCreditDebitNotes::ApproveCreditNote($request);
		$msg        = (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.NO_RECORD_FOUND");
		$code       = (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : List Service Credit Debit Note Report
	Author  : Axay Shah
	Date    : 13 September,2021
	*/
	public function CreditDebitReport(Request $request){
		$data       = WmServiceInvoicesCreditDebitNotes::ListServiceCreditDebitNoteReport($request);
		$msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = (!empty($data)) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

}
