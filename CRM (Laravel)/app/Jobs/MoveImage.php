<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\CompanyCategoryMaster;
use App\Models\MediaMaster;
use App\Models\CollectionTags;
class MoveImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $details;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data       = $this->details;
        
        if(isset($data['moduleName']) && !empty($data['moduleName'])){
            
            switch($data['moduleName']){
                case  CATEGORY_MOVE :
                    $path       = '';
                    $category   =   CompanyCategoryMaster::where('company_id',$data['company_id'])
                                    // ->where('city_id',$data['city_id'])
                                    ->get();
                    foreach ($category as $value) {
                        $path       = URL_HTTP_COMPANY.$value->company_id."/".$value->city_id."/".COMPANY_CATEGORY;
                        if(!is_dir(public_path().$path)) {
                            mkdir(public_path().$path,0777,true);
                        }
                        if(!empty($value->select_img)){
                            $newPath = public_path().$path."/".$value->select_img;
                            $oldPath = public_path().MASTER_PATH."/".MASTER_CATEGORY."/".$value->ref_category_id."/".$value->select_img;
                            $media = $this->insertImage($value->company_id,$value->city_id,$value->select_img,$value->select_img,$path,$oldPath,$newPath);
                            if($media > 0){
                                CompanyCategoryMaster::where('id',$value->id)->update(["select_img"=>$media]);
                            }
                        }
                        if(!empty($value->normal_img)){
                            $newPath = public_path().$path."/".$value->normal_img;
                            $oldPath = public_path().MASTER_PATH."/".MASTER_CATEGORY."/".$value->ref_category_id."/".$value->normal_img;
                            $media =  $this->insertImage($value->company_id,$value->city_id,$value->normal_img,$value->normal_img,$path,$oldPath,$newPath);
                            if($media > 0){
                               CompanyCategoryMaster::where('id',$value->id)->update(["normal_img"=>$media]);
                            }
                        }
                    }
                    break;
                case  COLLECTION_TAG_MOVE :
                    $path   = '';
                    $tags   = CollectionTags::where('company_id',$data['company_id'])->where('city_id',$data['city_id'])->get();
                    $path       = URL_HTTP_COMPANY.$data['company_id']."/".$data['city_id']."/".COMPANY_COLLECTION_TAG;
                    if(!is_dir(public_path().$path)) {
                        mkdir(public_path().$path,0777,true);
                    }
                    if($tags){
                        foreach ($tags as $value) {
                            if(!empty($value->select_img)){
                                $selectImg = $value->getOriginal('select_img');
                                $newPath = public_path().$path."/".$selectImg;
                                $oldPath = public_path().MASTER_PATH."/".MASTER_COLLECTION_TAG."/".$value->ref_tag_id."/".$selectImg;
                                $media = $this->insertImage($value->company_id,$value->city_id,$selectImg,$selectImg,$path,$oldPath,$newPath);
                                if($media > 0){
                                    CollectionTags::where('tag_id',$value->tag_id)->update(["select_img"=>$media]);
                                }
                            }
                            if(!empty($value->normal_img)){
                                $normalImg = $value->getOriginal('normal_img');
                                $newPath = public_path().$path."/".$normalImg;
                                $oldPath = public_path().MASTER_PATH."/".MASTER_COLLECTION_TAG."/".$value->ref_tag_id."/".$normalImg;
                                $media =  $this->insertImage($value->company_id,$value->city_id,$normalImg,$normalImg,$path,$oldPath,$newPath);
                                if($media > 0){
                                    CollectionTags::where('tag_id',$value->tag_id)->update(["normal_img"=>$media]);
                                }
                            }
                        }
                    }
                break;
            }
        }
    }

    public function insertImage($company_id,$city_id,$normalImg = '',$selectImg = '',$path,$oldPath,$newPath){
        if(\File::exists($oldPath) && !empty($newPath)) {
            \File::copy($oldPath , $newPath);
            $mediaMaster                = new MediaMaster();
            $mediaMaster->company_id 	= $company_id;
            $mediaMaster->city_id 	    = $city_id;
            $mediaMaster->original_name = $normalImg;
            $mediaMaster->server_name   = $selectImg;
            $mediaMaster->image_path    = $path;
            if($mediaMaster->save()){
                return $mediaMaster->id;
            }else{
                return 0;
            }
        }
    }
}
