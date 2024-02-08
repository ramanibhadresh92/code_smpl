<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MediaMaster;
class WmBatchMediaMaster extends Model
{
	protected   $table      =   'wm_batch_media_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;

	public function GetBatchMediaImage(){
		return $this->belongsTo(MediaMaster::class,'image_id');
	}
	
	/*
	Use     : Get Batch media list
	Author  : Axay Shah
	Date    : 02 April,2019
	*/
	public static function getBatchMedia($batchId){
		$result     = array();
		$batchData  = self::where("batch_id",$batchId)->get();
		if($batchData){
			foreach($batchData as $batch){
				$media = MediaMaster::find($batch->image_id);
				if($media){
					$result[] = $media;
				}
			}
		}
		return $result;
	}

	/*
	Use     : Insert Batch Media Master
	Author  : Axay Shah
	Date    : 18 July 2019
	*/
	public static function InsertBatchMedia($BatchId = 0,$MediaId = 0,$sleepType = "G",$FromDispatch = 0,$DispatchId = 0,$CreatedBy = 0){
		$MediaId                     = 0;
		$Media                       = new self();
		$Media->batch_id             = $BatchId;
		$Media->image_id             = $MediaId;
		$Media->waybridge_sleep_type = $sleepType;
		$Media->from_dispatch        = $FromDispatch;
		$Media->dispatch_id          = $DispatchId;
		$Media->created_by           = $CreatedBy;
		$Media->created_at           = date("Y-m-d H:i:s");
		if($Media->save()){
			$MediaId = $Media->id;
		}
		return $MediaId ;
	}

	/*
	Use     : Get Gallary image for batch
	Author  : Axay Shah
	Date    : 04 Dec,2020
	*/
	public static function GetBatchAllMedia($batchId){
		$result     = array();
		$batchData  = self::where("batch_id",$batchId)->get();
		if($batchData){
			foreach($batchData as $batch){
				$media = MediaMaster::find($batch->image_id);
				if($media){
					$image                  = array();
					$imageName              = str_replace('images/images/', 'images/', $media->original_name);
					$image['image']         = $imageName;
					$image['thumbImage']    = $imageName;
					$result[]               = $image;
				}
			}
		}
		return $result;
	}
}
