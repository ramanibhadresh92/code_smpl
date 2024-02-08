<?php

namespace App\Models;
use Illuminate\Support\Facades\Route;
use http\Env\Request;
use http\Env\Response;
use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use App\Models\VehicleMaster;
use App\Models\ClientMaster;
use App\Models\LocationMaster;
use App\Models\TransporterMaster;
use PDF;
use DB;

class WmTransportationDeclarationMaster extends Model
{
    protected   $table      =   'wm_transportation_declaration_master';   
    protected   $primaryKey =   'id'; 
    protected   $guarded    =   ['id'];
    public      $timestamps =   true;

    /*
	Use     : Trasportation Master List Show API
	Author  : Hardyesh Gupta
	Date 	: 10 feb,2023
	*/
	public static function getTransportationMasterList($request)
    {
    	$self 			= (new static)->getTable();    	
        $sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
     	$vehicle_number 	= $request->has('params.vehicle_number') && !empty($request->input('params.vehicle_number')) ? $request->input('params.vehicle_number') : '';
     	$client     		= $request->has('params.client') && !empty($request->input('params.client')) ? $request->input('params.client') : '';
     	$source     		= $request->has('params.source') && !empty($request->input('params.source')) ? $request->input('params.source') : '';
     	$destination     	= $request->has('params.destination') && !empty($request->input('params.destination')) ? $request->input('params.destination') : '';
     	$transporter     	= $request->has('params.transporter') && !empty($request->input('params.transporter')) ? $request->input('params.transporter') : '';
     	$type_of_transportation = $request->has('params.type_of_transportation') && !empty($request->input('params.type_of_transportation')) ? $request->input('params.type_of_transportation') : '';
     	$createdAt      	= $request->has('params.created_from') && !empty($request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input('params.created_from'))) : date("Y-m-d");
        $createdTo    		= $request->has('params.created_to') && !empty($request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input('params.created_to'))) : date("Y-m-d");

        $data = self::select(					
					\DB::raw("$self.id as id"),
					\DB::raw("$self.vehicle_number as vehicle_number"),
					\DB::raw("$self.client as client"),
					\DB::raw("$self.source as source"),
					\DB::raw("$self.destination as destination"),
					\DB::raw("$self.type_of_transportation as type_of_transportation"),
					\DB::raw("$self.transporter as transporter"),
					\DB::raw("$self.waste_type as waste_type"),
					\DB::raw("DATE_FORMAT($self.created_at,'%d-%m-%Y') as created_from"),
					\DB::raw("DATE_FORMAT($self.created_at,'%d-%m-%Y') as created_to"));

        if(!empty($vehicle_number)) {
            $data->where("$self.vehicle_number","like","%".$vehicle_number."%");
        }
        if(!empty($client)) {
            $data->where("$self.client",$client);
        }
        if(!empty($vehicle_id)) {
            $data->where("$self.vehicle_id",$vehicle_id);
        }
        if(!empty($source)) {
            $data->where("$self.source",$source);
        }
        if(!empty($destination)) {
            $data->where("$self.destination",$destination);
        }
        if(!empty($transporter)) {
            $data->where("$self.transporter",$transporter);
        }
        if(!empty($type_of_transportation)) {
            $data->where("$self.type_of_transportation",$type_of_transportation);
        }
        if(!empty($waste_type)) {
            $data->where("$self.waste_type",$type_of_transportation);
        }
        if(!empty($createdAt) && !empty($createdTo)) {
            $data->whereBetween("$self.created_at",array($createdAt." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME));
        } else if(!empty($createdAt)) {
            $data->whereBetween("$self.created_at",array($createdAt." ".GLOBAL_START_TIME,$createdAt." ".GLOBAL_END_TIME));
        } else if(!empty($createdTo)) {
            $data->whereBetween("$self.created_at",array($createdTo." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME));
        }
        $result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);

        if(!empty($result)){
        	$toArray = $result->toArray();
  			foreach($toArray['result'] as $key => $value){
  				$id = $toArray['result'][$key]['id']; 
  				$file_url 					= url('/')."/generateDeclarationPdf/".encode($id);
  				$result[$key]['pdf_url'] 	= $file_url;
			}
        }
  		return $result;
    }

    public static function createTransportationDeclarationMaster($request)
    {
		$insertId = 0;
		$vehicle_number 			= (isset($request->vehicle_number) && !empty($request->vehicle_number)) ? strtoupper($request->vehicle_number) : '';		
		$client 					= (isset($request->client) && !empty($request->client)) ? ucwords(strtolower($request->client)) : '';
		$source 					= (isset($request->source) && !empty($request->source)) ? ucwords(strtolower($request->source)) : '';
		$destination 				= (isset($request->destination) && !empty($request->destination)) ? $request->destination : '';
		$type_of_transportation 	= (isset($request->type_of_transportation) && !empty($request->type_of_transportation)) ? ucwords(strtolower($request->type_of_transportation)) : '';
		$transporter 				= (isset($request->transporter) && !empty($request->transporter)) ? ucwords(strtolower($request->transporter)) : '';
		$waste_type 				= (isset($request->waste_type) && !empty($request->waste_type)) ? ucwords(strtolower($request->waste_type)) : '';

		$data = new self();
		$data->vehicle_number 	= $vehicle_number;
		$data->client 			= $client;
		$data->source 			= $source;
		$data->destination 		= $destination;
		$data->type_of_transportation = $type_of_transportation;
		$data->transporter 		= $transporter;
		$data->waste_type 		= $waste_type;
		if($data->save())
		{
			$insertId = $data->id;
		}
		return $insertId;	
    }

    public static function generateBillTDeclarationPDF($id = 0)
	{
		$BillDeclarationData =  self::find($id);
		if($BillDeclarationData){

			$DATE 				= $BillDeclarationData->created_at->format('d-M-Y');
			$VehicleNo 			= $BillDeclarationData->vehicle_number;
			$WasteType  		= $BillDeclarationData->waste_type;
			$TranspoterName 	= $BillDeclarationData->transporter;
			$SOURCE  			= $BillDeclarationData->source;
			$CLIENT_NAME 		= $BillDeclarationData->client;
			$DESTINATION  		= $BillDeclarationData->destination;
			$TYPEOFTRANS 		= $BillDeclarationData->type_of_transportation;
			$declaration_date 	= $BillDeclarationData->declaration_date;
			$FILENAME   		= "Declaration-Sample.pdf";
			$HeaderImage 		= public_path("assets/pdf/Header.png");
			$HeaderImageType 	= pathinfo($HeaderImage, PATHINFO_EXTENSION);
			$imgData			= file_get_contents($HeaderImage);
			$HeaderImage 		= 'data:image/' . $HeaderImageType . ';base64,' . base64_encode($imgData);
			$FooterImage 		= public_path("assets/pdf/Footer.png");
			$FooterImageType 	= pathinfo($FooterImage, PATHINFO_EXTENSION);
			$imgData			= file_get_contents($FooterImage);
			$FooterImage 		= 'data:image/' . $FooterImageType . ';base64,' . base64_encode($imgData);
			$file_name 			= passencrypt($FILENAME);
	  		$file_url			= url("/generateDeclarationPdf/".$file_name);
			$params 			= array("Trans_Date"=>$declaration_date,
										"VehicleNo"=>$VehicleNo,
										"WasteType"=>$WasteType,
										"TranspoterName"=>$TranspoterName,
										"Source"=>$SOURCE,
										"Client_Name"=>$CLIENT_NAME,
										"Destination"=>$DESTINATION,
										"TYPEOFTRANS"=>$TYPEOFTRANS,
										"HeaderImage"=>$HeaderImage,
										"FooterImage"=>$FooterImage);
			return $params;
		}	
	}
}
