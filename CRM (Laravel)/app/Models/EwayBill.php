<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Classes\EwayBill;

	/*
	Use 	: To Generate EwayBill during dispatch
	Author 	: Axay Shah
	Date 	: 13 June,2019 
	*/
class EwayBill extends Model
{
	public static function GenerateEwaybill($request)
    {
    	dd($request->all());
    	$MEARCHANT_KEY 	= (isset($request['mearchant_key']) && !empty($request['mearchant_key'])) ? $request['mearchant_key'] : '';
    	$AGGREGATOR_ID 	= (isset($request['agreegator_id']) && !empty($request['agreegator_id'])) ? $request['agreegator_id'] : '';
		$SUPPLY_TYPE 	= (isset($request['supply_type']) && !empty($request['supply_type'])) ? $request['supply_type'] : 'O';
    	$SUB_SUPPLY_TYPE= (isset($request['sub_supply_type']) && !empty($request['sub_supply_type'])) ? $request['sub_supply_type'] : '1';
    	$SUB_SUPPLY_DESC= (isset($request['sub_supply_desc']) && !empty($request['sub_supply_desc'])) ? $request['sub_supply_desc'] : '';
    	$DOC_TYPE 		= (isset($request['doc_type']) && !empty($request['doc_type'])) ? $request['doc_type'] : 'INV';
    	$CHALLAN_NO 	= (isset($request['docNo']) && !empty($request['docNo'])) ? $request['docNo'] : '';
    	$DOC_DATE 		= (isset($request['doc_date']) && !empty($request['doc_date'])) ? $request['doc_date'] : '';
    	$BILL_TO_GSTNO 	= (isset($request['bill_to_gstin']) && !empty($request['bill_to_gstin'])) ? $request['bill_to_gstin'] : '';
    	$BILL_TO_NAME 	= (isset($request['bill_to_name']) && !empty($request['bill_to_name'])) ? $request['bill_to_name'] : '';
    	$BILL_TO_ADDRESS = (isset($request['bill_to_address']) && !empty($request['bill_to_address'])) ? $request['bill_to_address'] : '';
 		$BILL_TO_STATE 	= (isset($request['bill_to_state']) && !empty($request['bill_to_state'])) ? $request['bill_to_state'] : '';
 		$BILL_TO_ZIP 	= (isset($request['bill_to_zipcode']) && !empty($request['bill_to_zipcode'])) ? $request['bill_to_zipcode'] : '';
 		$BILL_TO_CODE 	= (isset($request['bill_to_state_code']) && !empty($request['bill_to_state_code'])) ? $request['bill_to_state_code'] : '';


 		$SHIP_TO_GSTNO 	= (isset($request['ship_to_gstin']) && !empty($request['ship_to_gstin'])) ? $request['ship_to_gstin'] : '';
    	$SHIP_TO_NAME 	= (isset($request['ship_to_name']) && !empty($request['ship_to_name'])) ? $request['ship_to_name'] : '';
    	$SHIP_TO_ADDRESS = (isset($request['ship_to_address']) && !empty($request['ship_to_address'])) ? $request['ship_to_address'] : '';
 		$SHIP_TO_STATE 	= (isset($request['ship_to_state']) && !empty($request['ship_to_state'])) ? $request['ship_to_state'] : '';
 		$SHIP_TO_ZIP 	= (isset($request['ship_to_zipcode']) && !empty($request['ship_to_zipcode'])) ? $request['ship_to_zipcode'] : '';
 		$SHIP_TO_CODE 	= (isset($request['ship_to_state_code']) && !empty($request['ship_to_state_code'])) ? $request['ship_to_state_code'] : '';



 		$TOTAL_AMOUNT 		= (isset($request['total_amount']) && !empty($request['total_amount'])) ? $request['total_amount'] : 0;
 		$TOTAL_CGST 		= (isset($request['total_cgst']) && !empty($request['total_cgst'])) ? $request['total_cgst'] : 0;
 		$TOTAL_SGST 		= (isset($request['total_sgst']) && !empty($request['total_sgst'])) ? $request['total_sgst'] : 0;
 		$TOTAL_IGST 		= (isset($request['total_igst']) && !empty($request['total_igst'])) ? $request['total_igst'] : 0;
 		$TOTAL_TAX_AMOUNT 	= (isset($request['total_tax_amount']) && !empty($request['total_tax_amount'])) ? $request['total_tax_amount'] : 0;
 		$CESS_VALUE 		= (isset($request['cessValue']) && !empty($request['cessValue'])) ? $request['cessValue'] : 0;


 		$TRANSPOTER_ID 		= (isset($request['transporter_id']) && !empty($request['transporter_id'])) ? $request['transporter_id'] : '';
 		$TRANSPOTER_NAME 	= (isset($request['transporter_name']) && !empty($request['transporter_name'])) ? $request['transporter_name'] : '';
 		$TRANSPOTER_DOC_NO 	= (isset($request['trans_doc_no']) && !empty($request['trans_doc_no'])) ? $request['trans_doc_no'] : 0;
 		$TRANS_MODE 		= (isset($request['trans_mode']) && !empty($request['trans_mode'])) ? $request['trans_mode'] : 1;
		$TRAVEL_DISTANCE 	= (isset($request['travel_distance']) && !empty($request['travel_distance'])) ? $request['travel_distance'] : 0;
		$TRANS_DOC_DATE 	= (isset($request['trans_doc_date']) && !empty($request['trans_doc_date'])) ? $request['trans_doc_date'] : "";
		$TRANS_TYPE 		= (isset($request['transaction_type']) && !empty($request['transaction_type'])) ? $request['transaction_type'] : 1;
		$VEHICLE_NO 		= (isset($request['vehicle_no']) && !empty($request['vehicle_no'])) ? $request['vehicle_no'] : '';
		$VEHICLE_TYPE 		= (isset($request['vehicle_type']) && !empty($request['vehicle_type'])) ? $request['vehicle_type'] : 'R';
		$ITEM_LIST 			= (isset($request['item_list']) && !empty($request['item_list'])) ? $request['item_list'] : '';
        $ewayObj     = new EwayBill();
	    $ewayBillApi = $ewayObj->generateRandomString();
    
    	$ewaybillrequest = [
            "agreegator_id"     => $AGGREGATOR_ID,
            "supplyType"        => $SUPPLY_TYPE,
            "subSupplyType"     => $SUB_SUPPLY_TYPE,
            "subSupplyDesc"     => $SUB_SUPPLY_DESC,
            "docType"           => $DOC_TYPE,
            "docNo"             => $CHALLAN_NO,
            "docDate"           => date("d/m/Y",strtotime($DOC_DATE)),
            "fromGstin"         => $BILL_TO_GSTNO, //05AAAAH1426Q1ZO,
            "fromTrdName"       => $BILL_TO_NAME,
            "fromAddr1"         => strip_tags($BILL_TO_ADDRESS),
            "fromAddr2"         => $BILL_TO_STATE,
            "fromPlace"         => $BILL_TO_STATE,
            "fromPincode"       => $BILL_TO_ZIP,
            "actFromStateCode"  => $BILL_TO_CODE,
            "fromStateCode"     => $BILL_TO_CODE,
            "toGstin"           => $SHIP_TO_GSTNO, //05AAAAH2043K1Z1
            "toTrdName"         => $SHIP_TO_NAME,
            "toAddr1"           => strip_tags($SHIP_TO_ADDRESS),
            "toAddr2"           => $SHIP_TO_STATE,
            "toPlace"           => $SHIP_TO_STATE,
            "toPincode"         => $SHIP_TO_ZIP,
            "actToStateCode"    => $SHIP_TO_CODE,
            "toStateCode"       => $SHIP_TO_CODE,
            "totalValue"        => (float)$TOTAL_AMOUNT,
            "cgstValue"         => (float)$TOTAL_CGST,
            "sgstValue"         => (float)$TOTAL_SGST,
            "igstValue"         => (float)$TOTAL_IGST,
            "cessValue"         => $CESS_VALUE,
            "totInvValue"       => (float)$TOTAL_TAX_AMOUNT,
            "transporterId"     => $TRANSPOTER_ID,
            "transporterName"   => $TRANSPOTER_NAME,
            "transDocNo"        => $TRANSPOTER_DOC_NO,
            "transMode"         => $TRANS_MODE,
            "transDistance"     => $TRAVEL_DISTANCE,
            "transDocDate"      => $TRANS_DOC_DATE,
            "transactionType"   => $TRANS_TYPE,
            "vehicleNo"         => $VEHICLE_NO,
            "vehicleType"       => $VEHICLE_TYPE,
            "itemList"          => $ITEM_LIST
        ];
        	$ewayBillApi        = $ewayObj->ewayBillApi($ewaybillrequest);
        	dd($ewayBillApi);
        if(isset($ewayBillApi)) {
	            $result = json_decode($ewayBillApi['response']);
            if(isset($result) && $result->status == 1) {
	                $decsek         = generateAesEncryption($ewayObj->SEK,$ewayObj->encAppKey,1);
	                $requestData    = generateAesEncryption(json_encode($result->data),$decsek,1);
	                $ewayBillNo     = json_decode($requestData);
                if (isset($ewayBillNo->ewayBillNo) && !empty($ewayBillNo->ewayBillNo)) {
                	self::where("dispatch_id",$DISPATCH_ID)->update("eway_bill_no",$ewayBillNo->ewayBillNo);
                	log_action('Agreegator_Collection_EwayBill_Generated',$DISPATCH_ID,(new static)->getTable());
                	// $this->UpdateEWaybillNo($DISPATCH_ID,$ewayBillNo->ewayBillNo);
	            }
            }
	    }
	}

}