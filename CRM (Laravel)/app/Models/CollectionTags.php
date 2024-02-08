<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Traits\storeImage;
use DB;
use App\Models\MediaMaster;
use Illuminate\Support\Facades\URL;

class CollectionTags extends Model
{
    use storeImage;
    protected 	$table              = 'collection_tags';
    protected 	$guarded            = ['tag_id'];
    protected 	$primaryKey         = 'tag_id'; // or null
    public      $timestamps         = true;
    
    public function normalImagePath()
    {
        return $this->belongsTo(MediaMaster::class,'normal_img');
    }
    public function selectImagePath()
    {
        return $this->belongsTo(MediaMaster::class,'select_img');
    }
    public function getNormalImgAttribute($value)
    {
        $media = MediaMaster::find($value);
        if($media){
            return $media->original_name;
        }
        return url('/'.PATH_IMAGE)."/".$value;
    }
    public function getSelectImgAttribute($value)
    {
        $media = MediaMaster::find($value);
        if($media){
            return $media->server_name;
        }
        return url('/'.PATH_IMAGE)."/".$value;
    }
    public static function collectionTagList($request){
        $cityId         =   GetBaseLocationCity();
        $sortBy         =   ($request->has('sortBy')      && !empty($request->input('sortBy'))) 
                            ? $request->input('sortBy')    : "tag_id";
        $sortOrder      =   ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) 
                            ? $request->input('sortOrder') : "ASC";
        $recordPerPage  =   !empty($request->input('size'))       ?   $request->input('size')         : 15;
        $pageNumber     =   !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : 1;
        $data           =   self::leftjoin('media_master','normal_img','=','media_master.id')
                            ->leftjoin('media_master as media_master1','select_img','=','media_master1.id')
                            ->leftjoin('adminuser as a1','collection_tags.created_by','=','a1.adminuserid')
                            ->leftjoin('adminuser as a2','collection_tags.updated_by','=','a2.adminuserid')
                            ->leftjoin('location_master as L','collection_tags.city_id','=','L.location_id')
                            ->select('collection_tags.*',\DB::raw("L.city as city_name"),
                            DB::raw('concat(a1.firstname," ",a1.lastname) AS createdBy'),
                            DB::raw('concat(a2.firstname," ",a2.lastname) AS updatedBy'),
                            DB::raw('concat(media_master1.image_path,"/",media_master1.server_name) AS select_img'),
                            DB::raw('concat(media_master.image_path,"/",media_master.server_name) AS normal_img')
                        );
                            
        if($request->has('params.tag_id') && !empty($request->input('params.tag_id')))
        {
            $tagId = explode(',',$request->input('params.tag_id'));
            $data->whereIn('collection_tags.tag_id',array($tagId));
        }
        // if($request->has('params.city_id') && !empty($request->input('params.city_id')))
        // {
        //     $data->where('collection_tags.city_id',$request->input('params.city_id'));
        // }else{
        //     $data->whereIn('collection_tags.city_id',$cityId);
        // }
        if($request->has('params.tag_name') && !empty($request->input('params.tag_name')))
        {
            $data->where('collection_tags.tag_name','like','%'.$request->input('params.tag_name').'%');
        }
        
        if($request->has('params.status') && !empty($request->input('params.status')))
        {
            $data->where('collection_tags.status',$request->input('params.status'));
        }
        if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
        {
            $data->whereBetween('collection_tags.created_at',array(date("Y-m-d", strtotime($request->input('params.startDate')))." 00:00:00",date("Y-m-d", strtotime($request->input('params.endDate')))." 23:59:59"));
        }else if(!empty($request->input('params.startDate'))){
           $data->whereBetween('collection_tags.created_at',array(date("Y-m-d", strtotime($request->input('params.startDate')))." 00:00:00",$Today." 23:59:59"));
        }else if(!empty($request->input('params.endDate'))){
            $data->whereBetween('collection_tags.created_at',array(date("Y-m-d", strtotime($request->input('params.endDate')))." 00:00:00",$Today." 23:59:59"));
        }
         return $data->where('collection_tags.company_id',auth()->user()->company_id)
                ->orderBy($sortBy, $sortOrder)
                ->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);

    }



    /*
	use  	: Add collection Tag
	Author 	: Axay Shah
	Date 	: 11 Oct,2018
	*/
	public static function add($request){
		DB::beginTransaction();
		try{
			$userId 	                    = Auth()->user()->adminuserid;
			$collectionTag                  = new self();
            $collectionTag->tag_name   	    = $request->tag_name;
			$collectionTag->description	    = $request->description;
			$collectionTag->status 		    = (isset($request->status) 		     && !empty($request->status)) 	    ? $request->status 		: "";
            $collectionTag->company_id 	    = (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0;
            $collectionTag->city_id 	    = (isset($request->city_id)       && !empty($request->city_id))   ? $request->city_id : 0;
            $collectionTag->created_by 	    = $userId;
            if($request->hasfile('normal_img')) {
                $normalImg = $collectionTag->verifyAndStoreImage($request,'normal_img',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_COLLECTIONTAG,Auth()->user()->company_id);
            }
            if($request->hasfile('select_img')) {
                $selectImg = $collectionTag->verifyAndStoreImage($request,'select_img',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_COLLECTIONTAG,Auth()->user()->company_id);
            }
            $collectionTag->normal_img      = (isset($normalImg) && !empty($normalImg)) ? $normalImg->id : NULL;
            $collectionTag->select_img      = (isset($selectImg) && !empty($selectImg)) ? $selectImg->id : NULL;
            $collectionTag->save();
            LR_Modules_Log_CompanyUserActionLog($request,$collectionTag->tag_id);
			DB::commit();
			return $collectionTag;
		}catch(\Exception $e) {
            DB::rollback();
            return false;
        }
    }
    
    /*
	use  	: Add collection Tag
	Author 	: Axay Shah
	Date 	: 11 Oct,2018
	*/
	public static function updateRecord($request){
        DB::beginTransaction();
        $imageId = 0;
		try{
            $userId 	                    = Auth()->user()->adminuserid;
            $collectionTag = self::findOrFail($request->id);
			$collectionTag->tag_name   	    = (isset($request->tag_name) 		 && !empty($request->tag_name)) 	? $request->tag_name 	: $collectionTag->tag_name;
			$collectionTag->description	    = (isset($request->description) 	 && !empty($request->description)) 	? $request->description : $collectionTag->description;
			$collectionTag->status 		    = (isset($request->status) 		     && !empty($request->status)) 	    ? $request->status 		: $collectionTag->status;
            $collectionTag->company_id 	    = (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : $collectionTag->company_id ;
            $collectionTag->city_id         = (isset($request->city_id)       && !empty($request->city_id))   ? $request->city_id : $collectionTag->city_id;
            $collectionTag->created_by 	    = $userId;
            if($request->hasfile('normal_img')) {
                (!empty($collectionTag->normal_img)) ? $imageId = $collectionTag->normal_img : $imageId;
                $normalImg = $collectionTag->verifyAndStoreImage($request,'normal_img',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_COLLECTIONTAG,Auth()->user()->company_id,$imageId);
            }
            if($request->hasfile('select_img')) {
                (!empty($collectionTag->select_img)) ? $imageId = $collectionTag->select_img : $imageId;
                $selectImg = $collectionTag->verifyAndStoreImage($request,'select_img',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_COLLECTIONTAG,Auth()->user()->company_id,$imageId);
            }
            $collectionTag->normal_img      = (isset($normalImg) && !empty($normalImg)) ? $normalImg->id : $collectionTag->normal_img;
            $collectionTag->select_img      = (isset($selectImg) && !empty($selectImg)) ? $selectImg->id : $collectionTag->select_img;
            $collectionTag->save();
            LR_Modules_Log_CompanyUserActionLog($request,$collectionTag->tag_id);
			DB::commit();
			return $collectionTag;
		}catch(\Exception $e) {
            DB::rollback();
            return false;
        }
	}

    /*
	use  	: Record Get By Id
	Author 	: Axay Shah
	Date 	: 11 Oct,2018
	*/
	public static function getById($id){
        try{
            $data = self::find($id);
            if($data){
                dd($data->selectImagePath);
                (!empty($data->select_img)) ? $select_img = CONST_HTTP_NAME.request()->getHttpHost()."/".PATH_IMAGE."/".$data->selectImagePath->server_name : $select_img = " ";
                (!empty($data->normal_img)) ? $normal_img = CONST_HTTP_NAME.request()->getHttpHost()."/".PATH_IMAGE."/".$data->selectImagePath->server_name : $normal_img = " ";
                $data['select_img']= $select_img;
                $data['normal_img']=$normal_img;
            }
            return $data;
		}catch(\Exception $e) {
            dd($e);
            return false;
        }
	}


    /**
     * Function Name : retrieveTagsByCustomer
     * @param $customer_id
     * @return
     * @author Sachin Patel
     * @date 04-02-2019
     */
    public static function retrieveTagsByCustomer($customer_id,$tag_name=false)
    {
        $arrResult	= array();
        if (!$tag_name) {
            return DB::table('customer_collection_tags')::where('customer_id',$customer_id)->get();

        } else {
            $data = self::select('collection_tags.tag_id','collection_tags.select_img','original_name','image_path')
                ->join('customer_collection_tags','collection_tags.tag_id','=','customer_collection_tags.tag_id')
                ->join('media_master','media_master.id','=','collection_tags.select_img')
                ->where('customer_collection_tags.customer_id',$customer_id)->get();

            return $data;

        }

    }
    
}
