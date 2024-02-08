<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmClientMaster;
use DB;
use Mail;
class WmSalesPaymentDetails extends Model
{
	protected 	$table 		=	'wm_sales_payment_details';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];

	/**
	* Function Name : ListOutStandingReport
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-12-15
	*/
	public static function ListOutStandingReport($request)
	{
		$customer 			= (isset($request['customer']) && !empty($request['customer'])) ? $request['customer'] : "";
		$customer_category 	= (isset($request['customer_category']) && !empty($request['customer_category'])) ? $request['customer_category'] : "";
		$days_till_net_due 	= (isset($request['days_till_net_due']) && !empty($request['days_till_net_due'])) ? $request['days_till_net_due'] : "";
		$transaction_type 	= (isset($request['transaction_type']) && !empty($request['transaction_type'])) ? $request['transaction_type'] : "";
		$po_no 				= (isset($request['po_no']) && !empty($request['po_no'])) ? $request['po_no'] : "";
		$remark 			= (isset($request['remark']) && !empty($request['remark'])) ? $request['remark'] : "";
		$document_number 	= (isset($request['document_number']) && !empty($request['document_number'])) ? $request['document_number'] : "";
		$location 			= (isset($request['location']) && !empty($request['location'])) ? $request['location'] : "";
		$po_no 				= (isset($request['po_no']) && !empty($request['po_no'])) ? $request['po_no'] : "";
		$data 				= self::select("*");
		if(!empty($customer)) {
			$data->where("Customer","like","%$customer%");
		}
		if(!empty($customer_category)) {
			$data->where("CustomerCategory","like","%$customer_category%");
		}
		if(!empty($location)) {
			$data->where("Location","like","%$location%");
		}
		if(!empty($transaction_type)) {
			$data->where("TransactionType","like","%$transaction_type%");
		}
		if(!empty($document_number)) {
			$data->where("DocumentNumber","like","%$document_number%");
		}
		if(!empty($po_no)) {
			$data->where("PONo","like","%$po_no%");
		}
		if(!empty($location)) {
			$data->where("Location","like","%$location%");
		}
		if(!empty($remark)) {
			$data->where("Remarks","like","%$remark%");
		}
		$result 			= $data->get()->toArray();
		$array 				= array();
		$TOTAL_OPENING_AMT 	= 0;
		$TOTAL_GROSS_AMT 	= 0;
		$GRAND_TOTAL_AMT 	= 0;
		$ReportAsONDate 	= "";
		if(!empty($result)) {
			$ReportDate = date('d-M-Y', strtotime('-1 day', strtotime($result[0]['created_at'])));
			if (!empty($ReportDate)) {
				$ReportAsONDate = "Last updated on ".$ReportDate;
			}
			foreach($result as $key => $value) {
				switch ($value['Remarks']) {
					case 'OVER DUE':
						$result[$key]['Remarks'] = "<span class='text-danger'>".$value['Remarks']."</span>";
						break;
					case 'NOT DUE':
						$result[$key]['Remarks'] = "<span class='text-success'>".$value['Remarks']."</span>";
						break;
					case 'PFDD':
					case 'DISPUTED':
						$result[$key]['Remarks'] = "<span class='text-warning'>".$value['Remarks']."</span>";
						break;
					default:
						$result[$key]['Remarks'] = $value['Remarks'];
						break;
				}
				$TOTAL_OPENING_AMT 	+= $value['OpenBalance'];
				$TOTAL_GROSS_AMT 	+= $value['AmountGross'];
				$result[$key]['Date'] 			= date("d-M-Y",strtotime($result[$key]['Date']));
				$result[$key]['DueDate'] 		= date("d-M-Y",strtotime($result[$key]['DueDate']));
				$result[$key]['AmountGross'] 	= str_replace(" -","-",trim(_FormatNumberV2(($value['AmountGross']),0,1)));
				$result[$key]['OpenBalance'] 	= str_replace(" -","-",trim(_FormatNumberV2($value['OpenBalance'],0,1)));
			}
		}
		$array['total_opening_balance'] 	= str_replace(" -","-",trim(_FormatNumberV2($TOTAL_OPENING_AMT,0,1)));
		$array['total_gross_amt'] 			= str_replace(" -","-",trim(_FormatNumberV2($TOTAL_GROSS_AMT,0,1)));
		$array['grand_total'] 				= str_replace(" -","-",trim(_FormatNumberV2($TOTAL_OPENING_AMT,0,1)));
		$array['customer_limit'] 			= "";
		$array['report_update_note'] 		= $ReportAsONDate;
		$array['summary_report'] 			= self::GetOutStandingReportSummary($request);
		$array['partywise_summary_report'] 	= self::GetOutStandingReportSummaryPartywise($request);
		$array['result'] 					= $result;
		return $array;
	}

	/**
	* Function Name : GetOutStandingReportSummary
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-12-15
	*/
	public static function GetOutStandingReportSummary($request)
	{
		$customer 			= (isset($request['customer']) && !empty($request['customer'])) ? $request['customer'] : "";
		$customer_category 	= (isset($request['customer_category']) && !empty($request['customer_category'])) ? $request['customer_category'] : "";
		$days_till_net_due 	= (isset($request['days_till_net_due']) && !empty($request['days_till_net_due'])) ? $request['days_till_net_due'] : "";
		$transaction_type 	= (isset($request['transaction_type']) && !empty($request['transaction_type'])) ? $request['transaction_type'] : "";
		$po_no 				= (isset($request['po_no']) && !empty($request['po_no'])) ? $request['po_no'] : "";
		$remark 			= (isset($request['remark']) && !empty($request['remark'])) ? $request['remark'] : "";
		$document_number 	= (isset($request['document_number']) && !empty($request['document_number'])) ? $request['document_number'] : "";
		$location 			= (isset($request['location']) && !empty($request['location'])) ? $request['location'] : "";
		$po_no 				= (isset($request['po_no']) && !empty($request['po_no'])) ? $request['po_no'] : "";
		$WHERECOND 			= "";
		if(!empty($customer)) {
			$WHERECOND .= " AND Customer like '%".$customer."%'";
		}
		if(!empty($customer_category)) {
			$WHERECOND .= " AND CustomerCategory like '%".$customer_category."%'";
		}
		if(!empty($location)) {
			$WHERECOND .= " AND Location like '%".$location."%'";
		}
		if(!empty($transaction_type)) {
			$WHERECOND .= " AND TransactionType like '%".$transaction_type."%'";
		}
		if(!empty($document_number)) {
			$WHERECOND .= " AND DocumentNumber like '%".$document_number."%'";
		}
		if(!empty($po_no)) {
			$WHERECOND .= " AND PONo like '%".$po_no."%'";
		}
		if(!empty($location)) {
			$WHERECOND .= " AND Location like '%".$location."%'";
		}
		if(!empty($remark)) {
			$WHERECOND .= " AND Remarks like '%".$remark."%'";
		}
		$SELECT_SQL 	= "	SELECT TRIM(wm_sales_payment_details.CustomerCategory) AS CUSTOMER_CATEGORY,
							TRIM(wm_sales_payment_details.Remarks) AS PAYMENT_STATUS,
							SUM(OpenBalance) as OutStandingAmount
							FROM wm_sales_payment_details
							WHERE 1=1 $WHERECOND
							GROUP BY CUSTOMER_CATEGORY, PAYMENT_STATUS";
		$SELECTRES 		= DB::connection('master_database')->select($SELECT_SQL);
		$TOTALCOLUMS 	= array();
		$TempArray 		= array();
		$arrResult 		= array();
		$GRAND_TOTAL 	= 0;
		$PrevTitle 		= "";
		if (!empty($SELECTRES)) {
			foreach ($SELECTRES as $SELECTROW) {
				if (!empty($PrevTitle) && $SELECTROW->CUSTOMER_CATEGORY != $PrevTitle) {
					$TempArray['TOTAL'] = str_replace(" -","-",trim(_FormatNumberV2($GRAND_TOTAL,0,1)));;
					$arrResult[] 		= $TempArray;
					$TempArray 			= array();
					$GRAND_TOTAL 		= 0;
					$PrevTitle 			= $SELECTROW->CUSTOMER_CATEGORY;
				} else if (empty($PrevTitle)) {
					$PrevTitle = $SELECTROW->CUSTOMER_CATEGORY;
				}
				$GRAND_TOTAL += $SELECTROW->OutStandingAmount;
				$TempArray['TITLE'] 					= $PrevTitle;
				$TempArray[$SELECTROW->PAYMENT_STATUS] 	= str_replace(" -","-",trim(_FormatNumberV2($SELECTROW->OutStandingAmount,0,1)));
				if (!in_array($SELECTROW->PAYMENT_STATUS,$TOTALCOLUMS)) array_push($TOTALCOLUMS,$SELECTROW->PAYMENT_STATUS);
			}
			$TempArray['TOTAL'] = str_replace(" -","-",trim(_FormatNumberV2($GRAND_TOTAL,0,1)));;
			$arrResult[] 		= $TempArray;
			$TOTALCOLUMS = array_unique($TOTALCOLUMS);
			array_push($TOTALCOLUMS,"TOTAL");
		}
		$SELECT_SQL 	= "	SELECT TRIM(wm_sales_payment_details.Remarks) AS PAYMENT_STATUS,
							SUM(OpenBalance) as OutStandingAmount
							FROM wm_sales_payment_details
							WHERE 1=1 $WHERECOND
							GROUP BY PAYMENT_STATUS";
		$SELECTRES 		= DB::connection('master_database')->select($SELECT_SQL);
		$GRAND_TOTAL 	= array();
		$GRANDTOTAL 	= 0;
		if (!empty($SELECTRES)) {
			foreach ($SELECTRES as $SELECTROW) {
				$GRAND_TOTAL[$SELECTROW->PAYMENT_STATUS] = str_replace(" -","-",trim(_FormatNumberV2($SELECTROW->OutStandingAmount,0,1)));
				$GRANDTOTAL += $SELECTROW->OutStandingAmount;
			}
			$GRAND_TOTAL['TOTAL'] = str_replace(" -","-",trim(_FormatNumberV2($GRANDTOTAL,0,1)));
		}
		$arrResultBoth['TOTALCOLUMS'] 	= $TOTALCOLUMS;
		$arrResultBoth['ReportResult'] 	= $arrResult;
		$arrResultBoth['GRAND_TOTAL'] 	= $GRAND_TOTAL;
		return $arrResultBoth;
	}

	/**
	* Function Name : GetOutStandingReportSummaryPartywise
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-12-15
	*/
	public static function GetOutStandingReportSummaryPartywise($request)
	{
		$customer 			= (isset($request['customer']) && !empty($request['customer'])) ? $request['customer'] : "";
		$customer_category 	= (isset($request['customer_category']) && !empty($request['customer_category'])) ? $request['customer_category'] : "";
		$days_till_net_due 	= (isset($request['days_till_net_due']) && !empty($request['days_till_net_due'])) ? $request['days_till_net_due'] : "";
		$transaction_type 	= (isset($request['transaction_type']) && !empty($request['transaction_type'])) ? $request['transaction_type'] : "";
		$po_no 				= (isset($request['po_no']) && !empty($request['po_no'])) ? $request['po_no'] : "";
		$remark 			= (isset($request['remark']) && !empty($request['remark'])) ? $request['remark'] : "";
		$document_number 	= (isset($request['document_number']) && !empty($request['document_number'])) ? $request['document_number'] : "";
		$location 			= (isset($request['location']) && !empty($request['location'])) ? $request['location'] : "";
		$po_no 				= (isset($request['po_no']) && !empty($request['po_no'])) ? $request['po_no'] : "";
		$WHERECOND 			= "";
		if(!empty($customer)) {
			$WHERECOND .= " AND Customer like '%".$customer."%'";
		}
		if(!empty($customer_category)) {
			$WHERECOND .= " AND CustomerCategory like '%".$customer_category."%'";
		}
		if(!empty($location)) {
			$WHERECOND .= " AND Location like '%".$location."%'";
		}
		if(!empty($transaction_type)) {
			$WHERECOND .= " AND TransactionType like '%".$transaction_type."%'";
		}
		if(!empty($document_number)) {
			$WHERECOND .= " AND DocumentNumber like '%".$document_number."%'";
		}
		if(!empty($po_no)) {
			$WHERECOND .= " AND PONo like '%".$po_no."%'";
		}
		if(!empty($location)) {
			$WHERECOND .= " AND Location like '%".$location."%'";
		}
		if(!empty($remark)) {
			$WHERECOND .= " AND Remarks like '%".$remark."%'";
		}
		$SELECT_SQL 	= "	SELECT TRIM(wm_sales_payment_details.Customer) AS Customer,
							TRIM(wm_sales_payment_details.Remarks) AS PAYMENT_STATUS,
							SUM(OpenBalance) as OutStandingAmount
							FROM wm_sales_payment_details
							WHERE 1=1 $WHERECOND
							GROUP BY Customer, PAYMENT_STATUS";
		$SELECTRES 		= DB::connection('master_database')->select($SELECT_SQL);
		$TOTALCOLUMS 	= array();
		$TempArray 		= array();
		$arrResult 		= array();
		$GRAND_TOTAL 	= 0;
		$PrevTitle 		= "";
		if (!empty($SELECTRES)) {
			foreach ($SELECTRES as $SELECTROW) {
				if (!empty($PrevTitle) && $SELECTROW->Customer != $PrevTitle) {
					$TempArray['TOTAL'] = str_replace(" -","-",trim(_FormatNumberV2($GRAND_TOTAL,0,1)));;
					$arrResult[] 		= $TempArray;
					$TempArray 			= array();
					$GRAND_TOTAL 		= 0;
					$PrevTitle 			= $SELECTROW->Customer;
				} else if (empty($PrevTitle)) {
					$PrevTitle = $SELECTROW->Customer;
				}
				$GRAND_TOTAL 							+= $SELECTROW->OutStandingAmount;
				$TempArray['Customer'] 					= $PrevTitle;
				$TempArray[$SELECTROW->PAYMENT_STATUS] 	= str_replace(" -","-",trim(_FormatNumberV2($SELECTROW->OutStandingAmount,0,1)));
				if (!in_array($SELECTROW->PAYMENT_STATUS,$TOTALCOLUMS)) array_push($TOTALCOLUMS,$SELECTROW->PAYMENT_STATUS);
			}
			$TempArray['TOTAL'] = str_replace(" -","-",trim(_FormatNumberV2($GRAND_TOTAL,0,1)));;
			$arrResult[] 		= $TempArray;
			$TOTALCOLUMS = array_unique($TOTALCOLUMS);
			array_push($TOTALCOLUMS,"TOTAL");
		}
		$SELECT_SQL 	= "	SELECT TRIM(wm_sales_payment_details.Remarks) AS PAYMENT_STATUS,
							SUM(OpenBalance) as OutStandingAmount
							FROM wm_sales_payment_details
							WHERE 1=1 $WHERECOND
							GROUP BY PAYMENT_STATUS";
		$SELECTRES 		= DB::connection('master_database')->select($SELECT_SQL);
		$GRAND_TOTAL 	= array();
		$GRANDTOTAL 	= 0;
		if (!empty($SELECTRES)) {
			foreach ($SELECTRES as $SELECTROW) {
				$GRAND_TOTAL[$SELECTROW->PAYMENT_STATUS] = str_replace(" -","-",trim(_FormatNumberV2($SELECTROW->OutStandingAmount,0,1)));
				$GRANDTOTAL += $SELECTROW->OutStandingAmount;
			}
			$GRAND_TOTAL['TOTAL'] = str_replace(" -","-",trim(_FormatNumberV2($GRANDTOTAL,0,1)));
		}
		$arrResultBoth['ReportResult'] 	= $arrResult;
		$arrResultBoth['GRAND_TOTAL'] 	= $GRAND_TOTAL;
		return $arrResultBoth;
	}

	/*
	Use 	: Send OutStanding Invoice Notification
	Author 	: KALPAK PRAJAPATI
	Date 	: 07 Dec 2023
	*/
	public static function 	SendOutStandingInvoiceNotification()
	{
		$LedgerTbl 		= (new self)->getTable();
		$WmClientMaster = new WmClientMaster;
		$SELECT_SQL 	= "	SELECT DISTINCT $LedgerTbl.wm_client_id, WmClientMaster.email
							FROM $LedgerTbl
							LEFT JOIN wm_client_master as WmClientMaster ON WmClientMaster.id = $LedgerTbl.wm_client_id
							WHERE $LedgerTbl.wm_client_id > 0
							AND WmClientMaster.email IS NOT NULL
							AND WmClientMaster.email != ''
							LIMIT 1";
		$SELECTRES 		= DB::connection('master_database')->select($SELECT_SQL);
		if(!empty($SELECTRES))
		{
			foreach($SELECTRES as $SELECTROW)
			{
				$InvoiceDetails = self::select(	"$LedgerTbl.Date",
												"$LedgerTbl.DocumentNumber",
												"$LedgerTbl.Customer",
												"$LedgerTbl.CustomerCategory",
												"$LedgerTbl.AmountGross",
												"$LedgerTbl.OpenBalance")
										->leftjoin($WmClientMaster->getTable()." AS WmClientMaster",$LedgerTbl.".wm_client_id","=","WmClientMaster.id")
										->where("WmClientMaster.id",$SELECTROW->wm_client_id)
										->orderBy("$LedgerTbl.Date","ASC")
										->get();
				if(!empty($InvoiceDetails))
				{
					$arrResult 			= array();
					$arrResult['Rows']	= array();
					$TotalInvoiceAmt 	= 0;
					$TotalOpenAmt 		= 0;
					$CustomerName 		= "";
					foreach ($InvoiceDetails as $InvoiceDetail) {
						$arrResult['Rows'][] 	= array("Date"=>$InvoiceDetail->Date,
														"DocumentNumber"=>$InvoiceDetail->DocumentNumber,
														"Customer"=>$InvoiceDetail->Customer,
														"CustomerCategory"=>$InvoiceDetail->CustomerCategory,
														"InvoiceAmount"=>$InvoiceDetail->AmountGross,
														"OpenAmount"=>$InvoiceDetail->OpenBalance);
						$TotalInvoiceAmt += $InvoiceDetail->AmountGross;
						$TotalOpenAmt += $InvoiceDetail->OpenBalance;
						$CustomerName = $InvoiceDetail->Customer;
					}
					$arrResult['TotalInvoiceAmt'] 	= $TotalInvoiceAmt;
					$arrResult['TotalOpenAmt']		= $TotalOpenAmt;
					$arrResult['CustomerName'] 		= $CustomerName;
					$ToEmail 						= (isset($SELECTROW->email) && !empty($SELECTROW->email))?explode(",",$SELECTROW->email):"";
					$ToEmail 						= array("kalpak@nepra.co.in");
					$Attachments  					= array();
					if(!empty($ToEmail))
					{
						$FromEmail		= array ("Name" => EMAIL_FROM_NAME,"Email" => EMAIL_FROM_ID);
						$Subject  		= "OutStanding Ledger - ".$CustomerName;
						$SendEmail		= Mail::send("email-template.SendOutStandingInvoiceNotification",array("ReportData"=>$arrResult),function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
							$message->from($FromEmail['Email'], $FromEmail['Name']);
							$message->to($ToEmail);
							// $message->bcc(["kalpak@nepra.co.in","sejal.banker@nepra.co.in","sakshi.rajput@nepra.co.in"]);
							$message->subject($Subject);
							if (!empty($Attachments)) {
								$message->attach($Attachments);
							}
						});
						DB::table("wm_service_invoice_email_sent_log")->where('service_id',$InvoiceDetail->id)->update(['sent_status'=>1,"email"=>json_encode($ToEmail)]);
					}
				} else {
					DB::table("wm_service_invoice_email_sent_log")->where('service_id',$value->service_id)->update(['sent_status'=>1,"email"=>""]);
				}
			}
		}
	}
}