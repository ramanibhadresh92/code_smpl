<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseLocationMaster;
use App\Models\LocationMaster;
use App\Models\VehicleMaster;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmDispatch;
use App\Models\WmDispatchProduct;
use App\Models\Parameter;
use App\Models\WmClientMaster;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use App\Models\Appoinment;
use App\Models\WmBatchCollectionMap;
use App\Models\WmProductMaster;
use App\Models\CustomerMaster;
use App\Models\CompanyProductPriceDetail;
use App\Models\SendTransferPurchaseDataToNetSuit;
use App\Models\WmDepartment;
use DB;
class NetSuitPurchaseTransactionMaster extends Model implements Auditable
{
	//
	protected 	$table 		=	'net_suit_purchase_transaction_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts = [

    ];
    /*
	Use 	: store purchase transaction in net suit table
	Author  : Axay Shah
	Date 	: 30 Sept 2021
   	*/
   	public static function StorePurchaseTransactionDataForNetSuit(){
   		$TO 		= date("Y-m-d H:i:s");
   		$FROM 		= date('Y-m-d H:i:s', strtotime(INTERVAL_TIME));
   		$FROM 		= "2022-01-01 00:00:00";
   		$FROM 		= date('Y-m-d',strtotime("-2 days"));
   		$TO 		= date("Y-m-d")." 23:59:00";
   		$SQL 		= "SELECT
	   					'APPOINTMENT' AS record_type,
						WBM.batch_id as 'record_id',
						if(CUS.ctype = 1007008,0,1) as paid,
					        CASE WHEN (CUS.ctype = 1007001 OR CUS.ctype = 1007003 OR CUS.ctype = 1007005 OR CUS.ctype = 1007009 OR CUS.ctype = 1007002 OR CUS.ctype = 1007008 OR CUS.ctype = 1007007 OR CUS.ctype = 1007011 OR CUS.ctype = 1007012 OR CUS.ctype = 1007013 OR CUS.ctype = 1007014) AND CUS.payment_type = 1 THEN '1'
							WHEN (CUS.ctype = 1007002 OR CUS.ctype = 1007004 OR CUS.ctype = 1007006) AND CUS.payment_type = 4 AND CUS.gst_no <> '' THEN '2'
							WHEN (CUS.ctype = 1007006 OR CUS.ctype = 1007004 ) AND (CUS.payment_type = 4 OR CUS.payment_type = 3) AND CUS.gst_no <> '' THEN '4'
							ELSE ''
						END AS  collection_type,
							CASE WHEN (CUS.ctype = 1007001 OR CUS.ctype = 1007003 OR CUS.ctype = 1007005 OR CUS.ctype = 1007009 OR CUS.ctype = 1007002 OR CUS.ctype = 1007008 OR CUS.ctype = 1007007 OR CUS.ctype = 1007011 OR CUS.ctype = 1007012 OR CUS.ctype = 1007013 OR CUS.ctype = 1007014) AND CUS.payment_type = 1 THEN 'POINT TO POINT'
							WHEN (CUS.ctype = 1007006 OR CUS.ctype = 1007004 OR CUS.ctype = 1007015) AND (CUS.payment_type = 4 OR CUS.payment_type = 3 OR CUS.payment_type = 2 ) AND (APP.direct_dispatch = 1)  THEN 'DIRECT PURCHASE'
							WHEN (CUS.ctype = 1007002 OR CUS.ctype = 1007004 OR CUS.ctype = 1007006 OR CUS.ctype = 1007015 OR CUS.ctype = 1007011 OR CUS.ctype = 1007012 OR CUS.ctype = 1007013 OR CUS.ctype = 1007014) AND (CUS.payment_type = 4 OR CUS.payment_type = 3 OR CUS.payment_type = 2) AND CUS.gst_no <> '' THEN 'SITE COLLECTION'
							WHEN (CUS.ctype = 1007010) AND (CUS.payment_type = 4 OR CUS.payment_type = 3) THEN 'FIXED PAYMENT'
							ELSE ''
						END AS  purchase_type_name,
						CASE WHEN CUS.payment_type = 1 THEN 'CASH'
							WHEN  CUS.payment_type = 2 THEN 'Cheque'
							WHEN  CUS.payment_type = 3 THEN 'NEFT / RTGS / Bank Transfer'
							WHEN  CUS.payment_type = 4 THEN 'NEFT / RTGS / Bank Transfer'
							ELSE ''
						END AS  payment_mode,
						DEPT.id as mrf_id,
						DEPT.department_name as mrf,
						DEPT.net_suit_code as mrf_ns_id,
						CUS.customer_id as vendor_id,
						CONCAT(CUS.first_name,' ',CUS.last_name) as vendor_name,
						-- CUS.net_suit_code as vendor_code,
						CASE WHEN CUS.ctype = 1007008 AND DEPT.id = 48 THEN 'C10107'
							WHEN  CUS.ctype = 1007008 AND DEPT.id = 22 THEN 'C10106'
							WHEN  CUS.ctype = 1007008 AND DEPT.id = 26 THEN 'C10108'
							ELSE  CUS.net_suit_code
						END AS  vendor_code,
						CUS.ctype as vendor_type_id,
						UPPER(PARA1.para_value) as vendor_type,
						APP.vehicle_id,
						VM.vehicle_number,
						WBM.collection_by as driver_id,
						CONCAT(U1.firstname,' ',U1.lastname) as driver_name,
						U1.net_suit_code as driver_net_suit_code,
						WBM.batch_id,
						WBM.code as batch_code,
						APP.invoice_no,
						'' as gts_name,
						WBM.audited_date as posting_date,
						'' as vendor_po_no,
						APP.invoice_date as tax_invoice_date,
						'' as challan_no,
						WBM.code as batch_no,
						APP.e_invoice_no as e_invoice_no,
						WBM.audited_date as updated_at,
						APP.company_id
						FROM wm_batch_master WBM
						LEFT JOIN wm_department DEPT ON WBM.master_dept_id = DEPT.id
						LEFT JOIN vehicle_master VM ON WBM.vehicle_id = VM.vehicle_id
						LEFT JOIN adminuser U1 ON WBM.collection_by = U1.adminuserid
						LEFT JOIN wm_batch_collection_map WBCM ON WBM.batch_id = WBCM.batch_id
						LEFT JOIN appointment_collection AC ON WBCM.collection_id = AC.collection_id
						LEFT JOIN appoinment APP ON AC.appointment_id = APP.appointment_id
						LEFT JOIN customer_master CUS ON APP.customer_id = CUS.customer_id
						LEFT JOIN parameter PARA1 ON CUS.ctype = PARA1.para_id
						WHERE WBM.is_audited = 1 AND WBM.audited_date BETWEEN '".$FROM."' and '".$TO."' 

				UNION ALL
					SELECT
					'INWARD_PLANT' AS record_type,
					IPD.id as record_id,
					0 as paid,
					'5' as collection_type,
					'CORPORATION INWARD' as purchase_type_name,
					'' AS payment_mode,
					DEPT.id as mrf_id,
					DEPT.department_name as mrf,
					DEPT.net_suit_code as mrf_ns_id,
					'' as vendor_id,
					CASE WHEN DEPT.id = 48 THEN 'COMMISSIONER MUNCIPAL CORPORATION - PUNE'
						WHEN  DEPT.id = 22 THEN 'COMMISSIONER MUNCIPAL CORPORATION - INDORE'
						WHEN  DEPT.id = 26 THEN 'CHIEF OFFICER - VAPI'
						ELSE NULL
					END AS  vendor_name,
					CASE WHEN DEPT.id = 48 THEN 'C10107'
						WHEN  DEPT.id = 22 THEN 'C10106'
						WHEN  DEPT.id = 26 THEN 'C10108'
						ELSE NULL
					END AS  vendor_code,
					0 as vendor_type_id,
					'' as vendor_type,
					IPD.vehicle_id,
					VM.vehicle_number,
					0 as driver_id,
					'' as driver_name,
					'' as driver_net_suit_code,
					'' as batch_id,
					'' as batch_code,
					'' as invoice_no,
					GNM.gts_name as gts_name,
					IPD.created_at as posting_date,
					'' as vendor_po_no,
					'' as tax_invoice_date,
					'' as challan_no,
					'' as batch_no,
					'' as e_invoice_no,
					IPD.updated_at,
					IPD.company_id
					FROM inward_plant_details AS IPD
					LEFT JOIN inward_vehicle_master VM ON IPD.vehicle_id = VM.id
					LEFT JOIN wm_department DEPT ON IPD.mrf_id = DEPT.id
					LEFT JOIN gts_name_master GNM ON IPD.gts_name_id = GNM.id
					LEFT JOIN company_product_master CPM ON IPD.product_id = CPM.id
					LEFT JOIN company_product_quality_parameter CPQP ON IPD.product_id = CPQP.product_id
					LEFT JOIN parameter PARA ON CPM.para_unit_id = PARA.para_id
					WHERE IPD.updated_at BETWEEN '".$FROM."' and '".$TO."'";
		$DATA 		= DB::select($SQL);
		if(!empty($DATA)){
			foreach($DATA AS $KEY => $VALUE){
				NetSuitPurchaseTransactionMaster::updateOrCreate([
					"record_type"	=> $VALUE->record_type,
					"record_id"		=> $VALUE->record_id
				],
				[
					"record_type" 				=> $VALUE->record_type,
					"record_id" 				=> $VALUE->record_id,
					"collection_type" 			=> $VALUE->collection_type,
					"purchase_type" 			=> $VALUE->purchase_type_name,
					"payment_mode" 				=> $VALUE->payment_mode,
					"paid" 						=> $VALUE->paid,
					"mrf_id" 					=> $VALUE->mrf_id,
					"mrf" 						=> $VALUE->mrf,
					"mrf_ns_id" 				=> $VALUE->mrf_ns_id,
					"vendor_id" 				=> $VALUE->vendor_id,
					"vendor_name" 				=> $VALUE->vendor_name,
					"vendor_code" 				=> $VALUE->vendor_code,
					"vendor_type_id" 			=> $VALUE->vendor_type_id,
					"vendor_type" 				=> $VALUE->vendor_type,
					"vehicle_id" 				=> $VALUE->vehicle_id,
					"vehicle_number" 			=> $VALUE->vehicle_number,
					"driver_id" 				=> $VALUE->driver_id,
					"driver_name" 				=> $VALUE->driver_name,
					"driver_net_suit_code" 		=> $VALUE->driver_net_suit_code,
					"batch_id" 					=> $VALUE->batch_id,
					"batch_code" 				=> $VALUE->batch_code,
					"invoice_no" 				=> $VALUE->invoice_no,
					"gts_name" 					=> $VALUE->gts_name,
					"posting_date" 				=> $VALUE->posting_date,
					"vendor_po_no" 				=> $VALUE->vendor_po_no,
					"tax_invoice_date" 			=> $VALUE->tax_invoice_date,
					"challan_no" 				=> $VALUE->challan_no,
					"e_invoice_no" 				=> $VALUE->e_invoice_no,
					"company_id" 				=> $VALUE->company_id,
					"created_at" 				=> date("Y-m-d H:i:s"),
					"updated_at" 				=> date("Y-m-d H:i:s"),
				]);
				if($VALUE->record_type == "APPOINTMENT"){
					$SQL1 = "SELECT  CPM.id as item_id,
								CPM.net_suit_code as item_code,
								CPM.name as item_name,
								CPQP.parameter_name as item_quality,
							        CPM.hsn_code as item_hsn_code,
								UPPER(PARA.para_value) as uom,
								SUM(WBAP.qty) as net_qty,
								WBPD.wrong_product_id as wrong_product_id,
								'0' as item_price,
								0 as total_value,
								P1.para_value as item_class,
								P2.para_value as item_department
					FROM wm_batch_product_detail as WBPD
					LEFT JOIN wm_batch_audited_product as WBAP on WBAP.id = WBPD.id
					LEFT JOIN company_product_master CPM ON WBPD.product_id = CPM.id
					LEFT JOIN parameter P1 ON CPM.net_suit_class = P1.para_id
					LEFT JOIN parameter P2 ON CPM.net_suit_department = P2.para_id
					LEFT JOIN company_product_quality_parameter CPQP ON WBPD.product_quality_para_id = CPQP.company_product_quality_id
					LEFT JOIN parameter PARA ON WBPD.product_para_unit_id = PARA.para_id
					WHERE WBPD.batch_id = ".$VALUE->record_id." GROUP BY WBPD.id";
					$GET_PRODUCT = DB::select($SQL1);
					if(!empty($GET_PRODUCT)){
						\DB::table("net_suit_purchase_transaction_product_master")->where("trn_id",$VALUE->record_id)->delete();
						$collection_id = WmBatchCollectionMap::where("batch_id",$VALUE->record_id)->pluck("collection_id");
						foreach($GET_PRODUCT AS $RAW){
							########## AVG PRICE LOGIC ############
							/* NOW ONWARD IF THERE IS ANY NEW PRODUCT COME WHICH IS NOT IN COLLECTION THEN ITS AVG PRICE WILL CONSIDER AGAINST THE PRODUCT WHICH IS ENTER BY USER BY MISTAKE - 13 DEC 2021*/
							$AUDITED_PRODUCT_ID = $RAW->item_id;
							if($RAW->wrong_product_id > 0){
								$AUDITED_PRODUCT_ID = $RAW->wrong_product_id;
							}
							$COLL_QTY = AppointmentCollectionDetail::where("product_id",$AUDITED_PRODUCT_ID)
							->whereIn("collection_id",$collection_id)
							->sum("actual_coll_quantity");
							$TOTAL_VALUE = AppointmentCollectionDetail::where("product_id",$AUDITED_PRODUCT_ID)
							->whereIn("collection_id",$collection_id)
							->sum("price");
							$AVG_PRICE 		= 0;
							$GRAND_TOTAL 	= 0;
							$TOTAL_QTY 		= $RAW->net_qty;

							if(!empty($TOTAL_VALUE)){
								$AVG_PRICE 	= ($TOTAL_QTY > 0) ? ($TOTAL_VALUE / $TOTAL_QTY) : 0;
							}

							// $BATCH_AVG_PRICE_DATA = WmBatchProductDetailsProcessAvgPrice::where("product_id",$AUDITED_PRODUCT_ID)->where("batch_id",$VALUE->record_id)->first();
							// $AVG_PRICE 	= ($BATCH_AVG_PRICE_DATA) ? $BATCH_AVG_PRICE_DATA->avg_price : $AVG_PRICE;
							$array = array(
								"trn_id" 			=> $VALUE->record_id,
								"record_type" 		=> $VALUE->record_type,
								"material_type" 	=> ($RAW->item_id == FOC_PRODUCT) ? "NON RECYCLABLE" : "RECYCLABLE",
								"item_id" 			=> $RAW->item_id,
								"item_code" 		=> $RAW->item_code,
								"item_class" 		=> $RAW->item_class,
								"item_department" 	=> $RAW->item_department,
								"item_name" 		=> $RAW->item_name,
								"item_quality" 		=> $RAW->item_quality,
								"item_hsn_code" 	=> $RAW->item_hsn_code,
								"uom" 				=> (!empty($RAW->uom)) ? $RAW->uom : "KG" ,
								"collection_qty" 	=> abs($COLL_QTY),
								"net_qty" 			=> abs($RAW->net_qty),
								"item_price" 		=> $AVG_PRICE,
								"total_value" 		=> ($AVG_PRICE > 0) ? _FormatNumberV2(abs($RAW->net_qty * $AVG_PRICE)) : 0,
								"created_at" 		=> date("Y-m-d H:i:s"),
								"updated_at" 		=> date("Y-m-d H:i:s"),
							);
							\DB::table("net_suit_purchase_transaction_product_master")->insert($array);
						}
					}
				}
				if($VALUE->record_type == "INWARD_PLANT"){
					$SQL2 = "SELECT
								CPM.id as item_id,
								CPM.net_suit_code as item_code,
								CPM.name as item_name,
								CPQP.parameter_name as item_quality,
							    CPM.hsn_code as item_hsn_code,
								UPPER(PARA.para_value) as uom,
								IPD.inward_qty as net_qty,
								'0' as item_price,
								0 as total_value,
								P1.para_value as item_class,
								P2.para_value as item_department
					FROM inward_plant_details as IPD
					LEFT JOIN company_product_master CPM ON IPD.product_id = CPM.id
					LEFT JOIN parameter P1 ON CPM.net_suit_class = P1.para_id
					LEFT JOIN parameter P2 ON CPM.net_suit_department = P2.para_id
					LEFT JOIN company_product_quality_parameter CPQP ON CPM.id = CPQP.product_id
					LEFT JOIN parameter PARA ON CPM.para_unit_id = PARA.para_id
					WHERE IPD.id 	= ".$VALUE->record_id;
					$GET_PRO 		= DB::select($SQL2);
					if(!empty($GET_PRO)){
						\DB::table("net_suit_purchase_transaction_product_master")->where("trn_id",$VALUE->record_id)->delete();
						foreach($GET_PRO AS $RAW){
							$array = array(
								"trn_id" 			=> $VALUE->record_id,
								"record_type" 		=> $VALUE->record_type,
								"material_type" 	=> ($RAW->item_id == FOC_PRODUCT) ? "NON RECYCLABLE" : "RECYCLABLE",
								"item_id" 			=> $RAW->item_id,
								"item_code" 		=> $RAW->item_code,
								"item_class" 		=> $RAW->item_class,
								"item_department" 	=> $RAW->item_department,
								"item_name" 		=> $RAW->item_name,
								"item_quality" 		=> $RAW->item_quality,
								"item_hsn_code" 	=> $RAW->item_hsn_code,
								"uom" 				=> $RAW->uom,
								"net_qty" 			=> abs($RAW->net_qty),
								"item_price" 		=> $RAW->item_price,
								"total_value" 		=> $RAW->total_value,
								"created_at" 		=> date("Y-m-d H:i:s"),
								"updated_at" 		=> date("Y-m-d H:i:s"),
							);
							\DB::table("net_suit_purchase_transaction_product_master")->insert($array);
						}
					}
				}
			}
		}

	}
	/*
	Use 	: send purchase data to net suit
	Author  : Axay Shah
	Date 	: 30 Sept 2021
   	*/
	public static function SendPurchaseTransactionDataToNetSuit($request){

		// return false;
		$ID 		= NetSuitApiLogMaster::AddRequest($request->all(),"PURCHASE_TRANSACTION",1);
		$date 		= (isset($request->date) && !empty($request->date)) ?  date("Y-m-d H:i:s",strtotime($request->date)) : "";
		$record_id 	= (isset($request->record_id) && !empty($request->record_id)) ?  $request->record_id : "";
		$WHERE 		= "";
		$data 		= "";
		if($record_id > 0){
			$WHERE .= " WHERE record_id = ".$record_id." ";
		}
		if(!empty($date)){
			if($record_id > 0){
				$WHERE .= "AND created_at >= '".$date."' ";
			}else{
				$WHERE .= "  WHERE created_at >= '".$date."' ";
			}
		}
		$FROM_DATE 	= date('Y-m-d',strtotime("-10 days"));
		// $FROM_DATE 	= "2023-06-01";
		$TO_DATE 	= date("Y-m-d");
		
		
		$WHERE = "  WHERE posting_date <= '".$TO_DATE."' and posting_date >= '".$FROM_DATE."'";


		// $WHERE = 	"WHERE record_id in (93407,93498,93499,93106,93651,93657,93676,93683,93685,93732,93735,93737,93794,93806,93829,93866,93939,93963,93965,93953,93972,93900,94044,94048,94065,94087,94130,94165,94235,94172,94292,94296,94308,94344,94359,94360,94362,94413,94426,94428,94451,94459,94475,94482,94526,94536,94558,94581,94582,94589,94631,94633,94634,94642,94662,94668,94669,94672,94707)";

		$flag 		= false;
		$SQL = 		"(SELECT
						record_id,
						record_type,
						collection_type,
						purchase_type,
						mrf,
						mrf_ns_id,
						vendor_id,
						vendor_code,
						vendor_type_id,
						vendor_type,
						vendor_name,
						vehicle_number,
						driver_name,
						driver_net_suit_code,
						payment_mode,
						gts_name,
						posting_date,
						vendor_po_no,
						tax_invoice_date,
						challan_no,
						batch_code,
						e_invoice_no,
						paid
					FROM
						net_suit_purchase_transaction_master
						$WHERE
						GROUP BY record_id
						ORDER BY record_id)";
			$data = \DB::select($SQL);
			if(!empty($data)){
				foreach($data as $key => $value){
					$data[$key]->vendor_code 			=  (empty($value->vendor_code) || $value->vendor_code == "null") ? NULL : $value->vendor_code;
					$data[$key]->driver_net_suit_code 	=  (empty($value->driver_net_suit_code) || $value->driver_net_suit_code == "null") ? NULL : $value->driver_net_suit_code;
					$itemData = \DB::table("net_suit_purchase_transaction_product_master")->select("material_type","item_id","item_code","item_name","item_hsn_code","uom","collection_qty","net_qty","item_price","total_value","item_class","item_department")
					->where("record_type",$value->record_type)
					->where("trn_id",$value->record_id);

					// if(strtolower(str_replace(" ","",$value->purchase_type)) == "pointtopoint" && empty($value->vendor_code)){
					// 	$data[$key]->vendor_code = $value->driver_net_suit_code;
					// 	$data[$key]->vendor_name = $value->driver_name;
					// }
					$VENDOR_CODE_PLANT 	= array('C10108','C10107','C10106');
					$VENDOR_TYPE_IDS 	= array(CUSTOMER_TYPE_COMMERCIAL,CUSTOMER_TYPE_COMMERCIAL_FIX_PAYMENT,CUSTOMER_TYPE_INDUSTRIAL,CUSTOMER_TYPE_AGGREGATOR,CUSTOMER_TYPE_BULK_AGGREGATOR,CUSTOMER_TYPE_CFM);
					if (in_array($value->vendor_type_id,$VENDOR_TYPE_IDS) || 
						(in_array($value->vendor_code,$VENDOR_CODE_PLANT) && $data[$key]->record_type == "INWARD_PLANT"))
					{
					  $data[$key]->driver_net_suit_code = "";
					  $data[$key]->driver_name 			= "";
					}
					else
					{
					   $data[$key]->vendor_code = "";
					   $data[$key]->vendor_name = "";
					}
					$GetItemData 			= $itemData->get()->toArray();
					$data[$key]->item_list 	= $GetItemData;
					$class 					= ""; 
					$dept 					= "";
					$ClassDeptData = \DB::table("net_suit_purchase_transaction_product_master")
					->select("item_class","item_department")
					->where("record_type",$value->record_type)
					->where("trn_id",$value->record_id)
					->whereNotNull("item_class")
					->whereNotNull("item_department")
					->first();
					$class 	= (isset($ClassDeptData->item_class) && !empty($ClassDeptData->item_class)) ? $ClassDeptData->item_class : "";
					$dept 	= (isset($ClassDeptData->item_department) && !empty($ClassDeptData->item_department)) ? $ClassDeptData->item_department : "";
					$data[$key]->class 	=  $class;
					$data[$key]->dept 	=  $dept;
				}
			}
			$counter 	= sizeof($data);
			$NewArr 	= array();
			$TRANSFER_DATA = self::SendTransferPurchaseDataToNetSuit($FROM_DATE,$TO_DATE);
			if(!empty($TRANSFER_DATA)){
				foreach($TRANSFER_DATA AS $T_KEY => $T_VALUE){
					$data[] = $T_VALUE;
				}
			}

		NetSuitApiLogMaster::UpdateRequest($ID,json_encode($data));
		return $data;
   	}
   	/*
	Use 	: send transfer purchase data to net suit
	Author  : Axay Shah
	Date 	: 30 Sept 2021
   	*/
	public static function SendTransferPurchaseDataToNetSuit($startDate,$endDate,$recordId=0){
		$WHERE 		= "";
		$data 		= "";
		$WHERE 		= " WHERE invoice_date BETWEEN '".$startDate."' and '".$endDate."'";
		$flag 		= false;
		$SQL 		= "(
						SELECT
						invoice_no as record_id,
						'APPOINTMENT' as record_type,
						0 as collection_type,
						'SITE COLLECTION' as purchase_type,
						client_mrf_name as mrf,
						client_mrf_ns_id as mrf_ns_id,
						client_id as vendor_id,
						client_code as vendor_code,
						'1007006' as vendor_type_id,
						'AGGREGATOR' as vendor_type,
						client_name as vendor_name,
						vehicle_no as vehicle_number,
						'' as driver_name,
						'' as driver_net_suit_code,
						'Cheque' as payment_mode,
						'' as gts_name,
						invoice_date as posting_date,
						'' as vendor_po_no,
						invoice_date as tax_invoice_date,
						invoice_no as challan_no,
						invoice_no as batch_code,
						e_invoice_no,
						paid,
						product_class as class,
						product_department as dept
					FROM
						net_suit_transfer_sales_transaction_master
						$WHERE
						GROUP BY record_id
						ORDER BY record_id)";
						// echo $SQL;
						// exit;
			$data = \DB::select($SQL);
			if(!empty($data)){
				foreach($data as $key => $value){
					$data[$key]->vendor_code 			=  (empty($value->vendor_code) || $value->vendor_code == "null") ? NULL : $value->vendor_code;
					$data[$key]->driver_net_suit_code 	=  (empty($value->driver_net_suit_code) || $value->driver_net_suit_code == "null") ? NULL : $value->driver_net_suit_code;
					$itemData = \DB::table("net_suit_transfer_sales_transaction_master")->select("material_type","product_id as item_id","product_code as item_code","product_name as item_name","hsn_code as item_hsn_code","uom","sales_qty as collection_qty","sales_qty as net_qty","product_rate as item_price","net_amount as total_value")
					->where("invoice_no",$value->record_id);
					$VENDOR_TYPE_IDS 	= array(CUSTOMER_TYPE_COMMERCIAL,CUSTOMER_TYPE_COMMERCIAL_FIX_PAYMENT,CUSTOMER_TYPE_INDUSTRIAL,CUSTOMER_TYPE_AGGREGATOR,CUSTOMER_TYPE_BULK_AGGREGATOR);
					if (in_array($value->vendor_type_id,$VENDOR_TYPE_IDS))
					{
					  $data[$key]->driver_net_suit_code = "";
					  $data[$key]->driver_name 			= "";
					}
					else
					{
					   $data[$key]->vendor_code = "";
					   $data[$key]->vendor_name = "";
					}
					$GetItemData 			= $itemData->get()->toArray();
					$data[$key]->item_list 	= $GetItemData;
				}
			}
		return $data;
   	}

   	/*
	Use 	: Export Net suit Purchase Product Master
	Author  : Hasmukhi Patel
	Date 	: 30 Sept 2021
   	*/
   	public static function ExportPurchaseProductMaster($request){
   		$start_date = (isset($request->start_date) && (!empty($request->start_date)) ? date("Y-m-d",strtotime($request->start_date)) : date('Y-m-d'));
   		$end_date 	= (isset($request->end_date) && (!empty($request->end_date)) ?  date("Y-m-d",strtotime($request->end_date)) : date('Y-m-d'));
   		$data 		= self::select('net_suit_purchase_transaction_master.*',
		   				'net_suit_purchase_transaction_master.id as net_suit_purchase_transaction_master_id',
		   				'NSPTPM.*',
		   				'net_suit_purchase_transaction_master.created_at as created_date',
		   				'net_suit_purchase_transaction_master.updated_at as updated_date'
   					)
					->leftjoin('net_suit_purchase_transaction_product_master as NSPTPM',"NSPTPM.trn_id","net_suit_purchase_transaction_master.record_id")
					->whereBetween('net_suit_purchase_transaction_master.posting_date', [$start_date, $end_date])
					->get()->toArray();
		return $data;
   	}


}
