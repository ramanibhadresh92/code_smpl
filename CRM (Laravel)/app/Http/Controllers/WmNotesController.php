<?php

namespace App\Http\Controllers;
use App\Http\Controllers\LRBaseController;
use Illuminate\Http\Request;
use App\Models\AdminUser;
use App\Models\AdminUserRights;
use App\Models\WmInvoicesCreditDebitNotes;
use App\Models\GroupRightsTransaction;
use App\Models\PurchaseCreditDebitNoteMaster;
use App\Models\Appoinment;
use JWTFactory;
use JWTAuth;
use Validator;
use Response;
use File;
use Storage;
use Input;
use DB;

class WmNotesController extends LRBaseController
{
	private function SetVariables($request)
	{
		
	}

	private function ChangeNoteStatus($NoteType, $Status, $credit_note_id="",$invoice_id="",$approved_by="",$Level="")
	{
		$CreditNoteID 	= decode($credit_note_id);
		$InvoiceID 		= decode($invoice_id);
		$ApprovedBy 	= decode($approved_by);
		$AdminUser 		= AdminUser::where("adminuserid",$ApprovedBy)->where("status","A")->first();
		
		if (!empty($AdminUser) && !empty($Level))
		{
			$WmInvoicesCreditDebitNotes = WmInvoicesCreditDebitNotes::find($CreditNoteID);
			if ($WmInvoicesCreditDebitNotes->status == 3) {
				echo "<br /><center>$NoteType note is already approved.</center>";
			} else if ($WmInvoicesCreditDebitNotes->status == 2) {
				echo "<br /><center>$NoteType note is already rejected.</center>";
			} else {
				if (strtolower($Level) == "first" && $WmInvoicesCreditDebitNotes->first_level_approved_by != $ApprovedBy) {
					echo "<br /><center>You're not authorize user to access this page.</center>";
				} else if ($WmInvoicesCreditDebitNotes->id > 0 && $WmInvoicesCreditDebitNotes->status != $Status && $WmInvoicesCreditDebitNotes->status != 2) {
					if (strtolower($Level) != "first") {
						$AdminUserRights = AdminUserRights::where("adminuserid",$ApprovedBy)->where("trnid",SALES_CN_DN_FINAL_LEVEL_APPROVAL)->first();

						$AdminUserRights = GroupRightsTransaction::where("trn_id",SALES_CN_DN_FINAL_LEVEL_APPROVAL)->where("group_id",$AdminUser->user_type)->count();
					} else {
						$AdminUserRights = AdminUserRights::where("adminuserid",$ApprovedBy)->where("trnid",SALES_CN_DN_FIRST_LEVEL_APPROVAL)->first();
						$AdminUserRights = GroupRightsTransaction::where("trn_id",SALES_CN_DN_FIRST_LEVEL_APPROVAL)->where("group_id",$AdminUser->user_type)->count();
					}
					if ($AdminUserRights) {
						$arrData = array("status"=>$Status,"id"=>$CreditNoteID);
						WmInvoicesCreditDebitNotes::ApproveCreditNote($arrData,$ApprovedBy,$AdminUser->company_id);
						if ($Status == 2) {
							echo "<br /><center>$NoteType note is rejected successfully.</center>";
						} else {
							echo "<br /><center>$NoteType note is approved successfully.</center>";
						}
					} else {
						echo "<br /><center>You're not authorize user to access this page.</center>";
					}
				} else if ($WmInvoicesCreditDebitNotes->id > 0) {
					if ($WmInvoicesCreditDebitNotes->status == 1 || $WmInvoicesCreditDebitNotes->status == 3) {
						echo "<br /><center>$NoteType note is already approved.</center>";
					} else {
						echo "<br /><center>$NoteType note is already rejected.</center>";
					}
				} else {
					echo "<br /><center>Invalid Request !!!</center>";
				}
			}
		} else {
			echo "<br /><center>You're not authorize user to access this page.</center>";
		}
	}

	public function approveFirstDebitNote(Request $request,$credit_note_id="",$invoice_id="",$approved_by="")
	{
		$this->ChangeNoteStatus("Debit",1,$credit_note_id,$invoice_id,$approved_by,"first");
	}

	public function rejectFirstDebitNote(Request $request,$credit_note_id="",$invoice_id="",$approved_by="")
	{
		$this->ChangeNoteStatus("Debit",2,$credit_note_id,$invoice_id,$approved_by,"first");
	}

	public function approveFirstCreditNote(Request $request,$credit_note_id="",$invoice_id="",$approved_by="")
	{
		$this->ChangeNoteStatus("Credit",1,$credit_note_id,$invoice_id,$approved_by,"first");
	}

	public function rejectFirstCreditNote(Request $request,$credit_note_id="",$invoice_id="",$approved_by="")
	{
		$this->ChangeNoteStatus("Credit",2,$credit_note_id,$invoice_id,$approved_by,"first");
	}

	public function approveFinalDebitNote(Request $request,$credit_note_id="",$invoice_id="",$approved_by="")
	{
		$this->ChangeNoteStatus("Debit",3,$credit_note_id,$invoice_id,$approved_by,"final");
	}

	public function rejectFinalDebitNote(Request $request,$credit_note_id="",$invoice_id="",$approved_by="")
	{
		$this->ChangeNoteStatus("Debit",2,$credit_note_id,$invoice_id,$approved_by,"final");
	}

	public function approveFinalCreditNote(Request $request,$credit_note_id="",$invoice_id="",$approved_by="")
	{
		$this->ChangeNoteStatus("Credit",3,$credit_note_id,$invoice_id,$approved_by,"final");
	}

	public function rejectFinalCreditNote(Request $request,$credit_note_id="",$invoice_id="",$approved_by="")
	{
		$this->ChangeNoteStatus("Credit",2,$credit_note_id,$invoice_id,$approved_by,"final");
	}

	######### PURCHASE CREDIT DEBIT NOTE APPROVAL CODE ##################
	private function ChangePurchaseNoteStatus($NoteType, $Status, $credit_note_id="",$approved_by="",$Level="")
	{
		$CreditNoteID 	= decode($credit_note_id);
		$ApprovedBy 	= decode($approved_by);
		$AdminUser 		= AdminUser::where("adminuserid",$ApprovedBy)->where("status","A")->first();
		if (!empty($AdminUser) && !empty($Level))
		{
			$WmInvoicesCreditDebitNotes = PurchaseCreditDebitNoteMaster::find($CreditNoteID);
			if ($WmInvoicesCreditDebitNotes->status == 3) {
				echo "<br /><center>$NoteType note is already approved.</center>";
			} else if ($WmInvoicesCreditDebitNotes->status == 2) {
				echo "<br /><center>$NoteType note is already rejected.</center>";
			} else {
				if (strtolower($Level) == "first" && $WmInvoicesCreditDebitNotes->first_level_approved_by != $ApprovedBy) {
					echo "<br /><center>You're not authorize user to access this page.</center>";
				} else if ($WmInvoicesCreditDebitNotes->id > 0 && $WmInvoicesCreditDebitNotes->status != $Status && $WmInvoicesCreditDebitNotes->status != 2) {
					if (strtolower($Level) == "first") {
						$AdminUserRights = AdminUserRights::where("adminuserid",$ApprovedBy)->where("trnid",PURCHASE_CN_DN_FIRST_LEVEL_APPROVAL)->first();
						$AdminUserRights = GroupRightsTransaction::where("trn_id",PURCHASE_CN_DN_FIRST_LEVEL_APPROVAL)->where("group_id",$AdminUser->user_type)->count();
						
					} else {
						$AdminUserRights = AdminUserRights::where("adminuserid",$ApprovedBy)->where("trnid",PURCHASE_CN_DN_FINAL_LEVEL_APPROVAL)->first();
						$AdminUserRights = GroupRightsTransaction::where("trn_id",PURCHASE_CN_DN_FINAL_LEVEL_APPROVAL)->where("group_id",$AdminUser->user_type)->count();
						
					}
					if ($AdminUserRights > 0) {
						$arrData = array("status"=>$Status,"id"=>$CreditNoteID);
						PurchaseCreditDebitNoteMaster::ApproveCreditDebitNote($arrData,$ApprovedBy,$AdminUser->company_id);
						if ($Status == 2) {
							echo "<br /><center>$NoteType note is rejected successfully.</center>";
						} else {
							echo "<br /><center>$NoteType note is approved successfully.</center>";
						}
					} else {
						echo "<br /><center>You're not authorize user to access this page.</center>";
					}
				} else if ($WmInvoicesCreditDebitNotes->id > 0) {
					if ($WmInvoicesCreditDebitNotes->status == 1 || $WmInvoicesCreditDebitNotes->status == 3) {
						echo "<br /><center>$NoteType note is already approved.</center>";
					} else {
						echo "<br /><center>$NoteType note is already rejected.</center>";
					}
				} else {
					echo "<br /><center>Invalid Request !!!</center>";
				}
			}
		} else {
			echo "<br /><center>You're not authorize user to access this page.</center>";
		}
	}

	public function approvePurchseFirstDebitNote(Request $request,$credit_note_id="",$approved_by="")
	{
		$this->ChangePurchaseNoteStatus("Debit",1,$credit_note_id,$approved_by,"first");
	}

	public function rejectPurchseFirstDebitNote(Request $request,$credit_note_id="",$approved_by="")
	{
		$this->ChangePurchaseNoteStatus("Debit",2,$credit_note_id,$approved_by,"first");
	}

	public function approvePurchseFirstCreditNote(Request $request,$credit_note_id="",$approved_by="")
	{
		$this->ChangePurchaseNoteStatus("Credit",1,$credit_note_id,$approved_by,"first");
	}

	public function rejectPurchseFirstCreditNote(Request $request,$credit_note_id="",$approved_by="")
	{
		$this->ChangePurchaseNoteStatus("Credit",2,$credit_note_id,$approved_by,"first");
	}

	public function approvePurchseFinalDebitNote(Request $request,$credit_note_id="",$approved_by="")
	{
		$this->ChangePurchaseNoteStatus("Debit",3,$credit_note_id,$approved_by,"final");
	}

	public function rejectPurchseFinalDebitNote(Request $request,$credit_note_id="",$approved_by="")
	{
		$this->ChangePurchaseNoteStatus("Debit",2,$credit_note_id,$approved_by,"final");
	}

	public function approvePurchseFinalCreditNote(Request $request,$credit_note_id="",$approved_by="")
	{
		$this->ChangePurchaseNoteStatus("Credit",3,$credit_note_id,$approved_by,"final");
	}

	public function rejectPurchseFinalCreditNote(Request $request,$credit_note_id="",$approved_by="")
	{
		$this->ChangePurchaseNoteStatus("Credit",2,$credit_note_id,$approved_by,"final");
	}

	public function approvePurchaseInvoice(Request $request,$appointment_id="")
	{
		$AppoinmentID 	= decode($appointment_id);
		$Appoinment 	= Appoinment::select("appointment_id")->where("appointment_id",$AppoinmentID)->where("invoice_approved",0)->first();
		if (!empty($Appoinment))
		{
			Appoinment::where("appointment_id",$AppoinmentID)->update(["invoice_approved"=>1]);
			echo "<br /><center>Request approved successfully.</center>";
			Appoinment::SendEmailInvoicePendingForApproval($AppoinmentID);
		} else {
			echo "<br /><center>Invoice is already approved.</center>";
		}
	}
}