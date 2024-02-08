<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmTransferMediaMaster extends Model
{
    protected   $table      =   'wm_transfer_media_master';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public      $timestamps =   true;

    public function GetBatchMediaImage(){
        return $this->belongsTo(MediaMaster::class,'image_id');
    }

    /*
    Use     : Get Transfer media list
    Author  : Axay Shah
    Date    : 12 Aug,2019
    */
    public static function getTransferMedia($transferId){
        $result     = array();
        $transferData  = self::where("transfer_id",$transferId)->get();
        if($transferData){
            foreach($transferData as $Transfer){
                $media = MediaMaster::find($Transfer->image_id);
                if($media){
                    $result[] = $media;
                }
            }
        }
        return $result;
    }
}
