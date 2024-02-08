<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmDispatchBrandingImgMapping extends Model
{
    //
    protected 	$table 		=	'wm_dispatch_branding_img_mapping';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;

	
	public static function GetImageDetails($dispatch_id=0){
		return self::where("dispatch_id",$dispatch_id)->get()->toArray();
	}

	public static function GetImageMediaIds($dispatch_id=0){
		return self::where("dispatch_id",$dispatch_id)->pluck("image_id")->toArray();
	}

	/*
	Use 	:  Save Dispatch Media 
	Author 	:  Axay Shah
	Date 	:  22 Jan.2020
	*/
	public static function AddDispatchMedia($dispatch_id = 0,$img_id=0){
		$id 						= 0;
		$mediaMaster 				= new self();
        $mediaMaster->dispatch_id 	= $dispatch_id;
        $mediaMaster->image_id 		= $img_id;
      	$mediaMaster->created_by    = (isset(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : 0;
        if($mediaMaster->save()){
        	$id = $mediaMaster->id;
        }
        return $id;
	}

}
