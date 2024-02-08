<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmDispatch;
use App\Models\AdminUser;
use App\Models\LocationMaster;
use App\Models\WmClientMaster;
use App\Models\WmDepartment;
use App\Models\WmProductMaster;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Mail;
use DB;
use Log;
use PDF;

class WmDispatchProduct extends Model implements Auditable
{
    protected 	$table 		=	'wm_dispatch_product';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	false;
	use AuditableTrait;
	/*
	Use 	: Get Product By Dispatch Id
	Author 	: Axay Shah
	Date 	: 25 June,2019
	*/
	public static function GetProductByDispatchId($dispatchId = 0){
		$table 		= (new static)->getTable();
		$product 	= new WmProductMaster();
		$data 		= self::select("$table.*","P.title","P.hsn_code")
		->join($product->getTable()." AS P","$table.product_id","=","P.id")
		->where("dispatch_id",$dispatchId)
		->get();
		return $data;
	}

	/*
	Use 	: Get Primary Key for Sales Master Mapping
	Author 	: Kalpak Prajapati
	Date 	: 08 March,2020
	*/
	public static function GetDispatchProductId($arrFilter=array())
	{
		$DispatchProductID = 0;
		if (empty($arrFilter)) return $DispatchProductID;
		$PRODUCT_ID 	= isset($arrFilter['product_id'])?$arrFilter['product_id']:0;
		$DISPATCH_ID 	= isset($arrFilter['dispatch_id'])?$arrFilter['dispatch_id']:0;
		$QUANTITY 		= isset($arrFilter['quantity'])?$arrFilter['quantity']:0;
		$table 			= (new static)->getTable();
		$product 		= new WmProductMaster();
		$SelectRows 	= self::select("id")
							->where("dispatch_id",$DISPATCH_ID)
							->where("product_id",$PRODUCT_ID)
							->where("quantity",$QUANTITY)
							->get()
							->toArray();
		if (!empty($SelectRows)) {
			foreach ($SelectRows as $SelectRow) {
				$DispatchProductID = isset($SelectRow['id'])?$SelectRow['id']:0;
			}
		}
		return $DispatchProductID;
	}

	public static function GetApprovalPendingDispatches($CompanyID,$StartTime,$EndTime,$arrFilter=array(),$Json=false)
	{
		$WmDispatch 		= new WmDispatch;
		$AdminUser          = new AdminUser;
		$LocationMaster     = new LocationMaster;
		$WmClientMaster 	= new WmClientMaster;
		$WmDepartment 		= new WmDepartment;
		$WmProductMaster 	= new WmProductMaster;
		$WmDispatchProduct  = (new self)->getTable();
		$ReportSql  		=  self::select(DB::raw("DATE_FORMAT(WD.dispatch_date,'%Y-%m-%d') as Dispatch_Date"),
											DB::raw("WD.challan_no"),
											DB::raw("WD.client_master_id"),
											DB::raw("WCM.client_name"),
											DB::raw("WPM.title"),
											DB::raw($WmDispatchProduct.".quantity"),
											DB::raw($WmDispatchProduct.".price"),
											DB::raw("(".$WmDispatchProduct.".quantity * ".$WmDispatchProduct.".price) as Amount"),
											DB::raw("CONCAT(AU.firstname,' ',AU.lastname) AS Dispatch_Generated_By"),
											DB::raw($LocationMaster->getTable().".city as City_Name"));
		$ReportSql->leftjoin($WmDispatch->getTable()." AS WD","WD.id","=",$WmDispatchProduct.".dispatch_id");
		$ReportSql->leftjoin($WmClientMaster->getTable()." AS WCM","WD.client_master_id","=","WCM.id");
		$ReportSql->leftjoin($WmProductMaster->getTable()." AS WPM","WPM.id","=",$WmDispatchProduct.".product_id");
		$ReportSql->leftjoin($AdminUser->getTable()." AS AU","WD.created_by","=","AU.adminuserid");
		$ReportSql->leftjoin($WmDepartment->getTable()." AS WDM","WDM.id","=","WD.master_dept_id");
		$ReportSql->leftjoin($LocationMaster->getTable(),"WDM.location_id","=",$LocationMaster->getTable().".location_id");
		$ReportSql->where("WDM.company_id",$CompanyID);
		$ReportSql->where("WD.approval_status",0);
		$ReportSql->whereBetween("WD.dispatch_date",[$StartTime,$EndTime]);
		if (isset($arrFilter['city_id']) && !empty($arrFilter['city_id']) && is_array($arrFilter['city_id'])) {
			$ReportSql->whereIn("WDM.location_id",$arrFilter['city_id']);
		}
		$ReportSql->orderBy($LocationMaster->getTable().".city","ASC");
		$ReportSql->orderBy("WD.dispatch_date","ASC");
		$ReportSql->orderBy("WCM.client_name","ASC");
		$Dispatches 	= $ReportSql->get()->toArray();
		$Attachments 	= array();
		if (!empty($Dispatches))
		{
			$result 				= array();
			$arrChallan 			= array();
			foreach ($Dispatches as $SelectRow)
			{
				if (in_array($SelectRow['challan_no'],$arrChallan)) {
					$result[$SelectRow['challan_no']]['Products'][] 	= array("Product"=>$SelectRow['title'],
																				"Qty"=>_FormatNumberV2($SelectRow['quantity']),
																				"Price"=>_FormatNumberV2($SelectRow['price']),
																				"Amount"=>_FormatNumberV2($SelectRow['Amount']));
				} else {
					$result[$SelectRow['challan_no']]['Dispatch_Date']      	= $SelectRow['Dispatch_Date'];
					$result[$SelectRow['challan_no']]['challan_no']     		= $SelectRow['challan_no'];
					$result[$SelectRow['challan_no']]['Dispatch_Generated_By']  = $SelectRow['Dispatch_Generated_By'];
					$result[$SelectRow['challan_no']]['Client_Name']  			= $SelectRow['client_name'];
					$result[$SelectRow['challan_no']]['City_Name']  			= $SelectRow['City_Name'];
					$result[$SelectRow['challan_no']]['Products'][] 		= array("Product"=>$SelectRow['title'],
																					"Qty"=>_FormatNumberV2($SelectRow['quantity']),
																					"Price"=>_FormatNumberV2($SelectRow['price']),
																					"Amount"=>_FormatNumberV2($SelectRow['Amount']));
					array_push($arrChallan,$SelectRow['challan_no']);
				}
			}
			if (!$Json) {
				$FILENAME           = "Dispatch_Approval_Pending_".date("Y-m-d",strtotime($StartTime))."_".date("Y-m-d",strtotime($EndTime))."_".getRandomNumber().".pdf";
				$REPORT_START_DATE  = date("Y-m-d",strtotime($StartTime));
				$REPORT_END_DATE    = date("Y-m-d",strtotime($EndTime));
				$Title              = "Dispatch Approval Pending ".$REPORT_START_DATE." To ".$REPORT_END_DATE;
				$Foc                = 0;
				$pdf = PDF::loadView('email-template.dispatch_approval_pending', compact('result','Title'));
				$pdf->setPaper("A4", "landscape");
				ob_get_clean();
				$path           = public_path("/").PATH_COLLECTION_RECIPT_PDF;
				$PDFFILENAME    = $path.$FILENAME;
				if (!is_dir($path)) {
					mkdir($path, 0777, true);
				}
				$pdf->save($PDFFILENAME, true);
				array_push($Attachments,$PDFFILENAME);
			} else {
				if (!empty($result)) {
					return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_FOUND'),
									'data'=>$result]);
				} else {
					return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_NOT_FOUND'),
									'data'=>array()]);
				}
			}
		}
		return $Attachments;
	}

	/*
	Use 	: EPR Rate Update
	Date 	: 26 May 2021
	Author 	: Axay Shah
	*/
	public static function UpdateEPRrate($request){
		$product 		= (isset($request->product) && !empty($request->product)) ? $request->product : "";
		$dispatch_id 	= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : "";
		$incentive 		= (isset($request->incentive) && !empty($request->incentive)) ? $request->incentive : 0;
		$service_cost 	= (isset($request->service_cost) && !empty($request->service_cost)) ? $request->service_cost : 0;
		$transportation_cost = (isset($request->transportation_cost) && !empty($request->transportation_cost)) ? $request->transportation_cost : 0;
		if(!empty($product))
		{
			$flag = false;
			foreach($product as $value){
				self::where("id",$value['id'])->update(["epr_rate"=>$value['epr_rate']]);
				$flag = true;
			}
			if($flag){
				WmDispatch::where("id",$dispatch_id)->update(["epr_rate_added"=>1,'incentive'=>$incentive,'service_cost'=>$service_cost,'transportation_cost'=>$transportation_cost]);
			}
			return true;
		}
		return false;
	}

	/*
	Use 	: Get Product Dispatch Qty By MRF
	Date 	: 12 Sep 2022
	Author 	: Kalpak Prajapati
	*/
	public static function GetProductDispatchByMRF($DispatchDate,$MRF_ID,$CLIENT_ID,$PRODUCT_ID)
	{
		$WmDispatchProduct 	= "	SELECT SUM(wm_dispatch_product.quantity) AS Dispatch_Qty
								FROM wm_dispatch_product
								INNER JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
								WHERE wm_dispatch_product.product_id = $PRODUCT_ID
								AND wm_dispatch.client_master_id = $CLIENT_ID
								AND wm_dispatch.master_dept_id = $MRF_ID
								AND wm_dispatch.dispatch_date = '$DispatchDate'
								AND wm_dispatch.approval_status = 1";
		$SELECTRES 			= DB::connection('master_database')->select($WmDispatchProduct);
		$Dispatch_Qty 		= isset($SELECTRES[0]->Dispatch_Qty)?$SELECTRES[0]->Dispatch_Qty:0;
		return $Dispatch_Qty;
	}
	
	/*
	Use 	: Get Product Details of Dispatch ID
	Date 	: 09  Jan 2023
	Author 	: Axay Shah
	*/
	public static function GetProductListByDispatchIDS($DispatchIds)
	{
		$table 		= (new static)->getTable();
		$product 	= new WmProductMaster();
		$data 		= self::select(
						"$table.*",
						"P.title",
						"P.hsn_code",
						"WDM.challan_no"
					)
		->join($product->getTable()." AS P","$table.product_id","=","P.id")
		->join("wm_dispatch AS WDM","$table.dispatch_id","=","WDM.id")
		->whereIn("dispatch_id",$DispatchIds)
		->get();
		return $data;
	}
}