<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\CompanyProductQualityParameter;
use App\Models\ViewCompanyProductMaster;
use App\Models\StockLadger;
use App\Traits\storeImage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Classes\PurchaseProductExport;
use App\Models\NetSuitMasterDataProcessMaster;
use Excel;
use App\Facades\LiveServices;
class CompanyProductMaster extends Model
{
	protected 	$table 		=	'company_product_master';
	protected 	$guarded 	=	['id'];
	protected 	$primaryKey =	'id'; // or null
	public 		$timestamps = 	true;
	use storeImage;
	protected $casts = [
	   'id' => 'integer',
	   "product_id" => "integer"
	];
	public function category()
	{
		return $this->belongsTo('App\CompanyCategoryMaster','id');
	}

	public function productQuality()
	{
		return $this->hasOne(CompanyProductQualityParameter::class,'product_id');
	}
	public function normal_img()
	{
		return $this->belongsTo(MediaMaster::class,'normal_img');
	}
	public function select_img()
	{
		return $this->belongsTo(MediaMaster::class,'select_img');
	}

	/*
	use  	: List master Products
	Author 	: Axay Shah
	Date 	: 27 Sep,2018
	*/
	public static function listProduct($request){
		$result         = array();
		$table          = new ViewCompanyProductMaster();
		$self           = $table->getTable();
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";

		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 10;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$list 			= ViewCompanyProductMaster::select(	"$self.id",
															"$self.company_id",
															"$self.city_id",
															\DB::raw("$self.product_name as name"),
															\DB::raw("$self.net_suit_code"),
															\DB::raw("$self.hsn_code"),
															"$self.category_name",
															"$self.status",
															"$self.created_date",
															"$self.updated_date",
															"$self.created_at",
															"$self.updated_at",
															"$self.created",
															"$self.updated",
															"$self.net_suit_class",
															"$self.net_suit_department",
															"P.para_value as tagging_id_name",
															"P1.para_value as net_suit_class_name",
															"P2.para_value as net_suit_department_name",
															"$self.cgst",
															"$self.sgst",
															"$self.igst",
															"$self.weight_in_kg",
															"$self.mapped_purchase_product",
															"UNIT.para_value AS PRODUCT_UNIT")
		->leftjoin("parameter as UNIT","$self.para_unit_id","=","UNIT.para_id")
		->leftjoin("parameter as P","$self.product_tagging_id","=","P.para_id")
		->leftjoin("parameter as P1","$self.net_suit_class","=","P1.para_id")
		->leftjoin("parameter as P2","$self.net_suit_department","=","P2.para_id");
		if($request->has('params.hsn_code') && !empty($request->input('params.hsn_code')))
        {
            $list->where("$self.hsn_code",$request->input('params.hsn_code'));
        }
		if($request->has('params.net_suit_code') && !empty($request->input('params.net_suit_code')))
		{
			$list->where("$self.net_suit_code",'like', '%'.$request->input('params.net_suit_code').'%');
		}
		if($request->has('params.id') && !empty($request->input('params.id')))
		{
			$product_id = explode(',',$request->input('params.id'));
			$list->whereIn("$self.id", $product_id);
		}
		if($request->has('params.status') && !empty($request->input('params.status')))
		{
			$list->where("$self.para_status_id",$request->input('params.status'));
		}
		if($request->has('params.name') && !empty($request->input('params.name')))
		{
			$list->where("$self.product_name",'like', '%'.$request->input('params.name').'%');
		}
		if($request->has('params.category_name') && !empty($request->input('params.category_name')))
		{
			$list->where("$self.category_name",'like', '%'.$request->input('params.category_name').'%');
		}

		if($request->has('params.product_tagging_id') && !empty($request->input('params.product_tagging_id')))
		{
			$productTagging = implode(",",$request->input('params.product_tagging_id'));
			$list->where("$self.product_tagging_id",$productTagging);
		}
		if($request->has('params.category_id') && !empty($request->input('params.category_id')))
		{
			$list->where("$self.category_id",$request->input('params.category_id'));
		}
		 if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
		{
			$list->whereBetween("$self.created_date",array(date("Y-m-d", strtotime($request->input('params.startDate'))),date("Y-m-d", strtotime($request->input('params.endDate')))));
		}else if(!empty($request->input('params.startDate'))){
		   $list->whereBetween("$self.created_date",array(date("Y-m-d", strtotime($request->input('params.startDate'))),$Today));
		}else if(!empty($request->input('params.endDate'))){
			$list->whereBetween("$self.created_date",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
		}
		$list->where("$self.company_id",Auth()->user()->company_id);
		if($request->has('ex') && !empty($request->input('ex')) && $request->input('ex') == EXPORT_ALL)
		{

		   $recordPerPage   = $list->get();
		   $recordPerPage   = count($recordPerPage);
		   $result          = $list->paginate($recordPerPage);
		}else{
			$list->orderBy($sortBy, $sortOrder);
			$result    = $list->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		}
		$toArray        = $result->toArray();
		if(!empty($toArray)){
			$toArray['export_url'] = url('/')."/".PURCHASE_TO_SALES_EXPORT."/".base64_encode(Auth()->user()->company_id);
		}
		return $toArray;
	}
	/*
	use     : add company products
	Author  : Axay Shah
	Date    : 26 Sep,2018
	*/
	public static function add($request){
		DB::beginTransaction();
		$created_by = Auth()->user()->adminuserid;
		try{
			$ADD 						= new self();
			$ADD->category_id       	= $request->category_id;
			$ADD->name              	= $request->name;
			$ADD->co2_saved         	= $request->co2_saved;
			$ADD->enurt             	= $request->enurt;
			$ADD->product_volume    	= (isset($request->product_volume) && !empty($request->product_volume))?$request->product_volume:0;
			$ADD->para_status_id    	= $request->para_status_id;
			$ADD->para_unit_id      	= $request->para_unit_id;
			$ADD->processing_cost   	= $request->processing_cost;
			$ADD->created_by        	= $created_by;
			$ADD->para_group_id     	= $request->para_group_id;
			$ADD->city_id           	= 0;
			$ADD->company_id        	= Auth()->user()->company_id;
			$ADD->mapped_purchase_product = (isset($request->mapped_purchase_product) && !empty($request->mapped_purchase_product)) ?  $request->mapped_purchase_product  : 0 ;
			$ADD->product_tagging_id	= (isset($request->product_tagging_id) && !empty($request->product_tagging_id)) ?  $request->product_tagging_id  : 0 ;
			$ADD->net_suit_code     	= (isset($request->net_suit_code) && !empty($request->net_suit_code)) ?  $request->net_suit_code  : "" ;
			$ADD->sortable          	= (isset($request->sortable) && !empty($request->sortable)) ? $request->sortable : 0 ;
			$ADD->hsn_code          	= (isset($request->hsn_code) && !empty($request->hsn_code)) ?  $request->hsn_code  : NULL ;
			$ADD->cgst              	= (isset($request->cgst) && !empty($request->cgst)) ?  $request->cgst  : 0 ;
			$ADD->sgst              	= (isset($request->sgst) && !empty($request->sgst)) ?  $request->sgst  : 0 ;
			$ADD->igst              	= (isset($request->igst) && !empty($request->igst)) ?  $request->igst  : 0 ;
			$ADD->net_suit_class    	= (isset($request->net_suit_class) && !empty($request->net_suit_class)) ?  $request->net_suit_class  : 0 ;
			$ADD->net_suit_department 	= (isset($request->net_suit_department) && !empty($request->net_suit_department)) ?  $request->net_suit_department  : 0 ;
			$ADD->ccof_category_id 		= (isset($request->ccof_category_id) && !empty($request->ccof_category_id)) ?  $request->ccof_category_id  : 0 ;
			$ADD->ccof_sub_category_id	= (isset($request->ccof_sub_category_id) && !empty($request->ccof_sub_category_id)) ?  $request->ccof_sub_category_id  : 0 ;
			$ADD->weight_in_kg			= (isset($request->weight_in_kg) && !empty($request->weight_in_kg))?$request->weight_in_kg:0;
			if($request->hasfile('normal_img')) {
				$normal_pic = $ADD->verifyAndStoreImage($request,'normal_img',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_PRODUCT,Auth()->user()->company_id);
			}
			if($request->hasfile('select_img')) {
				$select_pic = $ADD->verifyAndStoreImage($request,'select_img',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_PRODUCT,Auth()->user()->company_id);
			}
			$ADD->normal_img            = (isset($normal_pic) && !empty($normal_pic)) ? $normal_pic->id   : "" ;
			$ADD->select_img            = (isset($select_pic) && !empty($select_pic)) ? $select_pic->id   : "";
			if($ADD->save()){
				####### NET SUIT MASTER ##########
				$tableName =  (new static)->getTable();
				NetSuitMasterDataProcessMaster::NetSuitStoreMasterData($tableName,$ADD->id,Auth()->user()->company_id);
				####### NET SUIT MASTER ##########
				$PQP = CompanyProductQualityParameter::add($request,$ADD->id);
				LR_Modules_Log_CompanyUserActionLog($request,$ADD->id);
			}
			DB::commit();
			return $ADD;
		}catch (\Exception $e) {
			DB::rollback();
			return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>$e->getMessage(),"data" =>""]);
		}

	}
	/*
	use     : Update company Product by id
	Author  : Axay Shah
	Date    : 26 Sep,2018
	*/
	public static function updateProduct($request){
		DB::beginTransaction();
		try{
			$UPDATE 						= self::findOrFail($request->id);
			$UPDATE->mapped_purchase_product = (isset($request->mapped_purchase_product) && !empty($request->mapped_purchase_product)) ?  $request->mapped_purchase_product  : $UPDATE->mapped_purchase_product ;
			$UPDATE->name              		= (isset($request->name) && !empty($request->name))? $request->name : $UPDATE->name;
			$UPDATE->category_id       		= (isset($request->category_id) && !empty($request->category_id)) ? $request->category_id : $UPDATE->category_id;
			$UPDATE->para_status_id    		= (isset($request->para_status_id) && !empty($request->para_status_id)) ? $request->para_status_id : $UPDATE->para_status_id;
			$UPDATE->para_unit_id      		= (isset($request->para_unit_id) && !empty($request->para_unit_id)) ? $request->para_unit_id : $UPDATE->para_unit_id;
			$UPDATE->co2_saved         		= (isset($request->co2_saved) && !empty($request->co2_saved)) ? $request->co2_saved : $UPDATE->co2_saved;
			$UPDATE->processing_cost   		= (isset($request->processing_cost) && !empty($request->processing_cost))? $request->processing_cost : $UPDATE->processing_cost;
			$UPDATE->enurt             		= (isset($request->enurt ) && !empty($request->enurt ))? $request->enurt : $UPDATE->enurt;
			$UPDATE->para_group_id     		= (isset($request->para_group_id) && !empty($request->para_group_id)) ? $request->para_group_id : $UPDATE->para_group_id;
			$UPDATE->product_volume    		= (isset($request->product_volume) && !empty($request->product_volume)) ? $request->product_volume : $UPDATE->product_volume ;
			$UPDATE->product_tagging_id		= (isset($request->product_tagging_id) && !empty($request->product_tagging_id)) ? $request->product_tagging_id : 0 ;
			$UPDATE->net_suit_code     		= (isset($request->net_suit_code) && !empty($request->net_suit_code)) ?  $request->net_suit_code  : NULL ;
			$UPDATE->hsn_code          		= (isset($request->hsn_code) && !empty($request->hsn_code)) ?  $request->hsn_code  : NULL ;
			$UPDATE->sortable          		= (isset($request->sortable) && !empty($request->sortable)) ? $request->sortable : 0 ;
			$UPDATE->cgst              		= (isset($request->cgst) && !empty($request->cgst)) ?  $request->cgst  : 0 ;
			$UPDATE->sgst              		= (isset($request->sgst) && !empty($request->sgst)) ?  $request->sgst  : 0 ;
			$UPDATE->igst              		= (isset($request->igst) && !empty($request->igst)) ?  $request->igst  : 0 ;
			$UPDATE->net_suit_class 		= (isset($request->net_suit_class) && !empty($request->net_suit_class)) ?  $request->net_suit_class  : 0 ;
			$UPDATE->net_suit_department 	= (isset($request->net_suit_department) && !empty($request->net_suit_department)) ?  $request->net_suit_department  : 0 ;
			$UPDATE->ccof_category_id 		= (isset($request->ccof_category_id) && !empty($request->ccof_category_id)) ?  $request->ccof_category_id  : 0 ;
			$UPDATE->ccof_sub_category_id 	= (isset($request->ccof_sub_category_id) && !empty($request->ccof_sub_category_id)) ?  $request->ccof_sub_category_id  : 0 ;
			$UPDATE->weight_in_kg			= (isset($request->weight_in_kg) && !empty($request->weight_in_kg))?$request->weight_in_kg:0;
			$UPDATE->updated_by         	= Auth()->user()->adminuserid;
			$UPDATE->city_id            	= 0;
			$UPDATE->company_id         	= Auth()->user()->company_id;
			if($request->hasfile('normal_img')) {
				$normal_pic = $UPDATE->verifyAndStoreImage($request,'normal_img',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_PRODUCT,Auth()->user()->company_id);
			}
			if($request->hasfile('select_img')) {
				$select_pic = $UPDATE->verifyAndStoreImage($request,'select_img',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_PRODUCT,Auth()->user()->company_id);
			}
			$UPDATE->normal_img            = (isset($normal_pic) && !empty($normal_pic)) ? $normal_pic->id   : $UPDATE->normal_img ;
			$UPDATE->select_img            = (isset($select_pic) && !empty($select_pic)) ? $select_pic->id   : $UPDATE->select_img;
			if($UPDATE->save()){
				####### NET SUIT MASTER ##########
				$tableName =  (new static)->getTable();
				NetSuitMasterDataProcessMaster::NetSuitStoreMasterData($tableName,$UPDATE->id,Auth()->user()->company_id);
				####### NET SUIT MASTER ##########
				$PQP = CompanyProductQualityParameter::updateQualityByProduct($request,$UPDATE->id);
				LR_Modules_Log_CompanyUserActionLog($request,$request->id);
			}
			DB::commit();
			return $UPDATE;
		}catch(\Exception $e) {
			DB::rollback();
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
		}
	}
	/*
	use     : List company Product
	Author  : Axay Shah
	Date    : 09 Sep,2018
	*/
	public static function companyProduct($column = "*"){
		return self::select($column)->with(['normal_img','select_img'])
		->where('company_id',Auth()->user()->company_id)
		->where('para_status_id',PRODUCT_STATUS_ACTIVE)
		->get();
	}

	/*
	use     : Get By Id
	Author  : Axay Shah
	Date    : 21 Nov,2018
	*/
	public static function getById($productId){
		$product =  self::select("company_product_master.*",'m1.original_name as normal_original_name','m1.server_name as normal_server_name','m1.original_name as select_original_name','m2.server_name as select_server_name','m1.image_path as select_image_path','m1.image_path as normal_image_path','cq.*')
		->join('company_product_quality_parameter as cq','company_product_master.id','=','cq.product_id')
		->leftjoin('media_master as m1','company_product_master.normal_img','=','m1.id')
		->leftjoin('media_master as m2','company_product_master.select_img','=','m2.id')
		->where('company_product_master.id',$productId)
		->first();


		if($product){
			$product->mapped_purchase_product_name   = self::GetPurchaseProductsName($product->mapped_purchase_product); 

		   if(empty($product->net_suit_code))
			$product->net_suit_code = "";
			if(!empty($product->product_tagging_id)){
				$product->product_tagging_id = array_map('intval', explode(",",$product->product_tagging_id));
			}
			($product->normal_img!='') ? $product->normal_img   = url('/').URL_HTTP_IMAGES.$product->normal_image_path."/".$product->normal_server_name : "";
			($product->select_img!='') ? $product->select_img   = url('/').URL_HTTP_IMAGES.$product->select_image_path."/".$product->select_server_name : "";
		}
		return $product;

	}


	/*
	*   Use     :   Change status of Company Product
	*   Author  :   Axay Shah
	*   Date    :   29 Dec,2018
	*/
	public static function changeStatus($productId,$status){
		return self::where('id',$productId)->update(['para_status_id'=> $status]);
	}

	public static function productImage(){
		$data = DB::table('company_category_master')->where('id',"1")->get();
		if($data){
			foreach($data as $d){
				$mediaMaster = new MediaMaster();
				$mediaMaster->company_id 	= (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0;
				$mediaMaster->city_id 	    = 0;
				$mediaMaster->original_name = $d->normal_img;
				$mediaMaster->server_name   = $d->select_img;
				$mediaMaster->image_path    = 'company/1/category/'.$d->id;
				if($mediaMaster->save()){
					CompanyCategoryMaster::where('id',$d->id)->update(["normal_img"=>$mediaMaster->id,"select_img"=>$mediaMaster->id]);
				}
			}
		}
	}

	public static function ExportProduct(){
		$date =date("Y-m-d");
		return Excel::download(new PurchaseProductExport, 'test.xlsx');
	}

	public function download()
	{
		return Excel::download('Laravel Excel', function($excel)  {

			$excel->sheet('Excel sheet', function($sheet) {
				$sheet->loadView('test');
				$sheet->setOrientation('landscape');
			});
		})->export('xls');
	}

	/*
	use     : Company Product List With Its Stock of Specific Date
	Author  : Axay Shah
	Date    : 09 Sep,2018
	*/
	public static function companyProductListWithStock($date=""){
		$Stock          = new StockLadger();
		$MRF_ID         = (!empty(Auth()->user()->mrf_user_id)) ? Auth()->user()->mrf_user_id : 0;
		$Product        = array();
		if(!empty($date)){
			$Quality    =   new CompanyProductQualityParameter();
			$Self       =  (new static)->getTable();
			$Product    =  self::select("$Self.id",\DB::raw("CONCAT($Self.name,'',Q.parameter_name) as product_name"))
						->join($Quality->getTable()." as Q","$Self.id","=","Q.product_id")
						->where("$Self.company_id",Auth()->user()->company_id)
						->where("$Self.para_status_id",PRODUCT_STATUS_ACTIVE)
						->get()
						->toArray();
			if(!empty($Product)){
				foreach($Product as $key => $value){
					$Product[$key]["stock"] = _FormatNumberV2(StockLadger::GetPurchaseProductCurrentStock($date,$value['id'],$MRF_ID));
				}
			}
		}
		return $Product;
	}


	/*
	Use     : CCOF category and Its sub category
	Author  : Axay Shah
	Date    : 26 April,2021
	*/
	public static function GetPurchaseProductCCOFCategoryList($parent_id=0){
		$DATA = \DB::table('purchase_ccof_category')
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
	/*
    Use     : Get Purchase & Sales Product List
    Author  : Hardyesh Gupta
    Date    : 28 September, 2023
    */
    public static function GetPurchaseProductsName($product_id){
        $data = "";
        $ProductQuality =  new CompanyProductQualityParameter();
        $ProductMaster  =  new CompanyProductMaster();
        $PMT            =  $ProductMaster->getTable();
        $data = CompanyProductMaster::select("$PMT.id as product_id",
                   DB::raw("CONCAT($PMT.name,' ',QAL.parameter_name) AS product_name")
                )
                ->join($ProductQuality->getTable()." as QAL","$PMT.id","=","QAL.product_id")
        ->where("$PMT.id",$product_id)->get()->toArray();
        return $data;
    }
}
