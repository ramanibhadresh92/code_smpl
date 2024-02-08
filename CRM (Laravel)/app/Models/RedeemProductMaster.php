<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RedeemProductOrder;
use App\Models\RedeemProductOrderDetail;
use Carbon\Carbon;
use App\Models\CustomerMaster;
use PDF;
use App\Facades\LiveServices;

class RedeemProductMaster extends Model
{
    protected 	$table 		=	'redeem_product_master';
    protected 	$guarded 	=	['product_id'];
    public 		$timestamps = 	false;

    /**
    * Function Name : saveRedeemProductOrderRequest
    * @return
    * @author Sachin Patel
    * @date 23 April, 2019
    */
    public static function saveRedeemProductOrderRequest($request){
        \DB::beginTransaction();
        $result     = json_decode(isset($request->result)?stripslashes($request->result):"");
        $data       = isset($result->products)?$result->products:"";
        $success = 0;
        if(!empty($data) && !empty($request->clscustomer_customer_id)){
            $saveorder = self::saveProductOrder($request);
            
            $totalAmount = 0;
            foreach($data as $key => $value){
                $orderDetail = array();
                $totalAmount += $value->amount;  
                $orderDetail['product_id']      = $value->product_id;
                $orderDetail['qty']             = $value->quantity;
                $orderDetail['product_price']   = $value->price;
                $orderDetail['amount']          = $value->amount;
                $orderDetail['order_id']        = $saveorder->order_id;  
                self::saveProductOrderDetail($orderDetail);
            }
            self::updateProductOrderAmount($saveorder->order_id,$totalAmount);
            self::updateProductOrderDeliveryDate($saveorder->order_id);
            //self::SendNotificationtoCustomer($request->clscustomer_customer_id,"Thank you for order product.");
        }
        \DB::commit();
        $success = 1;
        return $success;
    }

    /**
    * Function Name : pageadminListProductOrder
    * @return
    * @author Sachin Patel
    * @date 23 April, 2019
    */
    public static function pageadminListProductOrder($request,$currentPage,$withOrderDetail){

    	\Illuminate\Pagination\Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

    	$query = RedeemProductOrder::select('redeem_product_order.*')
	    		->addSelect(\DB::raw("IF (CM.last_name != '',CONCAT(CM.first_name,' ',CM.last_name),CM.first_name) As customer_name"))
	    		->addSelect(\DB::raw("DATE_FORMAT(redeem_product_order.created_date,'%Y-%m-%d') as date_create"))
	    		->join('customer_master as CM','CM.customer_id','=','redeem_product_order.customer_id');
		
		if(isset($request->clsredeemproduct_order_id) && $request->clsredeemproduct_order_id !=""){	    		
	 	   	$query->where('redeem_product_order.order_id',$request->clsredeemproduct_order_id);
		}

		if(isset($request->clscustomer_customer_id) && $request->clscustomer_customer_id !=""){
	 		$query->where('redeem_product_order.customer_id',$request->clscustomer_customer_id);
		}
        
		$result = $query->orderBy('order_id','DESC')->paginate()->toArray();
		if($withOrderDetail){
			foreach($result['result'] as $key => $order){
	       		    $result['result'][$key]['order_details'] = self::GetProductOrderInvoiceDetail($order);
	        }
    	}

		$data['result']         = $result['result'];
        $data['total_record']   = $result['totalElements'];
        $data['current_page']   = $result['pageNumber'];
        $data['totalPages']     = $result['totalPages'];
        $data['rec_per_page']   = $result['size'];
		return $data;
    }

    /**
    * Function Name : GetProductOrderInvoiceDetail
    * @return
    * @author Sachin Patel
    * @date 23 April, 2019
    */
    public static function GetProductOrderInvoiceDetail($order){
    	$data =  RedeemProductOrderDetail::select('redeem_product_order_detail.*','PM.product_image','PM.product_name','OM.order_date','LM.city as city_name','LMS.state as state_name',\DB::raw('"India" AS country_name'),'CM.zipcode')
	    		->addSelect(\DB::raw("IF (CM.last_name != '',CONCAT(CM.first_name,' ',CM.last_name),CM.first_name) As customer_name"))
                ->addSelect('CM.address1','CM.address2','CM.email','CM.company_id')
	    		->addSelect(\DB::raw("DATE_FORMAT(redeem_product_order_detail.created_date,'%Y-%m-%d') as date_create"))
	    		->join('redeem_product_order as OM', 'redeem_product_order_detail.order_id','=','OM.order_id')
	    		->join('customer_master as CM','OM.customer_id','=','CM.customer_id')
	    		->join('redeem_product_master as PM','redeem_product_order_detail.product_id','=','PM.product_id')
	    		->leftJoin('location_master as LM','CM.city','=','LM.location_id')
	    		->leftJoin('location_master as LMS','CM.state','=','LMS.state_id')
	    		->where('redeem_product_order_detail.order_id',$order['order_id'])->groupBy('redeem_product_order_detail.id')->get();

                foreach($data as $key => $product){
                        $data[$key]['image_url'] = asset(URL_HTTP_IMAGES_REDEEM_PRODUCT).'/'.$product->product_id."/".$product->product_image;
                    }	
        return $data;       
    }

    /**
    * Function Name : ProductOrderInvoice
    * @return
    * @author Sachin Patel
    * @date 23 April, 2019
    */
    public static function ProductOrderInvoice($request){
        $order = RedeemProductOrder::where('order_id',$request->clsredeemproduct_order_id)->first()->toArray();
        $orderDetail = self::GetProductOrderInvoiceDetail($order);
        $company_detail = array();
        $ORDER_DATE     = (isset($order['created_date'])?date("d-m-y H:i",strtotime($order['created_date'])):'');

        $CUSTOMER_NAME  = (isset($orderDetail[0]->customer_name)?$orderDetail[0]->customer_name:''); 
        $COMPANY_ID     = (isset($orderDetail[0]->company_id)?$orderDetail[0]->company_id:0); 
        $CUST_ADDRESS = "";
        $comma    = "";
        
        if (!empty($orderDetail[0]->address1)) {
            $CUST_ADDRESS .= HTMLVarConv($orderDetail[0]->address1)." ";
            $comma  = ", ";
        }
        if (!empty($orderDetail[0]->address2)) {
            $CUST_ADDRESS .= HTMLVarConv($orderDetail[0]->address2)." ";
            $comma  = "<br />";
        }
        if (!empty($orderDetail[0]->city_name)) {
            $CUST_ADDRESS .= HTMLVarConv($orderDetail[0]->city_name)." ";
            $comma  = ", ";
        }
        if (!empty($orderDetail[0]->state_name)) {
            $CUST_ADDRESS .= HTMLVarConv($orderDetail[0]->state_name)." ";
            $comma  = ", ";
        }
        if (!empty($orderDetail[0]->country_name)) {
            $CUST_ADDRESS .= HTMLVarConv($orderDetail[0]->country_name)." ";
            $comma  = " - ";
        }
        if (!empty($orderDetail[0]->zipcode)) {
            $CUST_ADDRESS .= HTMLVarConv($orderDetail[0]->zipcode)." ";
        }
        if(!empty($COMPANY_ID)){
            $company_detail = Company::where('company_id',$COMPANY_ID)->first();    
        }
        $FILENAME       = "collection_receipt_".getRandomNumber().".pdf";
        $companyImage   = (isset($company_detail->certificate_logo) && !empty($company_detail->certificate_logo)) ? $company_detail->certificate_logo : "";
        $pdf            = PDF::loadView('email-template.corporate.product_invoice',compact('order','ORDER_DATE','CUSTOMER_NAME','CUST_ADDRESS','orderDetail','company_detail','companyImage','COMPANY_ID'));
        $pdf->setPaper("letter","portrait");
        $pdf->save(public_path("/").PATH_COLLECTION_RECIPT_PDF.$FILENAME,true);
        $filePath   = asset('/').PATH_COLLECTION_RECIPT_PDF.$FILENAME;
        return $filePath;
    }

    /**
    * Function Name : saveProductOrder
    * @return
    * @author Sachin Patel
    * @date 23 April, 2019
    */
    public static function saveProductOrder($request){
        $customer_id = CustomerMaster::find($request->clscustomer_customer_id);
        $order      = RedeemProductOrder::create([
                        'customer_id'       => $request->clscustomer_customer_id,
                        'customer_name'     => isset($request->clsredeemproduct_customer_name) ? $request->clsredeemproduct_customer_name : $customer->first_name .' '. $customer->last_name,
                        'customer_address'  => $request->clsredeemproduct_customer_address,
                        'order_date'        => Carbon::now(),
                        'created_by'        => auth()->user()->id,
                        'created_date'      => Carbon::now(),
                    ]);
        return $order;
    }

    /**
    * Function Name : saveProductOrderDetail
    * @return
    * @author Sachin Patel
    * @date 24 April, 2019
    */
    public static function saveProductOrderDetail($orderDetail){

        $orderDetail['created_by']      = auth()->user()->id;
        $orderDetail['created_date']    = Carbon::now();
        return RedeemProductOrderDetail::create($orderDetail);
    }


    /**
    * Function Name : updateProductOrderAmount
    * @param $totalAmount, $orderId
    * @return 
    * @author Sachin Patel
    * @date 24 April, 2019
    */
    public static function updateProductOrderAmount($orderId,$totalAmount)
    {
        RedeemProductOrder::find($orderId)->update([
            'amount' => $totalAmount
        ]);
    }

    /**
    * Function Name : updateProductOrderDeliveryDate
    * @param  $orderId
    * @return 
    * @author Sachin Patel
    * @date 24 April, 2019
    */
    public static function updateProductOrderDeliveryDate($orderId)
    {
        $result = RedeemProductOrderDetail::select(\DB::raw('MAX(PM.delivery_time) as days'))
                    ->join('redeem_product_master as PM','PM.product_id', '=', 'redeem_product_order_detail.product_id')
                    ->where('redeem_product_order_detail.order_id',$orderId)->first();
                   
        $deliveryDate = ((!empty($result) && $result->days !="") ?  date("Y-m-d",strtotime("+".$result->days." day")) : date("Y-m-d") );
        
        RedeemProductOrder::find($orderId)->update([
            'delivery_date' => $deliveryDate
        ]);
    }

    /**
    * Function Name : SendNotificationtoCustomer
    * @param  $customer_id, $message
    * @return 
    * @author Sachin Patel
    * @date 24 April, 2019
    */
    public static function SendNotificationtoCustomer($customer_id,$message){
        if(!empty($customer_id) && !empty($message)) {
            $registrationId = CustomerLoginDetail::select('customer_login_detail.registration_id','customer_login_detail.mobile', 'customer_master.customer_id', 'customer_contact_details.mobile')
                ->addSelect(\DB::raw("IF (customer_master.last_name != '',CONCAT(customer_master.first_name,' ',customer_master.last_name),customer_master.first_name) As customer_name"))
                ->join('customer_contact_details','customer_login_detail.mobile', '=','customer_contact_details.mobile')
                ->join('customer_master','customer_master.customer_id', '=', 'customer_contact_details.customer_id')
                ->where('customer_master.customer_id',$customer_id)
                ->get();

            foreach($registrationId as $key => $device){
                if($device->registration_id !=""){
                    $row['message'] = $message;
                    $data   = $row;
                    //self::send_push_notification($registrationId,$data);
                }
            }

        }
    }

    /**
    * Function Name : searchproduct
    * Dashboard Widget
    * @param  
    * @return 
    * @author Sachin Patel
    * @date 02 May, 2019
    */
    public static function searchproduct(){
        $query = RedeemProductOrder::select('redeem_product_order.*')
                ->addSelect(
                    \DB::raw('(
                        CASE 
                        WHEN redeem_product_order.status = 0 THEN   "Pending"
                        WHEN redeem_product_order.status = 1 THEN   "Approve"
                        WHEN redeem_product_order.status = 2 THEN   "Reject"
                        END) as status_name')
                )
                ->addSelect(\DB::raw('(
                    CASE 
                    WHEN redeem_product_order.rejected_by = 0 THEN   "Customer"
                    WHEN redeem_product_order.rejected_by = 1 THEN   "Admin"
                 END) as rejected_by_name'))
                ->addSelect(\DB::raw("IF (CM.last_name != '',CONCAT(CM.first_name,' ',CM.last_name),CM.first_name) As customer_name"))
                ->addSelect(\DB::raw("DATE_FORMAT(redeem_product_order.created_date,'%Y-%m-%d') as date_create"))
                ->join('customer_master as CM','CM.customer_id','=','redeem_product_order.customer_id');
        
        $withOrderDetail = $query->orderBy('order_id','DESC')->get();

        if($withOrderDetail){
            foreach($withOrderDetail as $key => $order){
                    $withOrderDetail[$key]['order_details'] = self::GetProductOrderInvoiceDetail($order);
            }
        }
        return $withOrderDetail;
    }

    /**
    * Function Name : listredeemproductorder
    * @param  
    * @return 
    * @author Sachin Patel
    * @date 02 May, 2019
    */
    public static function listredeemproductorder($request,$withOrderDetail){
        $sortBy         = ($request->has('sortBy')              && !empty($request->input('sortBy')))    ? $request->input('sortBy')    : "order_id";
        $sortOrder      = ($request->has('sortOrder')           && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
        $recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
        $pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';

        $query = RedeemProductOrder::select('redeem_product_order.*')
                ->addSelect(\DB::raw('(
                    CASE 
                    WHEN redeem_product_order.status = 0 THEN   "Pending"
                    WHEN redeem_product_order.status = 1 THEN   "Approve"
                    WHEN redeem_product_order.status = 2 THEN   "Reject"
                 END) as status_name'))
                ->addSelect(\DB::raw('(
                    CASE 
                    WHEN redeem_product_order.rejected_by = 0 THEN   "Customer"
                    WHEN redeem_product_order.rejected_by = 1 THEN   "Admin"
                 END) as rejected_by_name'))
                ->addSelect(\DB::raw("IF (CM.last_name != '',CONCAT(CM.first_name,' ',CM.last_name),CM.first_name) As customer_name"))
                ->addSelect(\DB::raw("DATE_FORMAT(redeem_product_order.created_date,'%Y-%m-%d') as date_create"))
                ->join('customer_master as CM','CM.customer_id','=','redeem_product_order.customer_id');
        
        
        if($request->has('params.created_from') && $request->has('params.created_to') && $request->input('params.created_from') !="" && $request->input('params.created_to') !=""){
            $from_date  = date('Y-m-d',strtotime($request->input('params.created_from'))).' 00:00:00';
            $to_date    = date('Y-m-d',strtotime($request->input('params.created_to'))).' 23:59:59';
            $query->whereBetween('redeem_product_order.order_date',[$from_date,$to_date]);
        }

        if($request->has('params.order_id') && $request->input('params.order_id') !=""){
            $query->where('redeem_product_order.order_id',$request->input('params.order_id'));
        }

        if($request->has('params.status') && $request->input('params.status') !=""){
            $query->where('redeem_product_order.status',$request->input('params.status'));
        }

        if($request->has('params.customer_name') && $request->input('params.customer_name') !=""){
            $query->where('CM.first_name','LIKE','%'.$request->input('params.customer_name').'%');
            $query->orWhere('CM.last_name','LIKE','%'.$request->input('params.customer_name').'%');
        }
        
        $result = $query->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber)->toArray();
        

        if($withOrderDetail){
            foreach($result['result'] as $key => $order){
                    $result['result'][$key]['order_details'] = self::GetProductOrderInvoiceDetail($order);
            }
        }
        return $result;
    }
    
}
