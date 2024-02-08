<?php

namespace App\Models;
use Illuminate\Http\Request;
use File;
use Image;
use App\Models\MediaMaster;
use Illuminate\Database\Eloquent\Model;

class UploadImage extends Model
{
	/*Upload Image comman function */
	public static function uploadImage($request,$folderName,$filedName = 'image',$companyId,$cityId,$mediaId = 0)
	{
		try{
			$fullPath       = public_path(PATH_IMAGE.'/'.PATH_COMPANY."/".$companyId."/".$cityId);
			$partialPath    = PATH_COMPANY."/".$companyId."/".$cityId."/".$folderName;
			if( $request->hasFile($filedName) ) {
				$file               = $request->file($filedName);
				$pathWithFolderName = $fullPath."/".$folderName;

				if(!is_dir($pathWithFolderName)) {
					mkdir($pathWithFolderName,0777,true);
				}
				if($mediaId != '0' || $mediaId != 0){
					$this->unlinkImage($mediaId);
				}
				$orignalImg     = $filedName."_".rand()."_".time().'.'.$file->getClientOriginalExtension();
				$imgName        = RESIZE_PRIFIX.$orignalImg;
				$img            = Image::make($file->getRealPath());
				$img->resize(RESIZE_HIGHT, RESIZE_WIDTH, function ($constraint) {
					$constraint->aspectRatio();
				})->save($pathWithFolderName.'/'.$imgName);
				/*move orignal file*/
				$file->move($pathWithFolderName.'/', $orignalImg);

				$mediaMaster = new MediaMaster();
				$mediaMaster->company_id 	= $companyId;
				$mediaMaster->city_id 	    = $cityId;
				$mediaMaster->original_name = ($request->hasFile( $filedName )) ? $orignalImg  : "";
				$mediaMaster->server_name   = $imgName;
				$mediaMaster->image_path    = $partialPath;
				$mediaMaster->save();
				return $mediaMaster;
			}
		}catch(\Exeption $e){
			Log::error("ERROR :".print_r(json_encode($e)));
		}
	}
}
