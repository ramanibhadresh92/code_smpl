<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Mobile\Http\Requests\Login;
use Illuminate\Support\Facades\Auth;

use App\Models\WmClientMaster;
use App\Models\WmDepartment;
use App\Models\LocationMaster;
use App\Models\CompanyMaster;
use App\Models\WmSalesMaster;
use App\Models\WmDispatch;
use App\Models\WmProductMaster;
use App\Models\GSTStateCodes;
use App\Models\ShippingAddressMaster;
use App\Models\WmDispatchProduct;
use App\Models\ProductInwardLadger;
use App\Models\InvoiceAdditionalCharges;
use App\Models\AdminUser;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use DB;
class WmInvoicePaymentLog extends Model implements Auditable
{
	protected 	$table 		=	'wm_invoice_payment_log';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	private 	$exclude_cat=	array("RDF");
	use AuditableTrait;
	
	/*
	use 	: Get Last invoice id
	Author 	: Axay Shah
	Date 	: 04 July,2019
	*/
	public static function saveRequest($request,$post,$data,$form) {
		$invoice_id = $post['invoice_id'];
		$invoice_no = $post['invoice_no'];
		$order_id = $post['order_id'];
		
		/*$request = base64_encode(json_encode($request->all(), true));
		$post = base64_encode(json_encode($post, true));
		$data = base64_encode($data);
		$form = base64_encode($form);*/

		$request = json_encode($request->all(), true);
		$post = json_encode($post, true);

		if(isset(Auth()->user()->adminuserid) && !empty(Auth()->user()->adminuserid)) {
			$adminuserid = Auth()->user()->adminuserid;
		} elseif(isset(Auth()->user()->id) && !empty(Auth()->user()->id)) {
			$adminuserid = Auth()->user()->id;
		} else {
			$adminuserid = 0;
		}


		return self::insert(["order_id"=>$order_id,"reference_id"=>$invoice_id,"invoice_id"=>$invoice_id,"invoice_no"=>$invoice_no,"request" => $request,"post" => $post,"data" => $data,"form" => $form,'created_by' => $adminuserid,'created_at'=>date('Y-m-d H:i:s')]);
	}

	/*
	Author  : Bhadresh Ramani
	Date    : 30,Jan 2024
	*/
	public static function saveResponce($request) {
		$order_id = $request['order_id'];
		$tid = $request['tracking_id'];
		$payment_responce_status = $request['order_status'];
		$q = self::where("order_id",$order_id)->update(["payment_responce"=>json_encode($request, true), "payment_responce_status" => $payment_responce_status, "tid" => $tid]);
		if($q) {
			$data = self::select(['request','created_by'])->where("order_id",$order_id)->first();
			if(!empty($data)) {
				$row = json_decode($data->request, true);
				$company_id = self::getUserCompanyId($data->created_by);
				$row['created_by'] = $data->created_by;
				$row['company_id'] = $company_id;				
				$row['received_amount'] = $request['amount'];		
				$row['invoice_amount'] = $request['amount'];	
				return WmPaymentReceive::AddPaymentReceive($row);
			}
		}
	}

	/*
	Author  : Bhadresh Ramani
	Date    : 30,Jan 2024
	*/
	public static function getPaymentStatus($orderID, $tid) {
		$data = self::select(['payment_responce_status','payment_responce'])->where("order_id",$orderID)->where("tid",$tid)->first();
		if(!empty($data)) {
			$data = $data->toArray();			
			return $data; 
		}
	}

	/*
	Author  : Bhadresh Ramani
	Date    : 30,Jan 2024
	*/
	public static function getUserCompanyId($uid) {
		$data = AdminUser::select(['company_id'])->where('adminuserid',$uid)->first();
		if(!empty($data)) {
			return $data->company_id;
		}
	}

	/*
	Author  : Bhadresh Ramani
	Date    : 30,Jan 2024
	*/
	public static function getResponce($tid,$order_id) {
		return self::select('payment_responce','payment_responce_status')->where('tid',$tid)->where('order_id',$order_id)->first();
	}

}
