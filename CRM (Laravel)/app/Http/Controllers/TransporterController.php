<?php

namespace App\Http\Controllers;
use App\Http\Controllers\LRBaseController;
use Illuminate\Http\Request;
use App\Models\AdminUser;
use App\Models\AdminUserRights;
use App\Models\TransporterDetailsMaster;
use App\Models\AutoWayBridgeDetails;
use JWTFactory;
use JWTAuth;
use Validator;
use Response;
use File;
use Storage;
use Input;
use DB;

class TransporterController extends LRBaseController
{
	private function SetVariables($request)
	{
		
	}

	public function approveTranspoterPO(Request $request,$po_id="",$user_id="")
	{
		$this->ChangePOStatus($po_id,$user_id);
	}

	private function ChangePOStatus($POID="",$approved_by="",$Status=1)
	{
		$POID 			= decode($POID);
		$ApprovedBy 	= decode($approved_by);
		$AdminUser 		= AdminUser::select("company_id")->where("adminuserid",$ApprovedBy)->where("status","A")->first();
		if (!empty($AdminUser))
		{
			$TransporterDetailsMaster = TransporterDetailsMaster::find($POID);
			if ($TransporterDetailsMaster->id > 0 && $TransporterDetailsMaster->approval_status != $Status && $TransporterDetailsMaster->approval_status != 2) {
				$AdminUserRights = AdminUserRights::where("adminuserid",$ApprovedBy)->where("trnid",50012)->first();
				if (isset($AdminUserRights->adminuserid)) {
					TransporterDetailsMaster::ApproveRecord($POID,$ApprovedBy,$Status);
					if ($Status == 2) {
						echo "<br /><center>Transpoter PO rejected successfully.</center>";
					} else {
						echo "<br /><center>Transpoter PO is approved successfully.</center>";
					}
				} else {
					echo "<br /><center>You're not authorize user to access this page.</center>";
				}
			} else if ($TransporterDetailsMaster->id > 0) {
				if ($TransporterDetailsMaster->approval_status == 1) {
					echo "<br /><center>Transpoter PO is already approved.</center>";
				} else {
					echo "<br /><center>Transpoter PO is already rejected.</center>";
				}
			} else {
				echo "<br /><center>Invalid Request !!!</center>";
			}
		} else {
			echo "<br /><center>You're not authorize user to access this page.</center>";
		}
	}

	public function ViewWayBridgeSlip($row_id="")
	{
		$row_id 	= decode($row_id);
		if (!empty($row_id)) {
			$RecordRow 	= AutoWayBridgeDetails::find($row_id);
			if (isset($RecordRow->wayslip_pdf) && !empty($RecordRow->wayslip_pdf)) {
				$FilePath = public_path($RecordRow->path."/".$RecordRow->wayslip_pdf);
				if (file_exists($FilePath) && is_readable($FilePath)) {
					header('Content-type: application/pdf');
					header('Content-Disposition: inline; filename="' . basename($FilePath) . '"');
					header('Content-Transfer-Encoding: binary');
					header('Accept-Ranges: bytes');
					// Read the file
					@readfile($FilePath);
				} else {
					echo "<br />Sorry, the page you are looking for could not be found.";
				}
			}
		} else {
			echo "<br />Sorry, the page you are looking for could not be found.";
		}
		die;
	}
}