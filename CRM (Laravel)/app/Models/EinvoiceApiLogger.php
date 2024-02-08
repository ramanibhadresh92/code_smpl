<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Classes\Einvoice;
class EinvoiceApiLogger extends Model
{
    protected 	$table 		= 'einvoice_api_logger';
    protected   $primaryKey =   'id'; // or null
    protected   $guarded    =   ['id'];
    public      $timestamps =   true;

    public static function AddEinvoiceLogger($request){
        $ID                 = 0;
        $LOG                = new self();
        $LOG->marchant_id   = "";
        $LOG->input         = (!empty($request)) ? json_encode($request) : "";
        $LOG->created_at    = date("Y-m-d H:i:s");
        if($LOG->save()){
            $ID =  $LOG->id;
        }
        return $ID;
    }


    public static function CallEInvoiceApi($data){
        $res    = array();
        if(!empty($data)){

            $rand                               = rand();
            $array["version"]                   = (string)"1.1";
            $array["TranDtls"]["TaxSch"]        = "GST";
            $array["TranDtls"]["SupTyp"]        = "B2B";
            $array["TranDtls"]["RegRev"]        = null;
            $array["TranDtls"]["EcmGstin"]      = null;
            $array["TranDtls"]["IgstOnIntra"]   = "N";
            $array["DocDtls"]["Typ"]            = (string)$data["docType"];
            $array["DocDtls"]["No"]             = (string)$data["docNo"];
            $array["DocDtls"]["Dt"]             = (string)$data["docDate"];
            ######## SALLER DETAILS ###########
            $array["SellerDtls"]["Gstin"]       = (string)$data["fromGstin"];
            $array["SellerDtls"]["LglNm"]       = (string)$data["fromTrdName"];
            $array["SellerDtls"]["TrdNm"]       = (string)$data["fromTrdName"];
            $array["SellerDtls"]["Addr1"]       = (string)$data["fromAddr1"];
            $array["SellerDtls"]["Addr2"]       = (isset($data["fromAddr2"]) && !empty($data["fromAddr2"])) ? (string)$data["fromAddr2"] : null;
            $array["SellerDtls"]["Loc"]         = (string)$data["fromPlace"];
            $array["SellerDtls"]["Pin"]         = (int)$data["fromPincode"];
            $array["SellerDtls"]["Stcd"]        = $data["fromStateCode"];
            $array["SellerDtls"]["Ph"]          = null;
            $array["SellerDtls"]["Em"]          = null;
            ######## BUYER DETAILS ###########
            $array["BuyerDtls"]["Gstin"]        = (string)$data["toGstin"];
            $array["BuyerDtls"]["LglNm"]        = (string)$data["toTrdName"];
            $array["BuyerDtls"]["TrdNm"]        = (string)$data["toTrdName"];
            $array["BuyerDtls"]["Addr1"]        = (string)$data["toAddr1"];
            $array["BuyerDtls"]["Addr2"]        = (isset($data["toAddr2"]) && !empty($data["toAddr2"])) ? (string)$data["toAddr2"] : null;
            $array["BuyerDtls"]["Loc"]          = (string)$data["toPlace"];
            $array["BuyerDtls"]["Pin"]          = (int)$data["toPincode"];
            $array["BuyerDtls"]["Stcd"]         = (string)$data["toStateCode"];
            $array["BuyerDtls"]["Pos"]          = (string)$data["toStateCode"];
            $array["BuyerDtls"]["Ph"]           = null;
            $array["BuyerDtls"]["Em"]           = null;

            $array["DispDtls"]                 = null;
            $array["ShipDtls"]                 = null;
            $array["EwbDtls"]                  = null;
            $array["ExpDtls"]                  = null;
            $array["AddlDocDtls"]              = null;
            ######## SUMMERY OF INVOICE DETAILS ###########
            $array["ValDtls"]["AssVal"]        = (float)$data["totalValue"];
            $array["ValDtls"]["CgstVal"]       = (float)$data["cgstValue"];
            $array["ValDtls"]["SgstVal"]       = (float)$data["sgstValue"];
            $array["ValDtls"]["IgstVal"]       = (float)$data["igstValue"];
            $array["ValDtls"]["CesVal"]        = (float)$data["cessValue"];
            $array["ValDtls"]["StCesVal"]      = (float)$data["cessNonAdvolValue"];
            $array["ValDtls"]["Discount"]      = (float)0;
            $array["ValDtls"]["OthChrg"]       = (float)0;
            $array["ValDtls"]["RndOffAmt"]     = (float)$data["otherValue"];
            $array["ValDtls"]["TotInvVal"]     = (float)$data["totInvValue"];
            ######## ITEM DETAILS ###########
            $i      = 1;
            $item   = array();
            foreach($data["itemList"] as $key => $value){
                    $item[] = array(
                        "SlNo"                  => (string)$i,
                        "PrdDesc"               => (string)$value["productName"],
                        "IsServc"               => "N",
                        "HsnCd"                 => (string)$value["hsnCode"],
                        "Barcde"                => null,
                        "Qty"                   => (float)$value["quantity"],
                        "Unit"                  => strtoupper($value["qtyUnit"]),
                        "UnitPrice"             => (float)$value["price"],
                        "TotAmt"                => (float)$value["taxableAmount"],
                        "Discount"              => (float)0,
                        "PreTaxVal"             => (float)0,
                        "AssAmt"                => (float)$value["taxableAmount"],
                        "GstRt"                 => (float)$value["totalGstPercent"],
                        "IgstAmt"               => (float)$value["igstAmt"],
                        "CgstAmt"               => (float)$value["cgstAmt"],
                        "SgstAmt"               => (float)$value["sgstAmt"],
                        "CesRt"                 => (float)0,
                        "CesAmt"                => (float)0,
                        "CesNonAdvlAmt"         => (float)0,
                        "StateCesRt"            => (float)0,
                        "StateCesAmt"           => (float)0,
                        "StateCesNonAdvlAmt"    => (float)0,
                        "OthChrg"               => (float)0,
                        "TotItemVal"            => (float)$value["totalItemAmount"],
                    );
                $i++;
            }
            $array["ItemList"]  =  $item;
            $record             = self::AddEinvoiceLogger($array);
            $Einvoice           = new Einvoice();
            $res                = $Einvoice->GenerateEInvoice($array);
            if(!empty($res)){
                $AckNo  =  "";
                $AckDt  = "";
                $Irn    = "";
                if($res["Status"] == 1){
                    $data   = json_decode($res["Data"],true);
                    $AckNo  = (isset($data['AckNo'])) ? $data['AckNo']  : "";
                    $AckDt  = (isset($data['AckDt'])) ? $data['AckDt']  : "";
                    $Irn    = (isset($data['Irn'])) ? $data['Irn']      : "";
                }
                $record     = self::where("id",$record)->update(["output"=> json_encode($res),"status"=>$res["Status"],
                    "irn"           => $Irn,
                    "ack_no"        => $AckNo,
                    "ack_date"      => $AckDt
                ]);
            }
        }
        return $res;
    }

}
