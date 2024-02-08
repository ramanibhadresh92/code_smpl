<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator,DB,Image;
use App\Models\ViewCompanyCategoryMaster;
use App\Traits\storeImage;
class CompanyCategoryMaster extends Authenticatable
{
    use storeImage;
	protected 	$table               = 'company_category_master';
    protected 	$guarded             = ['id'];
	protected 	$primaryKey          = 'id'; // or null
    protected static $VALID_IMG_TYPE = array("jpg","png","gif","jpeg");
    protected static $category_exist = "";
    protected static $normal_img     = array();
    protected static $select_img     = array();
    protected static $output         = array();
    
    public function product()
    {
        return $this->hasMany('App\Models\CompanyProductMaster','category_id');
    }
    public function productList()
    {
        return $this->hasMany(ViewCompanyProductMaster::class,'category_id');
    }
    public function normal_img()
    {
        return $this->belongsTo(MediaMaster::class,'normal_img');
    }
    public function select_img()
    {
        return $this->belongsTo(MediaMaster::class,'select_img');
    }
    public function customerCategory()
    {
        return $this->hasMany(CompanyCategoryMaster::class,'category_id');
    }
    
    public function productListWithExclude()
    {
        return $this->hasMany(ViewCompanyProductMaster::class,'category_id')
        ->join('customer_products','view_company_product_master.id','!=','customer_products.product_id');
    }
    
    /* 	Use 	: default scope for company category 
		Author 	: Axay Shah
		Date	: 25 Sep,2018	
	*/
	public function scopeCompany($query)
    {
        return $query->where('company_id',auth()->user()->company_id);
    }
	public function scopeCityId($query)
    {
        $cityId = GetBaseLocationCity();
        return $query->whereIn('city_id',$cityId);
    }
	/**
     * Use 		: list with search filter of company category 
	 * Author 	: Axay Shah
	 * Date 	: 25 Sep,2018
     **/
    public static function getCategoryList($requestData){
        $Today          = date('Y-m-d');
        $Yesterday      = date('Y-m-d',strtotime('-1 day'));
        $sortBy         = ($requestData->has('sortBy')      && !empty($requestData->input('sortBy')))    ? $requestData->input('sortBy') 	: "id";
	    $sortOrder      = ($requestData->has('sortOrder')   && !empty($requestData->input('sortOrder'))) ? $requestData->input('sortOrder') : "ASC";
	    $recordPerPage  = !empty($requestData->input('size'))       ?   $requestData->input('size')         : DEFAULT_SIZE;
	    $pageNumber     = !empty($requestData->input('pageNumber')) ?   $requestData->input('pageNumber')   : '';
        /* creating view view_company_category_master for category_master table for more efficent result - Axay shah 25 Sep,2018 */
        $listCategory       = ViewCompanyCategoryMaster::select('*');
        if($requestData->has('params.categoryId') && !empty($requestData->input('params.categoryId')))
        {
            if(!is_array($requestData->input('params.categoryId'))){
                $arr_category_id    = explode(",",$requestData->input('params.categoryId'));
            }else{
                $arr_category_id    = $requestData->input('params.categoryId');
            }
            
            $listCategory->whereIn('id', $arr_category_id);
        }
        if(!empty($requestData->input('params.categoryName')))
        {
            $listCategory->where('category_name','like', "%".$requestData->input('params.categoryName')."%");
        }
        if(!empty($requestData->input('params.parentCategoryName')))
        {
            $listCategory->where('category_name','like', "%".$requestData->input('params.parentCategoryName')."%");
        }
        if(!empty($requestData->input('params.status')))
        {
            $listCategory->where('status',$requestData->input('params.status'));
        }
        if(!empty($requestData->input('params.startDate')) && !empty($requestData->input('params.endDate')))
        {
            $listCategory->whereBetween('created_at',array(date("Y-m-d", strtotime($requestData->input('params.startDate')))." 00:00:00",date("Y-m-d", strtotime($requestData->input('params.endDate')))." 23:59:59"));
        }else if(!empty($requestData->input('params.startDate'))){
           $listCategory->whereBetween('created_at',array(date("Y-m-d", strtotime($requestData->input('params.startDate')))." 00:00:00",$Today." 23:59:59"));
        }else if(!empty($requestData->input('params.endDate'))){
            $listCategory->whereBetween('created_at',array(date("Y-m-d", strtotime($requestData->input('params.endDate')))." 00:00:00",$Today." 23:59:59"));
        }
        return  $listCategory
                ->where('company_id',auth()->user()->company_id)
                ->orderBy($sortBy, $sortOrder)
                ->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
    }
	/**
    * Use      	: get category detail by its id
    * Author  	: Axay Shah
    * Date 		: 25 Sep,2018
    **/
    public static function getCategoryDetails($category_id)
    {
        $category_details   =  array();
        $msg                =  trans('message.RECORD_NOT_FOUND');
        $category_details   = ViewCompanyCategoryMaster::select('view_company_category_master.*','B.category_name as parent_category_name',DB::raw('IFNULL(cm.co2_saved,"") as co2_saved'),'m1.original_name as normal_original_name','m1.original_name as normal_server_name','m.original_name as select_original_name','m.server_name as select_server_name','m.image_path as select_image_path','m1.image_path as normal_image_path')
        ->leftjoin('view_company_category_master as B','view_company_category_master.parent_id','=','B.id')
        ->leftjoin('company_category_co2_mitigate as cm','view_company_category_master.id','=','cm.category_id')
        ->leftjoin('media_master as m','view_company_category_master.select_img','=','m.id')
        ->leftjoin('media_master as m1','view_company_category_master.normal_img','=','m1.id')
        ->where('view_company_category_master.id',$category_id)
        ->first();
        if($category_details){
            $msg =  trans('message.RECORD_FOUND');
            ($category_details->normal_img!='') ? $category_details->normal_img   = url('/').URL_HTTP_IMAGES.$category_details->normal_image_path."/".$category_details->normal_server_name : "";
            ($category_details->select_img!='') ? $category_details->select_img   = url('/').URL_HTTP_IMAGES.$category_details->select_image_path."/".$category_details->select_server_name : "";
        }
        return $category_details;
    }
    public static function editCategory($request)
    {
        $data       = '';
        try {
            DB::beginTransaction();
            $categoryObj  = self::find($request->id);
            if($categoryObj){
                $categoryObj->category_name = (isset($request->category_name) && !empty($request->category_name))   ? $request->category_name   : $categoryObj->category_name;
                $categoryObj->parent_id     = (isset($request->parent_id)     && !empty($request->parent_id))       ? $request->parent_id       : $categoryObj->parent_id;
                $categoryObj->description   = (isset($request->description)   && !empty($request->description))     ? $request->description     : $categoryObj->description;
                $categoryObj->status        = (isset($request->status)        && !empty($request->status))          ? $request->status          : $categoryObj->status;
                $categoryObj->updated_by    = auth()->user()->adminuserid;
                if($request->hasfile('normal_img')) {
                
                    $normal_pic = $categoryObj->verifyAndStoreImage($request,'normal_img',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CATEGORY,Auth()->user()->company_id);
                }
                if($request->hasfile('select_img')) {
                    $select_pic = $categoryObj->verifyAndStoreImage($request,'select_img',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CATEGORY,Auth()->user()->company_id);
                }
                
                $categoryObj->normal_img            = (isset($normal_pic) && !empty($normal_pic)) ? $normal_pic->id   : $categoryObj->normal_img ;
                $categoryObj->select_img            = (isset($select_pic) && !empty($select_pic)) ? $select_pic->id   : $categoryObj->select_img;
                $categoryObj->save();
                self::InsertCategoryLog($categoryObj->id);
            }
            $mitigate = array();
            $request->co2_saved         = (isset($request->co2_saved) && !empty($request->co2_saved)) ? $request->co2_saved : 0;
            $category_mitigate          = \DB::table('company_category_co2_mitigate')->where('category_id',$request->id)->first();
            if(empty($category_mitigate))
            {
                \DB::table('company_category_co2_mitigate')->insert(['category_id' => $request->id,
                'co2_saved' => $request->co2_saved]);
            }
            else
            {
                \DB::table('company_category_co2_mitigate')->where('category_id',$request->id)->update([
                'co2_saved' => $request->co2_saved]);
            }
            $msg                        = trans('message.RECORD_UPDATED');
            $arr_category_data          = self::getCategoryDetails($request->id);
            LR_Modules_Log_CompanyUserActionLog($request,$request->id);
            DB::commit();
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
        }
        $data   = $arr_category_data;
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
   	/**
    * Use 		: Add company category 
	* Author 	: Axay Shah
	* Date 		: 25 Sep,2018
	**/
    public static function addCategory($request)
    {
        DB::beginTransaction();
        try 
        {
            $categoryObj                        = new self();
            $MinMaxOrder                        = self::getMaxMinSortOrder();
            $categoryObj->category_name         = isset($request->category_name) ? $request->category_name : '';
            $categoryObj->ref_category_id       = isset($request->ref_category_id) ? $request->ref_category_id : 0;
            $categoryObj->parent_id             = isset($request->parent_id) ? $request->parent_id : '0';
            $categoryObj->description           = isset($request->description) ? $request->description : '';
            $categoryObj->status                = isset($request->status) ? $request->status : 'Active';
            $categoryObj->sortorder             = (isset($MinMaxOrder['MaxSortOrder']) && $MinMaxOrder['MaxSortOrder'] > 0)?($MinMaxOrder['MaxSortOrder'] + 1):1;
            $categoryObj->created_by            = auth()->user()->adminuserid;
			$categoryObj->company_id            = auth()->user()->company_id;
            if(isset($request->normal_img) && !empty($request->normal_img)) {
                $normal_pic = $categoryObj->verifyAndStoreImage($request,'normal_img',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CATEGORY,Auth()->user()->company_id);
            }
            if(isset($request->select_img) && !empty($request->select_img)) {
                $select_pic = $categoryObj->verifyAndStoreImage($request,'select_img',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CATEGORY,Auth()->user()->company_id);
            }
            
            $categoryObj->normal_img            = (isset($normal_pic) && !empty($normal_pic)) ? $normal_pic->id   : '';
            $categoryObj->select_img            = (isset($select_pic) && !empty($select_pic)) ? $select_pic->id   : '';
            $categoryObj->save();
            self::InsertCategoryLog($categoryObj->id);
            if(isset($request['co2_saved']) && !empty($request['co2_saved']))
            {
                \DB::table('company_category_co2_mitigate')->insert([
                'category_id'           => $categoryObj->id,
                'co2_saved'             => $request['co2_saved']
                ]);
            }
            $msg    = trans('message.RECORD_INSERTED'); 
            LR_Modules_Log_CompanyUserActionLog($request,$$categoryObj->id);
            DB::commit();
        }
        catch (\Exception $e) {
            DB::rollback();
            return false;
            
        }
        
    }
    /**
	* getMaxMinSortOrder
	* @defination : In order to get maximum and minimum sort order.
	**/
    public static function getMaxMinSortOrder()
    {
        $maxMinSort  = self::select(\DB::raw('MAX(sortorder) as MaxSortOrder'), \DB::raw('MIN(sortorder) as MinSortOrder'))
        ->where('company_id',Auth()->user()->company_id)
        ->first();
        return $maxMinSort;
    }
    /**
     * saveCategoryImage
    **/
    public static function saveCategoryImage()
    {
        if(!is_dir(PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.self::$category_exist)) {
            mkdir(PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.self::$category_exist);
        }
        $categoty_data  = self::select('*')->where('id',self::$category_exist)->first();
        /* FOR NORMAL IMAGE */
        $NORMAL_IMAGE_NAME = "";
        if (isset(self::$normal_img) && !empty(self::$normal_img)) 
        {
            if ($categoty_data['normal_img'] != "" && file_exists(PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.self::$category_exist."/".$categoty_data['normal_img'])) {
                unlink(PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.self::$category_exist."/".$categoty_data['normal_img']);
            }
            $extension          = self::$normal_img->getClientOriginalExtension();
            $SERVER_IMAGE_NAME  = PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.self::$category_exist."/";
            self::$normal_img->move($SERVER_IMAGE_NAME, self::$category_exist.".".strtolower($extension));
            $SERVER_IMAGE_NAME  = PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.self::$category_exist."/".self::$category_exist.".".strtolower($extension);
            $RISIZE = PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.self::$category_exist."/"."resize_".basename($SERVER_IMAGE_NAME);
            //system("convert ".$SERVER_IMAGE_NAME." -resize 116x116 -quality 100 ".$RISIZE);
            Image::make($SERVER_IMAGE_NAME)->resize(116, 116)->save($RISIZE);
            unlink($SERVER_IMAGE_NAME);
            rename($RISIZE,$SERVER_IMAGE_NAME);
            $NORMAL_IMAGE_NAME = basename($SERVER_IMAGE_NAME);
        }

        /* FOR NORMAL IMAGE */
    
        /* FOR SELECTED IMAGE */
        $SELECTED_IMAGE_NAME = "";
        if (isset(self::$select_img) && !empty(self::$select_img)) 
        {
            if ($categoty_data['select_img'] != "" && file_exists(PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.self::$category_exist."/".$categoty_data['select_img'])) {
                unlink(PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.self::$category_exist."/".$categoty_data['select_img']);
            }
            $extension          = self::$select_img->getClientOriginalExtension();
            $SERVER_IMAGE_NAME  = PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.self::$category_exist."/";
            self::$select_img->move($SERVER_IMAGE_NAME, self::$category_exist."_selected.".strtolower($extension));
            $SERVER_IMAGE_NAME  = PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.self::$category_exist."/".self::$category_exist."_selected.".strtolower($extension);
            $RISIZE = PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.self::$category_exist."/"."resize_".basename($SERVER_IMAGE_NAME);
            chmod($SERVER_IMAGE_NAME,0777);
            //system("convert ".$SERVER_IMAGE_NAME." -resize 116x116 -quality 100 ".$RISIZE);
            Image::make($SERVER_IMAGE_NAME)->resize(116, 116)->save($RISIZE);
            unlink($SERVER_IMAGE_NAME);
            rename($RISIZE,$SERVER_IMAGE_NAME);
            $SELECTED_IMAGE_NAME = basename($SERVER_IMAGE_NAME);
        }
        if ($NORMAL_IMAGE_NAME != "" || $SELECTED_IMAGE_NAME != "") 
        {
            if ($NORMAL_IMAGE_NAME != "" && $SELECTED_IMAGE_NAME != "") 
            {
                $arr_update['normal_img'] = DBVarConv(basename($NORMAL_IMAGE_NAME));
                $arr_update['select_img'] = DBVarConv(basename($SELECTED_IMAGE_NAME));
            } else if ($NORMAL_IMAGE_NAME != "") {
                $arr_update['normal_img'] = DBVarConv(basename($NORMAL_IMAGE_NAME));
            } else if ($SELECTED_IMAGE_NAME != "") {
                $arr_update['select_img'] = DBVarConv(basename($SELECTED_IMAGE_NAME));
            }
            $categoryObj = self::where('id',self::$category_exist)->update($arr_update);
        }
    }
    /**
     * changeOrder
     * @param : move_flag (up = up value & dn = down value)
     * Use : In order to change sort order of category up/down.
     **/
    public static function changeOrder($request)
    {
        $data = '';
        $msg  = trans('message.RECORD_NOT_FOUND');
        if(isset($request['id']) && !empty($request['id']))
        { 
            if(isset($request['move_flag']) && !empty($request['move_flag']))
            {
                $category_id        = $request['id'];
                $moveflag           = strtolower($request['move_flag']);
                $MinMaxOrder        = self::getMaxMinSortOrder();
                $currentCatData     = self::getCategoryDetails($category_id);
                
                if(!empty($currentCatData))
                {
                    $cur_order          = $currentCatData->sortorder;
                    $new_order          = "";
                    if ($moveflag == "up") {
                        $new_order      = ($cur_order > 1)?($cur_order - 1):1;
                        if ($new_order < $MinMaxOrder['MinSortOrder']) {
                            $new_order  = $MinMaxOrder['MinSortOrder'];
                        }
                    } else if ($moveflag == "dn") {
                        $new_order      = ($cur_order >= 0)?($cur_order + 1):1;
                        if ($new_order > $MinMaxOrder['MaxSortOrder']) {
                            $new_order  = $MinMaxOrder['MaxSortOrder'];
                        }
                    }
                    if ($new_order != "") { 
                        self::where('sortorder',DBVarConv($new_order))
                                ->where('company_id',Auth()->user()->company_id)
                                ->update([
                                    'sortorder'     => DBVarConv($cur_order),
                                    'updated_by'    => auth()->user()->adminuserid,
                                ]);
                        self::where('id',$category_id)
                                ->where('company_id',Auth()->user()->company_id)
                                ->update([
                                    'sortorder'     => DBVarConv($new_order),
                                    'updated_by'    => auth()->user()->adminuserid,
                                ]);
                        $msg    = trans('message.RECORD_UPDATED');
                    }
                }
                else
                {
                    $msg        = trans('message.RECORD_NOT_FOUND');
                }
            }
        }
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    public static function dropdown()
    {
        $arr_category_data  = self::getAllCategoryList();
        $result             = array();
        if(count($arr_category_data) > 0)
        {
            foreach($arr_category_data as $val)
            {
                $menu_array[$val['id']] = array('id' => $val['id'],'name' => $val['category_name'],'parent' => $val['parent_id']);
            }
            if (count($menu_array) > 0) 
            {
                $result = self::generate_menu('0',$menu_array);
            }
            $msg = trans('message.RECORD_FOUND');
        }
        else
        {
            $msg = trans('message.RECORD_NOT_FOUND');
        }
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$result]);
    }
    public static function generate_menu($parent,$menu_array,$level=0,$selected=0)
    {
        $arr_data=array();
        $has_childs = false;
        if (count($menu_array) <= 0 || is_array($menu_array) == false) return;
        foreach($menu_array as $key => $value)
        {
            if ($value['parent'] == $parent)
            {
                //if this is the first child print '<ul>'
                if ($has_childs === false)
                {
                    //don't print '<ul>' multiple times
                    $has_childs = true;
                    $level++;
                }
                
                if ($level == 0) {
                    $selected_val['name']   = $value['name'];
                    $selected_val['key']    = $key;
                    $selected_val['child']  = array();
                } else {
                    $selected_val['name']   = $value['name'];
                    $selected_val['key']    = $key;
                    $selected_val['child']  = array();
                    
                }
                $selected_val['child']=self::generate_menu($key,$menu_array,$level,$selected);
                //call function again to generate nested list for subcategories belonging to this category

            $arr_data[]=$selected_val; 
            }

        }
        if ($has_childs === true) {
            $level = 0;
        }
       // print_r($arr_data);
        return $arr_data;
        //return self::$output;
    }

    public static function copy_images($company_id,$city_id)
    {
        $companyCityCategory =  self::select('*')
                                ->where('company_id',$company_id)
                                // ->where('city_id',$city_id)
                                ->get();
    	foreach($companyCityCategory as $val)
    	{
    		if($val['normal_img']!='')
    		{
    			$arrNormal		= explode(".",$val['normal_img']);
    			$normalPath 	= PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.$arrNormal[0]."/".$val['normal_img'];
    			if(!is_dir(PATH_ABSOLUTE_HTTP_IMAGES_COMPANY_CATEGORY.$val['id'])) {
		            mkdir(PATH_ABSOLUTE_HTTP_IMAGES_COMPANY_CATEGORY.$val['id']);
		        }
    			$dSNormalPath 	= PATH_ABSOLUTE_HTTP_IMAGES_COMPANY_CATEGORY.$val['id']."/".$val['normal_img'];
				if(file_exists($normalPath))
				{
					copy($normalPath,$dSNormalPath);
				}
    		}
    		if($val['select_img']!='' )
    		{
    			$arrSelect		= explode(".",$val['select_img']);
    			$idSelect		= explode("_",$arrSelect[0]);
    			$selectPath 	= PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.$idSelect[0]."/".$val['select_img'];
    			if(!is_dir(PATH_ABSOLUTE_HTTP_IMAGES_COMPANY_CATEGORY.$val['id'])) {
		            mkdir(PATH_ABSOLUTE_HTTP_IMAGES_COMPANY_CATEGORY.$val['id']);
		        }
    			$dSelectPath 	= PATH_ABSOLUTE_HTTP_IMAGES_COMPANY_CATEGORY.$val['id']."/".$val['select_img'];
    			if(file_exists($selectPath))
				{
					copy($selectPath,$dSelectPath);
				}
    		}
    	}
    }

    public function imageUpload($recordId,$imgName){
        if(!is_dir(IMAGE_FOLDER)) {
            mkdir(IMAGE_FOLDER.$recordId);
        }
        if ($categoty_data['normal_img'] != "" && file_exists(PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY.self::$category_exist."/".$categoty_data['normal_img'])) {
            // unlink();
        }  
    }

    /**
    * Use      :   get All category with daynamic select cluse
    * Author   :   Axay Shah
    * Date     :   19 Nov,2018
    **/
    public static function getAllCategoryList($column = "*"){
        return self::select($column)->with(['normal_img','select_img'])
        ->where('company_id',Auth()->user()->company_id)
        ->where('status',COMPANY_CATEGORY_STATUS_ACTIVE)
        ->get();
    }

    /*
    *   Use     :   Change status of Company Category
    *   Author  :   Axay Shah
    *   Date    :   29 Dec,2018
    */
    public static function changeStatus($categoryId,$status){
        return self::where('id',$categoryId)->update(['status'=> $status]);
    }

    /*
    *   Use     :   INSERT COMPANMY CATEGORY LOG
    *   Author  :   Axay Shah
    *   Date    :   10 APRIL 2019
    */
    public static function InsertCategoryLog($id){
        $table = (new static)->getTable();
        $query = "INSERT into company_category_master_log (  company_id,
        category_id,
        city_id,
        ref_category_id,
        category_name,
        parent_id,
        description,
        normal_img,
        select_img,
        color_code,
        sortorder,
        created_by,
        updated_by,
        created_at,
        updated_at,
        status) 
        select company_id,
  id,
  city_id,
  ref_category_id,
  category_name,
  parent_id,
  description,
  normal_img,
  select_img,
  color_code,
  sortorder,
  created_by,
  updated_by,
  created_at,
  updated_at,
  `status`
        FROM $table where id = ".$id;

        \DB::select($query);
    }
}
