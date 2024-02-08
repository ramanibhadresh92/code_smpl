<?php
namespace App\Traits;
use Illuminate\Http\Request;
use File;
use Image;
use App\Models\MediaMaster;
trait StoreImage {
 
    /**
     * Use      : Does very basic image validity checking and stores it. Redirects back if somethings wrong.
     * @Notice  : This is not an alternative to the model validation for this field.
     * Author   : Axay Shah
     * Date     : 10 Oct,2018
     */
    public function verifyAndStoreImage(Request $request, $fieldName = 'image', $fullPath = '',$companyId='0',$moduleName = 'demo',$cityId = '0',$mediaId = 0) {
        try{
            ($fullPath == '') ? $fullPath : $fullPath."/";
            $partialPath    = $fullPath."/".$companyId.'/'.$cityId."/".$moduleName;

            if( $request->hasFile( $fieldName ) ) {
                
                $file = $request->file($fieldName);
                if(!is_dir(public_path(PATH_IMAGE.'/').$partialPath)) {
                    mkdir(public_path(PATH_IMAGE.'/').$partialPath,0777,true);
                }
                if($mediaId != '0' || $mediaId != 0){
                    $this->unlinkImage($mediaId);
                }
                $orignalImg     = $fieldName."_".time().'.'.$file->getClientOriginalExtension();
                $imgName        = RESIZE_PRIFIX.$orignalImg;
                $img            = Image::make($file->getRealPath());
                $img->resize(RESIZE_HIGHT, RESIZE_WIDTH, function ($constraint) {
                    $constraint->aspectRatio();
                })->save(public_path(PATH_IMAGE.'/').$partialPath.'/'.$imgName);
                /*move orignal file*/
                $file->move(public_path(PATH_IMAGE.'/').$partialPath.'/', $orignalImg);
                /*move orignal file*/
                $mediaMaster = new MediaMaster();
                $mediaMaster->company_id 	= $companyId;
                $mediaMaster->city_id 	    = $cityId;
                $mediaMaster->original_name = ($request->hasFile( $fieldName )) ? $orignalImg  : "";
                $mediaMaster->server_name   = $imgName;
                $mediaMaster->image_path    = $partialPath;
                $mediaMaster->save();
                return $mediaMaster;
            }
        }catch(\Exception $e){
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }    
        return "";
    }

    public function unlinkImage($id){
        $image = MediaMaster::find($id);
        if($image){
            if (file_exists(public_path(PATH_IMAGE.'/').$image->getOriginal()['image_path']."/".$image->getOriginal()['original_name'])) {
                unlink(public_path(PATH_IMAGE.'/').$image->getOriginal()['image_path']."/".$image->getOriginal()['original_name']);
            }
            if (file_exists(public_path(PATH_IMAGE.'/').$image->getOriginal()['image_path']."/".$image->getOriginal()['server_name'])) {
                unlink(public_path(PATH_IMAGE.'/').$image->getOriginal()['image_path']."/".$image->getOriginal()['server_name']);
            }
            $image->delete();
        }
    }
    
    public function uploadDoc(Request $request, $fieldName = 'doc', $fullPath = '',$companyId='0',$moduleName = 'demo',$cityId = '0',$mediaId = 0) {
        try{
            ($fullPath == '') ? $fullPath : $fullPath;
            $partialPath    = $fullPath."/".$companyId.'/'.$cityId."/".$moduleName;
            
            if( $request->hasFile( $fieldName ) ) {
                $file = $request->file($fieldName);
                if(!is_dir(public_path(PATH_IMAGE.'/').$partialPath)) {
                    mkdir(public_path(PATH_IMAGE.'/').$partialPath,0777,true);
                }
                if($mediaId != '0' || $mediaId != 0){
                    $this->unlinkImage($mediaId);
                }
                $orignalImg     = $fieldName."_".time().'.'.$file->getClientOriginalExtension();
                /*move orignal file*/
                $file->move(public_path(PATH_IMAGE.'/').$partialPath.'/', $orignalImg);
                /*move orignal file*/
                $mediaMaster = new MediaMaster();
                $mediaMaster->company_id 	= $companyId;
                $mediaMaster->city_id 	    = $cityId;
                $mediaMaster->original_name = ($request->hasFile( $fieldName )) ? $orignalImg  : "";
                $mediaMaster->server_name   = ($request->hasFile( $fieldName )) ? $orignalImg  : "";
                $mediaMaster->image_path    = $partialPath;
                $mediaMaster->save();
                return $mediaMaster;
            }
            return "";
        }catch(\Exception $e){
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }    
    }
    /*
    Use     : Copy Document Function Use in ScopingMaster
    Date    : 27 Feb 2023
    Author  : Hardyesh Gupta
    */
    public function copyDocument($customer_id = 0,$filename = 0,$fieldName = 'doc', $fullPath = '',$companyId='0',$moduleName = 'demo',$cityId = '0',$mediaId = 0) {
        try{
            ($fullPath == '') ? $fullPath : $fullPath;
            $partialPath        = $fullPath."/".$companyId.'/'.$cityId."/".$moduleName;
            if($filename != 0){ 
                $mediaMaster    = MediaMaster::find($filename);
                if($mediaMaster){
                    $sourceimage_path           =  $mediaMaster->image_path;
                    $mediaMaster->image_path    = $partialPath;
                    if($mediaMaster->save()){
                        $sourceFilePath = 'public/'.PATH_IMAGE.'/'.$sourceimage_path.'/'.$mediaMaster->getOriginal('server_name');
                        if(!is_dir(public_path(PATH_IMAGE.'/').$partialPath)) {
                            mkdir(public_path(PATH_IMAGE.'/').$partialPath,0777,true);
                        } 
                        $destinationPath = 'public/'.PATH_IMAGE.'/'.$partialPath.'/'.$mediaMaster->getOriginal('server_name');
                        $success_copyfile = \File::copy(base_path($sourceFilePath),base_path($destinationPath));
                    }
                    return SUCCESS;  
                }   
            }
            return "";
        }catch(\Exception $e){
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }    
    }
   
    
 
}