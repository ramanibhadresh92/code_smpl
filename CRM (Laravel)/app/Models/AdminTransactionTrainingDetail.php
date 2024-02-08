<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Log;
use App\Models\AdminTransactionGroups;
use App\Models\AdminUserRights;
use App\Models\AdminTransaction;
use App\Models\AdminTransactionTrainingDetail;
class AdminTransactionTrainingDetail extends Model
{
    //
    
    protected 	$table 		=	'admin_transaction_training_detail';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    // public      $timestamps = false;
    
   /* 
    *   Use     :   List For Admin Transaction Menu For Training Detail
    *   Author  :   Hardyesh Gupta
    *   Date    :   22 Sep,2023
    */
    public static function AdminTransactionTrainingMenuList($request){
        $result = "";
        $result = AdminTransaction::where('insubmenu','Y')->where('showtrnflg','Y')->get();
        return $result;
    } 

    /* 
    *   Use     :   Add/Insert Training Detail
    *   Author  :   Hardyesh Gupta
    *   Date    :   22 Sep,2023
    */
    public static function AddTrainingData($request,$path = "",$media_name =""){
        // $id               = (isset($request->id) && (!empty($request->id))?$request->id:0);
        $youtube_media       = (isset($request->youtube_media) && (!empty($request->youtube_media)) ? $request->youtube_media : "");
        $description         = (isset($request->description) && (!empty($request->description)) ? $request->description : "");
        $trnid               = (isset($request->trnid) && (!empty($request->trnid)) ? $request->trnid : 0);
        $youtube_media_id    = (isset($request->youtube_media_id) && (!empty($request->youtube_media_id)) ? $request->youtube_media_id : 0);
        $audio_media_id      = (isset($request->audio_media_id) && (!empty($request->audio_media_id)) ? $request->audio_media_id : 0);
        $created_by          = (\Auth::check()) ? Auth()->user()->adminuserid :  0;
        $data = false;
        if(!empty($youtube_media)){
            $media_path = $youtube_media;
            $training = self::updateOrCreate(
                ['trnid' => $trnid,'id' => $youtube_media_id],
                ['media_name' => '','media_flag' => 0,'media_path' => $media_path, 'trnid' => $trnid,'created_by' => $created_by]
            ); 
            $data = true;
        }
        if($request->hasfile('audio_media') && !empty($request->hasfile('audio_media'))) {
            $media_data= self::UploadTrainingFile($request);
            if(!empty($media_data)){
                $media_name = $media_data['media_name'];
                $path       = $media_data['path'];
                 $training = self::updateOrCreate(
                    ['trnid' => $trnid,'id' => $audio_media_id],
                    ['media_name' => $media_name, 'media_flag' => 1,'media_path' => $path, 'trnid' => $trnid,'created_by' => $created_by]
                ); 
                $data = true;   
            }
        }
        AdminTransaction::where('trnid',$trnid)->update(['description'=>$description]);
        return $data;     
    }

    /* 
    *   Use     :   Upload Training Media
    *   Author  :   Hardyesh Gupta
    *   Date    :   22 Sep,2023
    */
    public static function UploadTrainingFile($request){
        $media_flag = 1;
        $path = "/".PATH_TRAINING;
        $file = $request->file('audio_media');
        $Extention = $file->getClientOriginalExtension();
        if(!is_dir(public_path($path))) {
            mkdir(public_path($path),0777,true);
        }
        $media_name     = "training_".time().'.'.$file->getClientOriginalExtension();
        $file->move(public_path($path), $media_name);
        $media_path = $path;
        $responseArray = array(
            "media_name" => $media_name,
            "extention" => $Extention,
            "path"      => $path,
            "media_flag" => $media_flag,
        );
        return $responseArray;
    }

    /* 
    *   Use     :   List Training Media List
    *   Author  :   Hardyesh Gupta
    *   Date    :   22 Sep,2023
    */
    public static function AdminTransactionTrainingMediaList($request){
        $table      = (new static)->getTable();
        $AdminUser  = new AdminUser();
        $AT         = new AdminTransaction();
        $result     = "";
        $Sql    = self::select(
                        "$table.id as id",
                        "$table.media_name as media_name",
                        "$table.media_path as media_path",
                        "$table.media_flag as media_flag",
                        "$table.trnid as trnid",
                        "$table.status as status",
                        "$table.created_at as created_date",
                        "$table.updated_at as updated_date"
                        )
                        ->leftjoin($AT->getTable()." as AT","$table.trnid","=","AT.trnid");
        $result =   $Sql->get();
        if(!empty($result)){
            return $result; 
        }
        return $result;
    }

    /*
    Use     : Get Media Details By Training ID
    Author  : Hardyesh Gupta
    Date    :22-09-2023
    */
    public static function GetMediaByTrainingID($id){
        $data = "";
        $data = self::select("id",
                    "media_name",
                    "media_path",
                    "media_flag")
        ->where('trnid',$id)->where('media_flag',1)->get()->toArray();
        if(!empty($data)){
            foreach($data as $key => $value){
                // $media_url = public_path().$value['media_path']."/".$value['media_name'];   
                $media_url = url('').$value['media_path']."/".$value['media_name'];   
                $data[$key]['media_url'] = (!empty($value['media_name'])) ? $media_url : "";
            }
        }
        return $data;
    }

    /*
    Use     : Get Youtube Media Details By Training ID
    Author  : Hardyesh Gupta
    Date    : 22-09-2023
    */
    public static function GetYoutubeByTrainingID($id){
        $data = "";
        $data = self::select("id",
                    "media_name",
                    "media_path",
                    "media_flag")
        ->where('trnid',$id)->where('media_flag',0)->get()->toArray();
        return $data;
    }

    /* 
    *   Use     :   Add/Insert Training Detail
    *   Author  :   Hardyesh Gupta
    *   Date    :   22 Sep,2023
    */

    public static function AddTrainingData_option($request,$path = "",$media_name =""){
        // $id               = (isset($request->id) && (!empty($request->id))?$request->id:0);
        $youtube_media       = (isset($request->youtube_media) && (!empty($request->youtube_media)) ? $request->youtube_media : "");
        $description         = (isset($request->description) && (!empty($request->description)) ? $request->description : "");
        $trnid               = (isset($request->trnid) && (!empty($request->trnid)) ? $request->trnid : 0);

        $data = false;
        if(!empty($youtube_media)){
            $media_path = $youtube_media;
            $media_flag = 0;
            $insertId = self::AddTrainingMedia($request,$media_path,$media_name,$media_flag);  
            ($insertId > 0) ? $data = true: $data = false;
        }
        if($request->hasfile('audio_media') && !empty($request->hasfile('audio_media'))) {
            $media_data= self::UploadTrainingFile($request);
            if(!empty($media_data)){
                $media_name = $media_data['media_name'];
                $path       = $media_data['path'];
                $media_flag = $media_data['media_flag'];
                $insertId   = self::AddTrainingMedia($request,$path,$media_name,$media_flag);
                ($insertId > 0) ? $data = true: $data = false;  
            }
        }
        AdminTransaction::where('trnid',$trnid)->update(['description'=>$description]);
        return $data;     
    }

    /* 
    *   Use     :   Add/Insert Training Detail
    *   Author  :   Hardyesh Gupta
    *   Date    :   22 Sep,2023
    */
    public static function AddTrainingMedia($request,$media_path = "",$media_name ="",$media_flag = 0){
        $trnid                   = (isset($request->trnid) && (!empty($request->trnid)) ? $request->trnid : 0);
        $description             = (isset($request->description) && (!empty($request->description)) ? $request->description : "");
        $youtube_media_id        = (isset($request->youtube_media_id) && (!empty($request->youtube_media_id)) ? $request->youtube_media_id : 0);
        $audio_media_id          = (isset($request->audio_media_id) && (!empty($request->audio_media_id)) ? $request->audio_media_id : 0);
        $created_by              = (\Auth::check()) ? Auth()->user()->adminuserid :  0;
        $id = 0;
        $data = false;
        if(!empty($youtube_media_id )){
            $training                = self::find($youtube_media_id);
            $training->updated_at    = date('Y-m-d H:i:s');
        }else{
            $training                = new self;    
        }
        if(!empty($audio_media_id)){
            $training                = self::find($audio_media_id);
            $training->updated_at    = date('Y-m-d H:i:s');
        }else{
            $training                = new self;    
        }
        $training->media_name    = $media_name;
        $training->media_path    = $media_path;
        $training->media_flag    = $media_flag;
        $training->trnid         = $trnid;
        $training->created_by    = $created_by;
        $training->created_at    = date('Y-m-d H:i:s');
        if($training->save()){
            $id = $training->id; 
            $data = true;     
        }
        return $id;
    }
    


}
