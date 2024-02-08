<?php
namespace App\Traits;
use Illuminate\Http\Request;
use File;
use Image;
use App\Models\MediaMaster;
trait uploads {
 
    /**
     * Use      : Does very basic image validity checking and stores it. Redirects back if somethings wrong.
     * @Notice  : This is not an alternative to the model validation for this field.
     * Author   : Axay Shah
     * Date     : 10 Oct,2018
     */

    public function createDirectory($path){
    	if(!is_dir($path)) {
        	mkdir($path,0777,true);
        }
    }
    public function checkMediaId($mediaId){
    	if($mediaId != '0' || $mediaId != 0){
            $this->unlinkImage($mediaId);
        }
    }
    public function setOrignalImgName($fieldName,$file){
        return  $fieldName."_".time().'.'.$file->getClientOriginalExtension();
    }
    public function uploadDocument(Request $request, $fieldName = 'documents', $fullPath = '',$companyId='0',$moduleName = 'demo',$cityId = '0',$mediaId = 0) {
        try{
        	$imageData = array();
            ($fullPath == '') ? $fullPath : $fullPath;
            $partialPath    = $fullPath."/".$companyId.'/'.$moduleName;
            $fullPath 	    =  public_path(PATH_IMAGE.'/').$partialPath;
            if($request->hasFile( $fieldName )) {
              
                /*create directory if not exits */
                $this->createDirectory($fullPath);
                $this->checkMediaId($mediaId);
                $file 		        = $request->file($fieldName);
                $orignalImg         = $this->setOrignalImgName($fieldName,$file);
                // /*move orignal file*/
                $file->move($fullPath.'/', $orignalImg);
                $imageData['original_name']	= $orignalImg;
                $imageData['server_name'] 	= $orignalImg;
                $imageData['image_path'] 	= $partialPath;
            }
            return (object)$imageData;
        }catch(\Exception $e){
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e,"data"=>""]);
        }     
    }

    public function unlinkImage($id){
        $image = MediaMaster::find($id);
        if($image){
            if (file_exists(public_path(PATH_IMAGE.'/').$image->image_path."/".$image->original_name)) {
                unlink(public_path(PATH_IMAGE.'/').$image->image_path."/".$image->original_name);
            }
            $image->delete();
        }
    }
 
}