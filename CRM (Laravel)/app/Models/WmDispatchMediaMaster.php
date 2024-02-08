<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class WmDispatchMediaMaster extends Model implements Auditable
{
    //
    protected 	$table 		=	'wm_dispatch_media_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	public function getImageUrlAttribute()	 	 
	{	 	 
		return url("/")."/".$this->image_path."/".$this->image_name;
	}
	/*
	Use 	:  Save Dispatch Media 
	Author 	:  Axay Shah
	Date 	:  22 Jan.2020
	*/
	public static function AddDispatchMedia($dispatch_id = 0,$image_name='',$resize_name='',$imagePath='',$media_type=0)
	{
		$id 						= 0;
		$mediaMaster 				= new self();
        $mediaMaster->company_id    = (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0;
        $mediaMaster->dispatch_id   = $dispatch_id;
        $mediaMaster->image_name    = $image_name;
        $mediaMaster->resize_name   = $resize_name;
        $mediaMaster->image_path    = $imagePath;
        $mediaMaster->media_type    = $media_type;
        $mediaMaster->created_by    = (isset(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : 0;
        if($mediaMaster->save()){
        	$id = $mediaMaster->id;
        }
        return $id;
	}

	/*
	Use 	:  Get Image By Id 
	Author 	:  Axay Shah
	Date 	:  23 June.2020
	*/
	public static function GetImgById($ImageID=0){
		$url 		= ""; 
		$imageData 	= self::find($ImageID);
		if($imageData){
			$url = $imageData->image_url;
		}
		return $url;
	}

	/*
	Use 	:  List all image of perticular dispatch
	Author 	:  Axay Shah
	Date 	:  03 December 2020
	*/
	public static function GetAllImageByDispatchID($DispatchID=0){
		$url 		= array(); 
		$imageData 	= self::where("dispatch_id",$DispatchID)->get();
		// prd($imageData);
		if(!empty($imageData)){
			foreach($imageData as $raw){
				$imageArr = array();
				$imageArr['image'] = url("/")."/".$raw->image_path."/".$raw->image_name;
				$imageArr['thumbImage'] = url("/")."/".$raw->image_path."/".$raw->image_name;
				$url[] = $imageArr;
			}
		}
		return $url;
	}
}
