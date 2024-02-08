<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use App\Models\WmProductProcess;
use App\Models\BailMaster;
use App\Models\WmSalesMaster;
use App\Models\Parameter;
use App\Models\ProductInwardLadger;
use App\Models\OutWardLadger;
use App\Models\StockLadger;
use App\Models\WmProcessedProductMaster;
use App\Models\WmProductionReportMaster;
use App\Models\WmDispatchPlan;
use App\Models\WmDispatchPlanProduct;
use App\Models\NetSuitMasterDataProcessMaster;
class WmProductMaster extends Model
{
    protected 	$table 		=	'wm_product_master';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public 		$timestamps = 	true;
    protected $casts = [
        'bailing' => 'int',
        // "product_tagging_id" => "integer"
    ];
    public function salesProduct()
    {
        return $this->hasMany(WmSalesToPurchaseSequence::class,'sales_product_id');
    }
    public function SalesToPurchaseMap()
    {
        return $this->hasMany(WmSalesToPurchaseSequence::class,'sales_product_id');
    }
    public function Bailable()
    {
        return $this->hasMany(BailMaster::class,'sales_product_id');
    }

    /*
	Use 	: Get Top parent product list
	Author 	: Axay Shah
	Date 	: 30 May,2019
	*/
	public static function getTopParentProduct($product_id) {
    	$data 	= 	self::select(\DB::raw("id as product_id"),
    				\DB::raw("sub_parent_id"),
    				\DB::raw("parent_id"),
    				\DB::raw("IF(parent_id!=0,parent_id,IF(sub_parent_id!=0,sub_parent_id,IF(sub_category!=0,sub_category,id))) AS top_parent_product"),
    				\DB::raw("title AS product_name"))
    				->where('id',$product_id)
    				->first();
		return $data;
	}


	public static function GetProductwiseSegregation($productId, $sub_category, $sub_parent_id, $parent_id, $master_dept_id=0) {
		if (!defined("DISPLAY_HIDDEN_BATCH") || DISPLAY_HIDDEN_BATCH == 0) {
			$Condition = " AND bm.display_batch = 1 ";
		}
		$res 	= 	\DB::select("SELECT sg.*,
					CASE WHEN 1 = 1 THEN(
						SELECT sum(qty-resegregate_qty) FROM wm_batch_segregation_detail WHERE id = sg.id
					) END as tmp_stock
					FROM wm_batch_segregation_detail sg
					LEFT JOIN wm_batch_master bm ON sg.batch_id = bm.batch_id
	   				LEFT JOIN wm_product_master pm ON sg.product_id = pm.id
					WHERE sg.product_id IN ($productId)
					AND sg.dispatch_stock = '0' AND sg.created_date >= '".EXCLUDE_STOCK_DATE."'
	   				AND bm.master_dept_id = '".$master_dept_id."'
	   				$BatchDisplayCond
	   				having tmp_stock > 0)

					UNION ALL

					(SELECT sg.*,
						CASE WHEN 1 = 1 THEN(
							SELECT sum(qty-resegregate_qty) FROM wm_batch_segregation_detail WHERE id = sg.id
						) END as tmp_stock
					FROM wm_batch_segregation_detail sg
					LEFT JOIN wm_batch_master bm ON sg.batch_id = bm.batch_id
	   				LEFT JOIN wm_product_master pm ON sg.product_id = pm.id
					WHERE sg.product_id IN ($sub_category)
					AND sg.dispatch_stock = '0' AND sg.created_date >= '".EXCLUDE_STOCK_DATE."'
	   				AND bm.master_dept_id = '".$master_dept_id."'
	   				$BatchDisplayCond
	   				having tmp_stock > 0)

					UNION ALL

					(SELECT sg.*,
						CASE WHEN 1 = 1 THEN(
							SELECT sum(qty-resegregate_qty) FROM wm_batch_segregation_detail WHERE id = sg.id
						) END as tmp_stock
					FROM wm_batch_segregation_detail sg
					LEFT JOIN wm_batch_master bm ON sg.batch_id = bm.batch_id
	   				LEFT JOIN wm_product_master pm ON sg.product_id = pm.id
					WHERE sg.product_id IN ($sub_parent_id)
					AND sg.dispatch_stock = '0' AND sg.created_date >= '".EXCLUDE_STOCK_DATE."'
	   				AND bm.master_dept_id = '".$master_dept_id."'
	   				$BatchDisplayCond
	   				having tmp_stock > 0)

					UNION ALL

					(SELECT sg.*,
						CASE WHEN 1 = 1 THEN(
							SELECT sum(qty-resegregate_qty) FROM wm_batch_segregation_detail WHERE id = sg.id
						) END as tmp_stock
					FROM wm_batch_segregation_detail sg
					LEFT JOIN wm_batch_master bm ON sg.batch_id = bm.batch_id
	   				LEFT JOIN wm_product_master pm ON sg.product_id = pm.id
					WHERE sg.product_id IN ($parent_id)
					AND sg.dispatch_stock = '0' AND sg.created_date >= '".EXCLUDE_STOCK_DATE."'
	   				AND bm.master_dept_id = '".$master_dept_id."'
	   				$BatchDisplayCond
	   				having tmp_stock > 0)");
				return $res;
	}

	/*
	Use 	: Get Product Drop Down List
	Author 	: Axay Shah
	Date 	: 30 May,2019
	*/
	public static function productDropDown($request) {

		$ReadyForDispatchTbl 	= new WmReadyForDispatchMaster();
		$self 					= (new static)->getTable();
		$result 	= array();
		$productId 	= (isset($request->product_id) && !empty($request->product_id)) ? $request->product_id : 0;
		$isFoc 		= (isset($request->is_foc) && !empty($request->is_foc)) ? $request->is_foc : 0;
		$bailing	= (isset($request->bailing) && !empty($request->bailing)) ? $request->bailing : 0;
		$salesFlag	= (isset($request->sales_flag_on) && !empty($request->sales_flag_on)) ? $request->sales_flag_on : 0;
		$ReadyForDispatch = (isset($request->ready_for_dispatch) && !empty($request->ready_for_dispatch)) ? $request->ready_for_dispatch : 0;
		$MRF_ID 	= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		$data 		= 	self::with(['Bailable' => function ($q){
    					return $q->select("id",'sales_product_id',"product_dec","qty","company_id");
    				}])->select(\DB::raw("title"),
					\DB::raw("$self.id"),
					\DB::raw("$self.description"),
					\DB::raw("$self.bailing"));

					if($ReadyForDispatch == 1){
						$data->join($ReadyForDispatchTbl->getTable()." as RD","$self.id","=","RD.sales_product_id");
						$data->where("RD.mrf_id",$MRF_ID);
					}
					if($isFoc == 1){
						$data->where("$self.id",SALES_FOC_PRODUCT);
					}
					if($bailing == 1){
						$data->where("$self.bailing",$bailing);
					}
					$data->where("$self.status",1);
					$data->where("$self.company_id",Auth()->user()->company_id);
					$data->where("$self.sales",1);
					// LiveServices::toSqlWithBinding($data);
					$result = $data->orderBy("title","ASC")->get()->toArray();
					if(!empty($result)){
						foreach($result as $key => $value){
							$result[$key]['avg_rate'] =  0;
						}
					}
				return $result;
	}

	/*
	Use 	: Retrieve Collection Products
	Author 	: Axay Shah
	Date 	: 12 June,2019
	*/
	public static function retrieveCollectionProducts($id){
		$DispatchProduct 	= self::select(
				\DB::raw("id as product_id"),
				\DB::raw("title as product_name"),
				\DB::raw("hsn_code as HSN"),
				\DB::raw("'2.5' as CGST"),
				\DB::raw("'2.5' as SGST"),
				\DB::raw("'5.0' as IGST"),
				\DB::raw("'5.0' as IGST"),
				\DB::raw("'KGS' as UOM"),
				\DB::raw("'0' as audit_qty"),
				\DB::raw("'0' as inert"),
				\DB::raw("quantity as net_weight"),
				\DB::raw("DP.quantity"),
				\DB::raw("DP.price")
			)
		->leftjoin("wm_dispatch_product AS DP","wm_product_master.id","=","DP.product_id")
		->where("DP.dispatch_id",$id)
		->get();
		return $DispatchProduct;
	}

	/*
	Use 	: List Sales Product
	Author 	: Axay Shah
	Date 	: 09 Aug,2019
	*/
	public static function ListProduct($request){
		$Product 		= (new static)->getTable();
		$Parameter 		= new Parameter();
		$WmPurchaseToSalesMap 	= new WmPurchaseToSalesMap();
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$cityId         = GetBaseLocationCity();
		$MapsalesProductID 		= array();
		if($request->has('params.purchase_product_id') && !empty($request->input('params.purchase_product_id')))
        {
            $purchase_product_id = $request->input('params.purchase_product_id');
            $MapsalesProductID 	 = WmSalesToPurchaseSequence::where("purchase_product_id",$purchase_product_id)->pluck("sales_product_id")->toArray();
        }
		$data = self::select("$Product.*",
				\DB::raw("IF($Product.recyclable = 1,'R','NR') as recyclable_label"),
				\DB::raw("U1.title as parent_title"),
				\DB::raw("P.para_value as tagging_id_name")

				)->with(["SalesToPurchaseMap" =>function($q){
					$q->select("wm_sales_to_purchase_sequence.*",\DB::raw("CONCAT(company_product_master.name,'-',company_product_quality_parameter.parameter_name) as name"),\DB::raw("company_product_master.net_suit_code as purchase_net_suit_code")
					);
					$q->join("company_product_master","company_product_master.id","=","wm_sales_to_purchase_sequence.purchase_product_id");
					$q->join("company_product_quality_parameter","company_product_master.id","=","company_product_quality_parameter.product_id");
				}])
		->leftjoin($Product." AS U1","$Product.parent_id","=","U1.id")
		->leftjoin($Parameter->getTable()." as P","$Product.product_tagging_id","=","P.para_id");
		if(!empty($MapsalesProductID)){
			$data->whereIn("$Product.id",$MapsalesProductID);
		}
		if($request->has('params.hsn_code') && !empty($request->input('params.hsn_code')))
		{
			$data->where("$Product.hsn_code",$request->input('params.hsn_code'));
		}
		if($request->has('params.group_name') && !empty($request->input('params.group_name')))
		{
			$data->where("U1.title",$request->input('params.group_name'));
		}
		if($request->has('params.id') && !empty($request->input('params.id')))
		{
			$id = $request->input('params.id');
			if(!is_array($request->input('params.id'))){
				$id = explode(",",$request->input("params.id"));
			}
			$data->whereIn("$Product.id",$id);
		}
		if($request->has('params.recyclable'))
		{
			if($request->input('params.recyclable') == "0" || !empty($request->input('params.recyclable'))){
				$data->where("$Product.recyclable",$request->input('params.recyclable'));
			}
			
		}elseif($request->has('params.status') && $request->input('params.status') == "0"){
			$data->where("$Product.status",$request->input('params.status'));
		}
		if($request->has('params.product_tagging_id') && !empty($request->input('params.product_tagging_id')))
        {
            $productTagging = $request->input('params.product_tagging_id');
            $data->whereIn("$Product.product_tagging_id",$productTagging);
        }
        if($request->has('params.net_suit_code') && !empty($request->input('params.net_suit_code')))
		{
			$data->where("$Product.net_suit_code","like","%".$request->input('params.net_suit_code')."%");
		}
		if($request->has('params.sales') && !empty($request->input('params.sales')))
		{
			$data->where("$Product.sales",$request->input('params.sales'));
		}elseif($request->has('params.sales') && $request->input('params.sales') == "0"){
			$data->where("$Product.sales",$request->input('params.sales'));
		}

		if($request->has('params.title') && !empty($request->input('params.title')))
		{
			$data->where("$Product.title","like","%".$request->input('params.title')."%");
		}
		if($request->has('params.sub_parent_title') && !empty($request->input('params.sub_parent_title')))
		{
			$data->where("$Product.sub_parent_title","like","%".$request->input('params.sub_parent_title')."%");
		}
		if($request->has('params.sub_category_title') && !empty($request->input('params.sub_category_title')))
		{
			$data->where("$Product.sub_category_title","like","%".$request->input('params.sub_category_title')."%");
		}
		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
		{
			$data->whereBetween("$Product.created_at",array(date("Y-m-d H:i:s", strtotime($request->input('params.startDate')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('params.endDate')." ".GLOBAL_END_TIME))));
		}else if(!empty($request->input('params.startDate'))){
		   $datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
		   $data->whereBetween("$Product.created_at",array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
		}else if(!empty($request->input('params.startDate'))){
		   $data->whereBetween("$Product.created_at",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
		}
		if($request->has('params.status') && !empty($request->input('params.status')))
		{
			$data->where("$Product.status",$request->input('params.status'));
		}elseif($request->has('params.status') && $request->input('params.status') == "0"){
			$data->where("$Product.status",$request->input('params.status'));
		}
		$data->where("$Product.company_id",Auth()->user()->company_id);
		if($request->has('ex') && !empty($request->input('ex')) && $request->input('ex') == EXPORT_ALL)
		{
			$data->where("$Product.status","1");
		   	$recordPerPage 	= $data->get();
		   	$recordPerPage 	= count($recordPerPage);
		   	$result    		= $data->paginate($recordPerPage);
		}else{
			$data->orderBy($sortBy, $sortOrder);
			$result    = $data->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		}
		$toArray 	= $result->toArray();
		if(!empty($result)){
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0){
				foreach($toArray['result'] as $key => $value){
					if(!empty($value["product_tagging_id"])){
						$para_id 		= explode(",",$value["product_tagging_id"]);

						$TaggingName 	= Parameter::select(\DB::raw("group_concat(para_value) as tagging_id_name"))->whereIn("para_id",$para_id)
							->get();
						$taging_name = $TaggingName[0]['tagging_id_name'];
						$toArray['result'][$key]['tagging_id_name'] = $taging_name;
					}
				}
				$toArray['export_url'] = url('/')."/".PURCHASE_TO_SALES_EXPORT;
			}
		}


		return $toArray;
	}
	/*
	Use 	: Create sales product
	Author 	: Axay Shah
	Date 	: 09 Aug,2019
	*/
	public static function InsertSalesProduct($request)
	{
		try
		{
			$product 						= new self();
			$lumping 						= (isset($request['lumping']) 		&& !empty($request['lumping'])) ?  $request['lumping'] : 0;
			$grinding 						= (isset($request['grinding']) 		&& !empty($request['grinding'])) ?  $request['grinding'] : 0;
			$granulating 					= (isset($request['granulating']) 	&& !empty($request['granulating'])) ?  $request['granulating'] : 0;
			$bailing  						= (isset($request['bailing']) 		&& !empty($request['bailing'])) ?  $request['bailing'] : 0;
			$washing  						= (isset($request['washing']) 		&& !empty($request['washing'])) ?  $request['washing'] : 0;
			$product->parent_id 			= (isset($request['parent_id']) 	&& !empty($request['parent_id'])) ?  $request['parent_id'] : 0;
			$product->sub_parent_id			= (isset($request['sub_parent_id']) && !empty($request['sub_parent_id'])) ?  $request['sub_parent_id'] : 0;
			$product->sub_category 			= (isset($request['sub_category']) 	&& !empty($request['sub_category'])) ?  $request['sub_category'] : 0;
			$product->title 				= (isset($request['title']) 		&& !empty($request['title'])) ?  $request['title'] : "";
			$product->description 			= (isset($request['description']) 	&& !empty($request['description'])) ?  $request['description'] : "";
			$product->hsn_code 				= (isset($request['hsn_code']) 		&& !empty($request['hsn_code'])) ?  $request['hsn_code'] : "";
			$product->quality 				= (isset($request['quality']) 		&& !empty($request['quality'])) ?  $request['quality'] : 0;
			$product->sorting 				= (isset($request['sorting']) 		&& !empty($request['sorting'])) ?  $request['sorting'] : 0;
			$product->quality_control 		= (isset($request['quality_control']) && !empty($request['quality_control'])) ?  $request['quality_control'] : 0;
			$product->sales 				= (isset($request['sales']) 		&& !empty($request['sales'])) ?  $request['sales'] : 0;
			$product->status 				= (isset($request['status']) 		&& !empty($request['status'])) ?  $request['status'] : 0;
			$product->master_dept_id		= (isset($request['master_dept_id']) && !empty($request['master_dept_id'])) ?  $request['master_dept_id'] : 0;
			$product->product_tagging_id	= (isset($request['product_tagging_id']) && !empty($request['product_tagging_id'])) ?  $request['product_tagging_id'] : 0;
			$product->net_suit_code 		= (isset($request['net_suit_code']) && !empty($request['net_suit_code'])) ?  $request['net_suit_code'] : "";
			$product->net_suit_class		= (isset($request['net_suit_class']) && !empty($request['net_suit_class'])) ?  $request['net_suit_class'] : 0;
			$product->net_suit_department	= (isset($request['net_suit_department']) && !empty($request['net_suit_department'])) ?  $request['net_suit_department'] : 0;
			$product->cgst					= (isset($request['cgst']) && !empty($request['cgst'])) ?  $request['cgst'] : 0;
			$product->sgst					= (isset($request['sgst']) && !empty($request['sgst'])) ?  $request['sgst'] : 0;
			$product->igst					= (isset($request['igst']) && !empty($request['igst'])) ?  $request['igst'] : 0;
			$product->recyclable			= (isset($request['recyclable']) && !empty($request['recyclable'])) ?  $request['recyclable'] : 0;
			$product->epr_enabled			= (isset($request['epr_enabled']) && !empty($request['epr_enabled'])) ?  $request['epr_enabled'] : 0;
			$product->is_afr				= (isset($request['is_afr']) && !empty($request['is_afr'])) ?  $request['is_afr'] : 0;
			$product->is_rdf				= (isset($request['is_rdf']) && !empty($request['is_rdf'])) ?  $request['is_rdf'] : 0;
			$product->is_inert				= (isset($request['is_inert']) && !empty($request['is_inert'])) ?  $request['is_inert'] : 0;
			$product->epr_product_type		= (isset($request['epr_product_type']) && !empty($request['epr_product_type'])) ?  $request['epr_product_type'] : 0;
			$product->ccof_category_id 		= (isset($request['ccof_category_id']) && !empty($request['ccof_category_id'])) ? $request['ccof_category_id']  : 0 ;
            $product->ccof_sub_category_id 	= (isset($request['ccof_sub_category_id']) && !empty($request['ccof_sub_category_id'])) ? $request['ccof_sub_category_id']  : 0 ;
			$product->washing 				= $washing;
			$product->grinding 				= $grinding;
			$product->granulating 			= $granulating;
			$product->bailing  				= $bailing;
			$product->lumping 				= $lumping;
			$product->company_id 			= Auth()->user()->company_id;
			$product->created_by 			= Auth()->user()->adminuserid;
			$product->updated_by 			= Auth()->user()->adminuserid;
			if($product->save())
			{
				$productId = $product->id;
				####### NET SUIT MASTER ##########
				$tableName =  (new static)->getTable();
				NetSuitMasterDataProcessMaster::NetSuitStoreMasterData($tableName,$productId,Auth()->user()->company_id);
				####### NET SUIT MASTER ##########
				WmProductProcess::where("product_id",$productId)->delete();
				if(!empty($grinding)){
					WmProductProcess::AddProductProcess($productId,PROCESS_TYPE_GRINDING);
				}
				if(!empty($washing)){
					WmProductProcess::AddProductProcess($productId,PROCESS_TYPE_WASHING);
				}
				if(!empty($granulating)){
					WmProductProcess::AddProductProcess($productId,PROCESS_TYPE_GRANULATING);
				}
				if(!empty($bailing)){
					WmProductProcess::AddProductProcess($productId,PROCESS_TYPE_BAILING);
				}
				if(!empty($lumping)){
					WmProductProcess::AddProductProcess($productId,PROCESS_TYPE_LUMPING);
				}
				$requestObj = json_encode($request,JSON_FORCE_OBJECT);
				LR_Modules_Log_CompanyUserActionLog($requestObj,$productId);
			}
			return $product;
		} catch(\Exception $e) {
			dd($e);
		}
	}
	
	/*
	Use 	: Update Sales Product
	Author 	: Axay Shah
	Date 	: 09 Aug,2019
	*/
	public static function UpdateSalesProduct($request)
	{
		$product = self::find($request['id']);
		if($product)
		{
			$lumping 						= (isset($request['lumping']) 		&& !empty($request['lumping'])) ?  $request['lumping'] : 0;
			$grinding 						= (isset($request['grinding']) 		&& !empty($request['grinding'])) ?  $request['grinding'] : 0;
			$granulating 					= (isset($request['granulating']) 	&& !empty($request['granulating'])) ?  $request['granulating'] : 0;
			$bailing  						= (isset($request['bailing']) 		&& !empty($request['bailing'])) ?  $request['bailing'] : 0;
			$washing  						= (isset($request['washing']) 		&& !empty($request['washing'])) ?  $request['washing'] : 0;
			$product->parent_id 			= (isset($request['parent_id']) 	&& !empty($request['parent_id'])) ?  $request['parent_id'] : 0;
			$product->sub_parent_id			= (isset($request['sub_parent_id']) && !empty($request['sub_parent_id'])) ?  $request['sub_parent_id'] : 0;
			$product->sub_category 			= (isset($request['sub_category']) 	&& !empty($request['sub_category'])) ?  $request['sub_category'] : 0;
			$product->title 				= (isset($request['title']) 		&& !empty($request['title'])) ?  $request['title'] : "";
			$product->description 			= (isset($request['description']) 	&& !empty($request['description'])) ?  $request['description'] : "";
			$product->hsn_code 				= (isset($request['hsn_code']) 		&& !empty($request['hsn_code'])) ?  $request['hsn_code'] : "";
			$product->quality 				= (isset($request['quality']) 		&& !empty($request['quality'])) ?  $request['quality'] : 0;
			$product->sorting 				= (isset($request['sorting']) 		&& !empty($request['sorting'])) ?  $request['sorting'] : 0;
			$product->sgst 					= (isset($request['sgst']) 			&& !empty($request['sgst'])) ?  $request['sgst'] : 0;
			$product->cgst 					= (isset($request['cgst']) 			&& !empty($request['cgst'])) ?  $request['cgst'] : 0;
			$product->igst 					= (isset($request['igst']) 			&& !empty($request['igst'])) ?  $request['igst'] : 0;
			$product->quality_control 		= (isset($request['quality_control']) && !empty($request['quality_control'])) ?  $request['quality_control'] : 0;
			$product->product_tagging_id	= (isset($request['product_tagging_id']) && !empty($request['product_tagging_id'])) ?  $request['product_tagging_id'] : 0;
			$product->net_suit_code 		= (isset($request['net_suit_code']) && !empty($request['net_suit_code'])) ?  $request['net_suit_code'] : "";
			$product->net_suit_class		= (isset($request['net_suit_class']) && !empty($request['net_suit_class'])) ?  $request['net_suit_class'] : 0;
			$product->net_suit_department	= (isset($request['net_suit_department']) && !empty($request['net_suit_department'])) ?  $request['net_suit_department'] : 0;
			$product->cgst					= (isset($request['cgst']) && !empty($request['cgst'])) ?  $request['cgst'] : 0;
			$product->sgst					= (isset($request['sgst']) && !empty($request['sgst'])) ?  $request['sgst'] : 0;
			$product->igst					= (isset($request['igst']) && !empty($request['igst'])) ?  $request['igst'] : 0;
			$product->recyclable			= (isset($request['recyclable']) && !empty($request['recyclable'])) ?  $request['recyclable'] : 0;
			$product->epr_enabled			= (isset($request['epr_enabled']) && !empty($request['epr_enabled'])) ?  $request['epr_enabled'] : 0;
			$product->epr_product_type		= (isset($request['epr_product_type']) && !empty($request['epr_product_type'])) ?  $request['epr_product_type'] : 0;
			$product->ccof_category_id 		= (isset($request['ccof_category_id']) && !empty($request['ccof_category_id'])) ? $request['ccof_category_id']  : 0 ;
            $product->ccof_sub_category_id 	= (isset($request['ccof_sub_category_id']) && !empty($request['ccof_sub_category_id'])) ? $request['ccof_sub_category_id']  : 0 ;
            $product->is_afr				= (isset($request['is_afr']) && !empty($request['is_afr'])) ?  $request['is_afr'] : 0;
			$product->is_rdf				= (isset($request['is_rdf']) && !empty($request['is_rdf'])) ?  $request['is_rdf'] : 0;
			$product->is_inert				= (isset($request['is_inert']) && !empty($request['is_inert'])) ?  $request['is_inert'] : 0;
			$product->washing 				= $washing;
			$product->grinding 				= $grinding;
			$product->granulating 			= $granulating;
			$product->bailing  				= $bailing;
			$product->lumping 				= $lumping;
			$product->sales 				= (isset($request['sales']) 		&& !empty($request['sales'])) ?  $request['sales'] : 0;
			$product->status 				= (isset($request['status']) 		&& !empty($request['status'])) ?  $request['status'] : 0;
			$product->master_dept_id		= (isset($request['master_dept_id']) && !empty($request['master_dept_id'])) ?  $request['master_dept_id'] : 0;
			$product->company_id 			= Auth()->user()->company_id;
			$product->updated_by 			= Auth()->user()->adminuserid;
			if($product->save())
			{
				$productId = $product->id;
				####### NET SUIT MASTER ##########
				$tableName =  (new static)->getTable();
				NetSuitMasterDataProcessMaster::NetSuitStoreMasterData($tableName,$productId,Auth()->user()->company_id);
				####### NET SUIT MASTER ##########
				WmProductProcess::where("product_id",$productId)->delete();
				if(!empty($grinding)){
					WmProductProcess::AddProductProcess($productId,PROCESS_TYPE_GRINDING);
				}
				if(!empty($washing)){
					WmProductProcess::AddProductProcess($productId,PROCESS_TYPE_WASHING);
				}
				if(!empty($granulating)){
					WmProductProcess::AddProductProcess($productId,PROCESS_TYPE_GRANULATING);
				}
				if(!empty($bailing)){
					WmProductProcess::AddProductProcess($productId,PROCESS_TYPE_BAILING);
				}
				if(!empty($lumping)){
					WmProductProcess::AddProductProcess($productId,PROCESS_TYPE_LUMPING);
				}
				$requestObj = json_encode($request,JSON_FORCE_OBJECT);
				LR_Modules_Log_CompanyUserActionLog($requestObj,$productId);
			}
			return $product;
		}
	}

	/*
	Use 	: Get Product Group
	Author 	: Axay Shah
	Date 	: 12 Aug,2019
	*/
	public static function GetProductGroup($product_group = "", $parent_id = "", $sub_parent_id = "", $sub_category= ""){
		$self 	= (new static)->getTable();
		$res 	= array();
		$data 	= self::select("$self.*",
			\DB::raw("u2.title as parent_title"),
			\DB::raw("u3.title as sub_parent_title"),
			\DB::raw("u4.title as sub_category_title")
		)
		->leftjoin($self." as u2","u2.id","=",$self.".parent_id")
		->leftjoin($self." as u3","u3.id","=",$self.".sub_parent_id")
		->leftjoin($self." as u4","u4.id","=",$self.".sub_category");
		if (!empty($product_group)) {
			$data->where("$self.id",$product_group);
		}
		if (!empty($parent_id)) {
			$data->where("$self.id",$parent_id);
		}
		if (!empty($sub_parent_id)) {
			$data->where("$self.id",$sub_parent_id);
		}
		if (!empty($sub_category)) {
			$data->where("$self.id",$sub_category);
		}
		// LiveServices::toSqlWithBinding($data);
		$data->where("$self.company_id",Auth()->user()->company_id);
		$res = $data->get();
		return $res;
	}

	/*
	Use 	: Get Product Group
	Author 	: Axay Shah
	Date 	: 12 Aug,2019
	*/
	public static function GetProductMasterDropDown($request){
		$DATA 		= array();
		$key 		= (isset($request->key) && !empty($request->key)) ? $request->key : "";
		$groupId 	= (isset($request->group_id) && !empty($request->group_id)) ? $request->group_id : 0;
		$parent_id 	= (isset($request->parent_id) && !empty($request->parent_id)) ? $request->parent_id : 0;
		switch($key)
		{
			case "group":
			{
				$DATA = self::GetProductGroup('',$parent_id,'0','0');
				return $DATA;
				break;
			}
			case "parent":
			{
				$DATA = self::GetProductGroup('', $groupId,'0','0');
				return $DATA;
				break;
			}

			case "sub_parent":
			{
				$DATA = self::GetProductGroup('',$groupId,$parent_id,'0');
				return $DATA;
				break;
			}
			default :
				return $DATA;
				break;
		}
		return $DATA;
	}

	/*
	Use 	: Get By Id
	Author 	: Axay Shah
	Date 	: 12 Aug,2019
	*/
	public static function GetById($id){
		$data = self::find($id);
		if($data){
			if(!empty($data->product_tagging_id)){
				$data->product_tagging_id = array_map('intval', explode(",",$data->product_tagging_id)); ;
			}
		}
		return $data;
	}

	/*
	Use 	: Calculate Product GST
	Author 	: Axay Shah
	Date 	: 28 Feb 2020
	*/
	public static function calculateProductGST($ProductId,$Qty,$Rate,$IsFromSameState = true){
		$GST_ARR 		= array('CGST_RATE'=>0,
								'CGST_AMT'=>0,
								'SGST_RATE'=>0,
								'SGST_AMT'=>0,
								'IGST_RATE'=>0,
								'IGST_AMT'=>0,
								'TOTAL_GST_AMT'=>0,
								'TOTAL_GR_AMT'=>0,
								'TOTAL_NET_AMT'	=>0,
								"SUM_GST_PERCENT" => 0);
		(!empty($Rate)) ? $Rate : 0;
		(!empty($Qty)) ? $Qty : 0;
		$productData 		= self::GetById($ProductId);
		if($productData) {
			$SUM_GST_PERCENT = 0;
			$CGST_AMT 		= 0;
			$SGST_AMT 		= 0;
			$IGST_AMT 		= 0;
			$TOTAL_GST_AMT 	= 0;
			$CGST_RATE 		= $productData->cgst;
			$SGST_RATE 		= $productData->sgst;
			$IGST_RATE 		= $productData->igst;
			if($IsFromSameState) {
				if($Rate > 0){
					$CGST_AMT 			= ($CGST_RATE > 0) ? (($Qty* $Rate) / 100) * $CGST_RATE:0;
					$SGST_AMT 			= ($SGST_RATE > 0) ? (($Qty* $Rate) / 100) *  $SGST_RATE:0;
					$TOTAL_GST_AMT 		= $CGST_AMT + $SGST_AMT;
					$SUM_GST_PERCENT 	= $CGST_RATE + $SGST_RATE;
				}
			}else{
				if($Rate > 0){
					$IGST_AMT 			= ($IGST_RATE > 0) ? (($Qty* $Rate) / 100) * $IGST_RATE:0;
					$TOTAL_GST_AMT 		= $IGST_AMT;
					$SUM_GST_PERCENT 	= $IGST_RATE;
				}
			}


			$GST_ARR['CGST_RATE'] 		= $CGST_RATE;
			$GST_ARR['CGST_AMT']		= _FormatNumberV2($CGST_AMT);
			$GST_ARR['SGST_RATE'] 		= $SGST_RATE;
			$GST_ARR['SGST_AMT']		= _FormatNumberV2($SGST_AMT);
			$GST_ARR['IGST_RATE'] 		= $IGST_RATE;
			$GST_ARR['IGST_AMT']		= _FormatNumberV2($IGST_AMT);
			$GST_ARR['TOTAL_GST_AMT']	= _FormatNumberV2($TOTAL_GST_AMT);
			$GST_ARR['TOTAL_GR_AMT']	= ($Rate > 0) ? _FormatNumberV2(($Rate * $Qty)) : 0;
			$GST_ARR['TOTAL_NET_AMT']	= ($Rate > 0) ? _FormatNumberV2(($Rate * $Qty) + $TOTAL_GST_AMT) : 0;
			$GST_ARR['SUM_GST_PERCENT']	= _FormatNumberV2($SUM_GST_PERCENT);
		}
		return $GST_ARR;
	}


	/*
	Use 	: Get Product Drop Down List For Shift Wise Sorting Module
	Author 	: Axay Shah
	Date 	: 02 April,2020
	*/
	public static function ShiftProductList($request) {
		$Sales 			= new WmSalesMaster();
		$self 			= (new static)->getTable();
		$DisplayInShift = (isset($request->display_in_shift) && !empty($request->display_in_shift)) ? $request->display_in_shift : 0;
		$result  = 	array();
		$data 	 = 	self::select(\DB::raw("title"),
    				\DB::raw("id"),
    				\DB::raw("hsn_code"),
    				\DB::raw("status"),
    				\DB::raw("description"),
    				\DB::raw("id as product_id"),
    				\DB::raw("display_in_shift")
    				)
    				->where("status",1)
    				->where("display_in_shift",$DisplayInShift)
    				->where("company_id",Auth()->user()->company_id);
    				$result = $data->orderBy("title","ASC")->get()->toArray();
    	return $result;
	}

	/*
	Use 	: List Sales Product in Dispatch
	Author 	: Axay Shah
	Date 	: 21 April,2020
	*/
	public static function DispatchSalesProductDropDownV1($request)
	{
		$SalesProcess 		= new WmProcessedProductMaster();
		$Production 		= new WmProductionReportMaster();
		$WmDispatchPlan 	= new WmDispatchPlan();
		$TblDispatchPlan 	= $WmDispatchPlan->getTable();
		$WmDispatchPlanProduct 	= new WmDispatchPlanProduct();
		$TblPlanProduct 	= $WmDispatchPlanProduct->getTable();
		$Today 			= date("Y-m-d");
		$Inward 		= new ProductInwardLadger();
		$Outward 		= new OutWardLadger();
		$Stock 			= new StockLadger();
		$self 			= (new static)->getTable();
		$MRF_ID 		= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id 		: 0;
		$isFoc 			= (isset($request->is_foc) && !empty($request->is_foc)) ? $request->is_foc 		: 0;
		$bailing		= (isset($request->bailing) && !empty($request->bailing)) ? $request->bailing 	: 0;
		$DispatchID		= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id 	: 0;
		$SalesOrderID	= (isset($request->sales_order_id) && !empty($request->sales_order_id)) ? $request->sales_order_id 	: 0;
		$OriginID		= (isset($request->origin_id) && !empty($request->origin_id)) ? $request->origin_id : 0;
		$DispatchDate	= (isset($request->dispatch_date) && !empty($request->dispatch_date)) ? date("Y-m-d",strtotime($request->dispatch_date)) : "";
		$ClientID		= (isset($request->client_id) && !empty($request->client_id)) ? $request->client_id : 0;
		if($DispatchID > 0){
			$MRF_ID = WmDispatch::where("id",$DispatchID)->value("bill_from_mrf_id");
		}
		$res 			= array();
		$data 			= self::with(['Bailable' => function ($q) { return $q->select("id",'sales_product_id',"product_dec","qty","company_id");}])
							->select(\DB::raw("$self.title"),
									\DB::raw("$self.id"),
									\DB::raw("$self.description"),
									\DB::raw("$self.bailing"),
				    				\DB::raw("
				    					CASE WHEN 1=1 THEN (
											SELECT opening_stock
											FROM ".$Stock->getTable()."
											WHERE product_id = $self.id
											AND product_type = ".PRODUCT_SALES."
											AND mrf_id 	 	 = '".$MRF_ID."'
											AND stock_date 	= '".$Today."'
											group by product_id
										) END AS opening_stock"),
				    				\DB::raw("
				    					CASE WHEN 1=1 THEN (
											SELECT SUM(quantity) as inward_qty
											FROM ".$Inward->getTable()."
											WHERE product_id = $self.id
											AND product_type = ".PRODUCT_SALES."
											AND mrf_id 	 	 = '".$MRF_ID."'
											AND inward_date = '".$Today."'
										) END AS inward_stock"),
				    				\DB::raw("
				    					CASE WHEN 1=1 THEN (
											SELECT SUM(quantity) as outward_qty
											FROM ".$Outward->getTable()."
											WHERE sales_product_id = $self.id
											AND mrf_id = '".$MRF_ID."'
											AND outward_date = '".$Today."'
										) END AS outward_stock"));
							$data->leftjoin("wm_product_saleable_tagging",function($q) use($self,$MRF_ID){
								$q->on("wm_product_saleable_tagging.product_id","=","$self.id");
								$q->on("wm_product_saleable_tagging.mrf_id","=", \DB::raw($MRF_ID));

							});
    	if($isFoc == 1){
			$data->whereIn("$self.id",SALES_FOC_PRODUCT_ARRAY);
		}
		if($bailing == 1){
			$data->where("$self.bailing",$bailing);
		}
		$data->where("$self.status",1);
		$data->where("$self.company_id",Auth()->user()->company_id);
		$data->where("$self.sales",1);
		$SQL_QUERY 	= LiveServices::toSqlWithBinding($data,true);
		$result 	= $data->groupby("$self.id")->orderBy("title","ASC")->get()->toArray();
		if(!empty($result)) {
			foreach($result as $key => $value){
				$client_rate 	= 0;
				$is_disable 	= 0;
				$qty 			= 0;
				if(!empty($DispatchDate)){
					$GET_RATE_DATA = self::GetSalesOrderClientRate($DispatchDate,$value['id'],$MRF_ID,$ClientID,$OriginID,$DispatchID);
					if(!empty($GET_RATE_DATA)){
							$client_rate 	= _FormatNumberV2($GET_RATE_DATA[0]->rate);
							$is_disable 	= 1;
							$qty 			= $GET_RATE_DATA[0]->qty;
					}
				}
				$result[$key]['client_rate'] 	= $client_rate;
				$result[$key]['is_disable'] 	= $is_disable;
				$result[$key]['qty'] 			= $is_disable;
				$FridgeQty 		= OutWardLadger::where("ref_id",$DispatchID)
								->where("mrf_id",$MRF_ID)
								->where("type",TYPE_DISPATCH)
								->where("sales_product_id",$value['id'])
								->sum("quantity");
				$INWARD_QTY 	= (isset($value['inward_stock']) && !empty($value['inward_stock']) ? (float)$value['inward_stock'] : 0);
				$OUTWARD_QTY 	= (isset($value['outward_stock']) && !empty($value['outward_stock']) ? (float)$value['outward_stock'] : 0);
				$OPENING_QTY 	= (isset($value['opening_stock']) && !empty($value['opening_stock']) ? (float)$value['opening_stock'] : 0);
				$CURRENT_STOCK  = ($INWARD_QTY + $OPENING_QTY) - $OUTWARD_QTY;
				$FridgeQty      = (!empty($FridgeQty)) ? $FridgeQty : 0;
				$CURRENT_STOCK  = $CURRENT_STOCK + $FridgeQty;
				########### AVG PRICE GIVEN IN PRODUCT LISTING ############
				$AVG_PRICE 		= StockLadger::where("mrf_id",$MRF_ID)->where("stock_date",date("Y-m-d"))->where("product_type",PRODUCT_SALES)->where("product_id",$value['id'])->value("avg_price");
				$result[$key]['avg_price']  	= (!empty($AVG_PRICE)) ? _FormatNumberV2($AVG_PRICE) : 0;
				########### AVG PRICE GIVEN IN PRODUCT LISTING ############
				if($SalesOrderID > 0){
					$result[$key]['current_stock'] =  ($CURRENT_STOCK > 0) ? _FormatNumberV2($CURRENT_STOCK) : 0;
						$res[] =  $result[$key];
				}else{
					if($CURRENT_STOCK > 0){
						$result[$key]['current_stock'] =  ($CURRENT_STOCK > 0) ? _FormatNumberV2($CURRENT_STOCK) : 0;
						$res[] =  $result[$key];
					}
				}
			}
		}
		return $res;
	}

	/*
	Use 	: List Sales Product in Dispatch
	Author 	: Axay Shah
	Date 	: 21 April,2020
	Modified : Kalpak Prajapati
	@since : 14 Sep 2022
	*/
	public static function DispatchSalesProductDropDown($request)
	{
		$SalesProcess 			= new WmProcessedProductMaster();
		$Production 			= new WmProductionReportMaster();
		$WmDispatchPlan 		= new WmDispatchPlan();
		$TblDispatchPlan 		= $WmDispatchPlan->getTable();
		$WmDispatchPlanProduct 	= new WmDispatchPlanProduct();
		$TblPlanProduct 		= $WmDispatchPlanProduct->getTable();
		$Today 					= date("Y-m-d");
		$Inward 				= new ProductInwardLadger();
		$Outward 				= new OutWardLadger();
		$Stock 					= new StockLadger();
		$self 					= (new static)->getTable();
		$MRF_ID 				= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id 		: 0;
		$isFoc 					= (isset($request->is_foc) && !empty($request->is_foc)) ? $request->is_foc 		: 0;
		$bailing				= (isset($request->bailing) && !empty($request->bailing)) ? $request->bailing 	: 0;
		$DispatchID				= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id 	: 0;
		$SalesOrderID			= (isset($request->sales_order_id) && !empty($request->sales_order_id)) ? $request->sales_order_id 	: 0;
		$OriginID				= (isset($request->origin_id) && !empty($request->origin_id)) ? $request->origin_id : 0;
		$DispatchDate			= (isset($request->dispatch_date) && !empty($request->dispatch_date)) ? date("Y-m-d",strtotime($request->dispatch_date)) : "";
		$ClientID				= (isset($request->client_id) && !empty($request->client_id)) ? $request->client_id : 0;
		if($DispatchID > 0) {
			$MRF_ID = WmDispatch::where("id",$DispatchID)->value("bill_from_mrf_id");
		}
		$res 	= array();
		$data 	= self::select(	\DB::raw("$self.title"),
								\DB::raw("$self.id"),
								\DB::raw("$self.description"),
								\DB::raw("$self.bailing"),
								\DB::raw("GetOpeningStockByProduct('".$Today."',".$MRF_ID.",$self.id) as opening_stock"),
								\DB::raw("GetInwardQtyByProduct('".$Today."',".$MRF_ID.",$self.id) as inward_stock"),
								\DB::raw("GetOutwardQtyByProduct('".$Today."',".$MRF_ID.",$self.id) as outward_stock"));
						$data->leftjoin("wm_product_saleable_tagging",function($q) use($self,$MRF_ID){
							$q->on("wm_product_saleable_tagging.product_id","=","$self.id");
							$q->on("wm_product_saleable_tagging.mrf_id","=", \DB::raw($MRF_ID));
						});
    	if($isFoc == 1) {
			$data->whereIn("$self.id",SALES_FOC_PRODUCT_ARRAY);
		}
		if($bailing == 1){
			$data->where("$self.bailing",$bailing);
		}
		$data->where("$self.status",1);
		$data->where("$self.company_id",Auth()->user()->company_id);
		$data->where("$self.sales",1);
		$SQL_QUERY 	= LiveServices::toSqlWithBinding($data,true);
		$result 	= $data->groupby("$self.id")->orderBy("title","ASC")->get()->toArray();
		if(!empty($result)) {
			foreach($result as $key => $value) {
				$client_rate 	= 0;
				$is_disable 	= 0;
				$qty 			= 0;
				if(!empty($DispatchDate)) {
					$GET_RATE_DATA = self::GetSalesOrderClientRate($DispatchDate,$value['id'],$MRF_ID,$ClientID,$OriginID,$DispatchID);
					if(!empty($GET_RATE_DATA))  {
						$client_rate 	= _FormatNumberV2($GET_RATE_DATA[0]->rate);
						$is_disable 	= 1;
						$qty 			= $GET_RATE_DATA[0]->qty;
					}
				}
				$result[$key]['client_rate'] 	= $client_rate;
				$result[$key]['is_disable'] 	= $is_disable;
				$result[$key]['qty'] 			= $is_disable;
				$FridgeQty 						= OutWardLadger::where("ref_id",$DispatchID)
													->where("mrf_id",$MRF_ID)
													->where("type",TYPE_DISPATCH)
													->where("sales_product_id",$value['id'])
													->sum("quantity");
				$INWARD_QTY 					= (isset($value['inward_stock']) && !empty($value['inward_stock']) ? (float)$value['inward_stock'] : 0);
				$OUTWARD_QTY 					= (isset($value['outward_stock']) && !empty($value['outward_stock']) ? (float)$value['outward_stock'] : 0);
				$OPENING_QTY 					= (isset($value['opening_stock']) && !empty($value['opening_stock']) ? (float)$value['opening_stock'] : 0);
				$CURRENT_STOCK  				= ($INWARD_QTY + $OPENING_QTY) - $OUTWARD_QTY;
				$FridgeQty      				= (!empty($FridgeQty)) ? $FridgeQty : 0;
				$CURRENT_STOCK  				= $CURRENT_STOCK + $FridgeQty;
				########### AVG PRICE GIVEN IN PRODUCT LISTING ############
				$AVG_PRICE 						= StockLadger::where("mrf_id",$MRF_ID)->where("stock_date",date("Y-m-d"))->where("product_type",PRODUCT_SALES)->where("product_id",$value['id'])->value("avg_price");
				$result[$key]['avg_price']  	= (!empty($AVG_PRICE)) ? _FormatNumberV2($AVG_PRICE) : 0;
				########### AVG PRICE GIVEN IN PRODUCT LISTING ############
				if($SalesOrderID > 0) {
					$result[$key]['current_stock'] =  ($CURRENT_STOCK > 0) ? _FormatNumberV2($CURRENT_STOCK) : 0;
						$res[] =  $result[$key];
				} else {
					if($CURRENT_STOCK > 0) {
						$result[$key]['current_stock'] =  ($CURRENT_STOCK > 0) ? _FormatNumberV2($CURRENT_STOCK) : 0;
						$res[] =  $result[$key];
					}
				}
			}
		}
		return $res;
	}

	/*
	Use 	: List Sales Product in Dispatch
	Author 	: Axay Shah
	Date 	: 21 April,2020
	*/
	public static function GetSalesOrderClientRate($DispatchDate,$productID,$MRF_ID,$ClientID,$OriginID=0,$DispatchID=0){
		$DispatchDate 	= (!empty($DispatchDate)) ? date("Y-m-d",strtotime($DispatchDate)) : "";
		if($DispatchID > 0){
			$MRF_ID = WmDispatch::where("id",$DispatchID)->value("master_dept_id");
		}
		$client_rate 	= 0;
		$is_disable 	= 0;
		$SQL 	=  "SELECT *,WDPP.qty as qty,WDPP.rate as client_rate,'0' as is_disable FROM wm_dispatch_plan AS WDP
					JOIN wm_dispatch_plan_product AS WDPP on WDP.id = WDPP.dispatch_plan_id
					WHERE '".$DispatchDate."' BETWEEN WDP.dispatch_plan_date and WDP.valid_last_date
					AND WDP.approval_status = 1
					AND WDPP.sales_product_id 	= $productID
					AND WDP.client_master_id 	= $ClientID
					AND WDP.master_dept_id 		= $MRF_ID";
				if($OriginID > 0){
					$SQL .= " AND WDP.origin = $OriginID AND WDP.direct_dispatch = 1";
				}else{
					$SQL .= " AND WDP.direct_dispatch = 0";
				}
				$SQL .=" ORDER BY WDP.id desc LIMIT 0,1";
				$GET_RATE_DATA = \DB::select($SQL);

				if(!empty($GET_RATE_DATA)){
						$GET_RATE_DATA[0]->client_rate 	= _FormatNumberV2($GET_RATE_DATA[0]->rate);
						$GET_RATE_DATA[0]->is_disable 	= 1;
				}

		return $GET_RATE_DATA;
	}
	public static function AutoCompleteSalesProduct($request) {

		$ReadyForDispatchTbl 	= new WmReadyForDispatchMaster();
		$self 					= (new static)->getTable();
		$result 	= array();
		$productId 	= (isset($request->product_id) && !empty($request->product_id)) ? $request->product_id : 0;
		$isFoc 		= (isset($request->is_foc) && !empty($request->is_foc)) ? $request->is_foc : 0;
		$bailing	= (isset($request->bailing) && !empty($request->bailing)) ? $request->bailing : 0;
		$salesFlag	= (isset($request->sales_flag_on) && !empty($request->sales_flag_on)) ? $request->sales_flag_on : 0;
		$ReadyForDispatch = (isset($request->ready_for_dispatch) && !empty($request->ready_for_dispatch)) ? $request->ready_for_dispatch
		 : 0;
		$keyword		= (isset($request->keyword) && !empty($request->keyword)) ? $request->keyword : "";
		$MRF_ID 	= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		if($keyword){
			$data 		= 	self::with(['Bailable' => function ($q){
	    					return $q->select("id",'sales_product_id',"product_dec","qty","company_id");
	   				}])->select(\DB::raw("title"),
					\DB::raw("$self.id"),
					\DB::raw("$self.description"),
					\DB::raw("$self.bailing"));
    				$data->where("$self.title","like","%".$keyword."%");
					if($ReadyForDispatch == 1){
						$data->join($ReadyForDispatchTbl->getTable()." as RD","$self.id","=","RD.sales_product_id");
						$data->where("RD.mrf_id",$MRF_ID);
					}
					if($isFoc == 1){
						$data->where("$self.id",SALES_FOC_PRODUCT);
					}
					if($bailing == 1){
						$data->where("$self.bailing",$bailing);
					}
					$data->where("$self.status",1);
					$data->where("$self.company_id",Auth()->user()->company_id);
					$data->where("$self.sales",1);
					// LiveServices::toSqlWithBinding($data);
					$result = $data->orderBy("title","ASC")->get()->toArray();
					if(!empty($result)){
					foreach($result as $key => $value){
						$result[$key]['avg_rate'] =  0;
					}
				}
			return $result;
		}
	}
	/*
	Use     : Auto Complete sales product list
    Author  : Hasmukhi
    Date    : 05 June,2021
    */
	public static function AutoComplateProductionReportSalesProductList($request){
		$keyword				= (isset($request->keyword) && !empty($request->keyword)) ? $request->keyword : "";
		$WmProcessedProductMaster= new WmProcessedProductMaster();
		$self 					= (new static)->getTable();
		$result 				= array();
		$data 					= 	self::select(\DB::raw("title"),
									\DB::raw("$self.id"),
									\DB::raw("$self.description"));
		$data->where("$self.title","like","%".$keyword."%");
		$data->join($WmProcessedProductMaster->getTable()." as WPPM","$self.id","=","WPPM.sales_product_id");
		$data->where("$self.status",1);
		$data->where("$self.company_id",Auth()->user()->company_id);
		$data->where("$self.sales",1);
		// LiveServices::toSqlWithBinding($data);
		$result = $data->groupBy('WPPM.sales_product_id')->orderBy("title","ASC")->get()->toArray();

		return $result;
	}

	/*
	Use     : CCOF category and Its sub category
    Author  : Axay Shah
    Date    : 26 April,2021
    */
	public static function GetSalesProductCCOFCategoryList($parent_id=0){
		$DATA = \DB::table('wm_sales_ccof_category')
				->select("id","parent_id","title")
				->where("status",1);
		if($parent_id > 0){
			$DATA->where("parent_id",$parent_id);
		}else{
			$DATA->where("parent_id",0);
		}
		$list = $DATA->orderBy("title","ASC")->get();
		return $list;
	}
}
