<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class MediaMaster extends Model
{
    protected $table        = 'media_master';
    protected $guarded      = ['id'];
    protected $primaryKey   = 'id'; // or null
    public $timestamps      = true;


    public function category_normal_img()
    {
        return $this->hasOne(CompanyCategoryMaster::class,'normal_img');
    }
    public function category_select_img()
    {
        return $this->hasOne(CompanyCategoryMaster::class,'select_img');
    }
    public function getOriginalNameAttribute($value)
    {
        if(!empty($value)){
            return url('/'.PATH_IMAGE)."/".$this->image_path."/".$value;
        }else{
            return $value;
        }
        
    }
    public function getServerNameAttribute($value)
    {
        if(!empty($value)){
            return url('/'.PATH_IMAGE)."/".$this->image_path."/".$value;
        }else{
            return $value;
        }
        
    }


    // public function getServerNameAttribute($value)
    // {
    //     return CONST_HTTP_NAME.request()->getHttpHost()."/".PATH_IMAGE."/".$this->image_path."/".$value;
    // }
    // public function getOriginalNameAttribute($value)
    // {
    //     return public_path(PATH_IMAGE.'/').$this->image_path."/".$value;
    // }
    /*
    Use     : Insert Media record in media master table
    Author  : Axay Shah
    Date    : 25 Oct,2018
    */ 
    public static function add($request){
        $cityId      = (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
        $mediaMaster = new MediaMaster();
        $mediaMaster->company_id 	= (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0;
        $mediaMaster->city_id 	    = $cityId;
        $mediaMaster->original_name = ($request->original_name) ? $request->original_name   : "";
        $mediaMaster->server_name   = ($request->server_name)   ? $request->server_name     : "";
        $mediaMaster->image_path    = ($request->image_path)    ? $request->image_path      : "";
        $mediaMaster->save();
        return $mediaMaster;
    }

    /*
    Use     :  store image which is not matched in aws
    Author  :  Axay Shah
    Date    :  17 April,2019
    */
    public static function AwsFailedImageUpload($request){
        if($request->hasFile('image')){
            $path = "/".PATH_AWS_FAILED_IMAGES;
            $file = $request->file('image');
            if(!is_dir(public_path($path))) {
                mkdir(public_path($path),0777,true);
            }
            $orignalImg     = "AWS_FAILED_USER_ID_".Auth()->user()->adminuserid."_".time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path($path), $orignalImg);
            // $file->save();
        }
    }

    /*
    Use     :  Add Media Master Code
    Author  :  Axay Shah
    Date    :  17 June,2019
    */

    public static function AddMedia($orignal,$server,$path,$companyId,$cityId=0){
        $mediaMaster = new MediaMaster();
        $mediaMaster->company_id    = $companyId;
        $mediaMaster->city_id       = $cityId;
        $mediaMaster->original_name = $orignal;
        $mediaMaster->server_name   = $server;
        $mediaMaster->image_path    = $path;
        if($mediaMaster->save()){
            return $mediaMaster->id;
        }
        return 0;
    }

    


    /*
    Use     :  Upload while Direct dispatch finalize in two folder
    Author  :  Axay Shah
    Date    :  17 June,2019
    */
    public static function ImageUpload($request,$filedName,$path,$oldPath = 0,$withMedia=0){
        if(isset($filedName) && !empty($filedName)){
            $File           = $request[$filedName];
            $Extenstion     = $File->getClientOriginalExtension();
            $OrignalName    = $filedName."_".time().".".$Extenstion;
            $ResizeName     = RESIZE_PRIFIX.$OrignalName;
            $img            = Image::make($File->getRealPath());
                $img->resize(RESIZE_HIGHT, RESIZE_WIDTH, function ($constraint) {
                    $constraint->aspectRatio();
                })->save(public_path(PATH_IMAGE.'/'.$path.'/'.$ResizeName));
            $File->move(public_path(PATH_IMAGE.'/'.$path.'/'.$OrignalName));
            $media = self::AddMedia($OrignalName,$ResizeName,$path,Auth()->user()->company_id);
            return $media;
        }
    }
}