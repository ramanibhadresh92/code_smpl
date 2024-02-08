<?php
use Illuminate\Http\Request;
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\uploads;
use App\Models\MediaMaster;
use DB;
use Illuminate\Http\UploadedFile;
use Image,File;
use Log;
class VehicleDocument extends Model
{
    use uploads;
    protected 	$table 		=	'vehicle_documents';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public      $timestamps =   true;

    public function getDocumentFileAttribute($value)
    {
        if(!empty($value)){
            $find = MediaMaster::find($value);
            if($find){
                $value =   $find->server_name;
            }
        }
        return $value;
    }
    
    /*
    Use     :  Add vehicle documents
    Author  :  Axay shah
    Date    :  25 Oct,2018 
    */
    public static function addVehicleDocument($request,$no = 0){
        try{
            $document_file  = "";
            $vehicleDoc     = new self();
            $cityId                    = (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
            $vehicleDoc->vehicle_id    = (isset($request->vehicle_id)  && !empty($request->vehicle_id)) ? $request->vehicle_id :0;
            $vehicleDoc->document_type = (isset($request->document_type)  && !empty($request->document_type)) ? $request->document_type : " ";
            $vehicleDoc->document_name = (isset($request->document_name)  && !empty($request->document_name)) ? $request->document_name : " ";
            $vehicleDoc->document_note = (isset($request->document_note)  && !empty($request->document_note)) ? $request->document_note : " ";
            if($request->hasfile('document_file')) {
                $document = $vehicleDoc->uploadDocument($request,'document_file',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_VEHICLE,$cityId);
                if(!empty($document)){
                    $mediaMaster = MediaMaster::add($document);
                    if(count($mediaMaster)>0){
                        $document_file = $mediaMaster->id;
                    }
                }
            }
            if($request->hasfile('doc_'.$no)) {
                $document = $vehicleDoc->uploadDocument($request,'doc_'.$no,PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_VEHICLE,$cityId);
                if(!empty($document)){
                    $mediaMaster = MediaMaster::add($document);
                    if(!empty($mediaMaster)){
                        $document_file = $mediaMaster->id;
                    }
                }
            }else{
                $document_file = self::where("vehicle_id",$request->clone_from)->where("document_type",$request->document_type)->value("document_file");
            }
            $vehicleDoc->document_file = $document_file;
            $vehicleDoc->save();
            return $vehicleDoc;
        }catch(\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
    }

    public static function updateVehicleDocument($request,$no = 0){
		try{
            
                   
            $docFile        = 0;
            $vehicleDoc     = self::find($request->id);
            if($vehicleDoc){
                $cityId                    = (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
                $vehicleDoc->vehicle_id    = (isset($request->vehicle_id)  && !empty($request->vehicle_id)) ? $request->vehicle_id : $vehicleDoc->vehicle_id;
                $vehicleDoc->document_type = (isset($request->document_type)  && !empty($request->document_type)) ? $request->document_type : $vehicleDoc->document_type;
                $vehicleDoc->document_name = (isset($request->document_name)  && !empty($request->document_name)) ? $request->document_name : $vehicleDoc->document_name;
                $vehicleDoc->document_note = (isset($request->document_note)  && !empty($request->document_note)) ? $request->document_note : $vehicleDoc->document_note;
                $vehicleDoc->document_change = (isset($request->document_change)  && !empty($request->document_change)) ? $request->document_change :"";

                if(isset($request->document_change) && !empty($request->document_change)){
                    $user = DB::table('vehicle_documents')->where("id",$request->document_change)->first();
                    if($user){
                        $docFile = $user->document_file;       
                    }
                }
                if($request->hasfile('doc_'.$no)) {
                    $document = $vehicleDoc->uploadDocument($request,'doc_'.$no,PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_VEHICLE,$cityId,$vehicleDoc->document_file);
                    if(!empty($document)){
                        $mediaMaster = MediaMaster::add($document);
                        if(!empty($mediaMaster)){
                            $document_file = (isset($mediaMaster->id)  && !empty($mediaMaster->id)) ? $mediaMaster->id : $vehicleDoc->document_file;
                        }
                    }
                }

                if(isset($document_file)  && !empty($document_file)){
                    $vehicleDoc->document_file = $document_file;
                    if(!empty($docFile)){

                        $update = self::where("document_file",$docFile)->whereIn("document_type",[OWNER_AADHAR_CARD,OWNER_PAN_CARD,OWNER_ELECTION_CARD])->where("vehicle_id","!=",$vehicleDoc->vehicle_id)->update(["document_file"=>$vehicleDoc->document_file]);
                       $docUpdate = self::where("id",$vehicleDoc->id)->update(["document_file"=>$document_file]);
                        
                    }
                }
                $vehicleDoc->save();
				return $vehicleDoc;
            }
            
        }catch(\Exception $e) {
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
    }
    /*
    Use     : Get Vehicle Document
    Author  : Axay Shah
    Date    : 03 December,2020
    */
    public static function GetVehicleDocumentImages($vehicleID=0){
        $result =  array();
        $data = self::where("vehicle_id",$vehicleID)->get();
        if(!empty($data)){
            foreach($data as $raw){
                $image = array();
                $image['image'] = $raw->document_file;
                $image['thumbImage'] = $raw->document_file;

                $result[] = $image;
            }
        }
        return $result;
    }

    /*
    Use     :  Add vehicle documents
    Author  :  Axay shah
    Date    :  25 Oct,2018 
    */
    public static function UpdateDocFromDispatch($request,$no = 0){
        try{
           
            $document_file             = "";
            $vehicleDoc                = new self();
            $cityId                    = (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
            $vehicleDoc->vehicle_id    = (isset($request->vehicle_id)  && !empty($request->vehicle_id)) ? $request->vehicle_id :0;
            $vehicleDoc->document_type = (isset($request->document_type)  && !empty($request->document_type)) ? $request->document_type : " ";
            $vehicleDoc->document_name = (isset($request->document_name)  && !empty($request->document_name)) ? $request->document_name : " ";
            $vehicleDoc->document_note = (isset($request->document_note)  && !empty($request->document_note)) ? $request->document_note : " ";
            if($request->hasfile('rc_book')) {
                $document = $vehicleDoc->uploadDocument($request,'rc_book',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_VEHICLE,$cityId);
                if(!empty($document)){
                    $mediaMaster = MediaMaster::add($document);
                    if(!empty($mediaMaster)){
                        $document_file = $mediaMaster->id;
                    }
                }
                $vehicleDoc->document_file = $document_file;
            }
            $vehicleDoc->save();
            return $vehicleDoc;
        }catch(\Exception $e) {
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
    }
}
