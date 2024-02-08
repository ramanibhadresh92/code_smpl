<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseLocationMaster;
use App\Models\LocationMaster;
use App\Models\VehicleMaster;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\MasterCodes;
class TransporterMaster extends Model implements Auditable
{
	protected 	$table 		=	'transporter_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts 		= [];
	
	/*
	Use 	:  Get Transporter List
	Author 	:  Axay Shah
	Date 	:  17 March 2021
	*/
	public static function TransporterDropDown($request){
		if(Auth()->user()->adminuserid == 1){
			return self::TransporterDropDownV2($request);
		}
		$result = self::select("id","name")->groupBy('name')->get();
		return $result;
	}


	public static function TransporterDropDownV2($request){
		$result = "";
		$self 		= (new static)->getTable();		
		if (isset($request->searchquery) && $request->searchquery) {
			$result = self::select("id","name");		
			$result->where(function ($q) use ($request,$self) {
				$q->where("$self.name", 'LIKE', '%' . DBVarConv($request->searchquery) . '%');
			});
			$result = $result->groupBy('id')->get();
		}else{
			$result = self::select("id","name")->groupBy('name')->get();	
		}
		return $result;
	}

	/*
	Use 	:  Get Transporter List from BAMS
	Author 	:  Axay Shah
	Date 	:  20 Sep 2021
	*/
	public static function GetTransporterListFromBAMS($request){
		
	}

	/*
	Use 	:  Store Transporter List
	Author 	: Axay Shah
	Date 	: 20 Octomber 2022
	*/
	public static function StoreTransporter($request){
		$NAME 			= (isset($request->name) && !empty($request->name)) ? ucwords(strtolower($request->name)) : "";
		$TRANSPOTER_ID 	= (isset($request->transporter_id) && !empty($request->transporter_id)) ? ucwords(strtolower($request->transporter_id)) : "";
		
		$TRANSPORTER_DATA = TransporterMaster::find($TRANSPOTER_ID);
 		######## TRANSPORTER CODE AUTO GENERATED###########
		$newCode 	 	= "";
		$newCreatedCode = "";
		$lastCusCode 	= MasterCodes::getMasterCode(MASTER_CODE_TRANSPORTER);
		if($lastCusCode){
			$newCreatedCode  = $lastCusCode->code_value + 1;
			$newCode         = $lastCusCode->prefix.''.LeadingZero($newCreatedCode);
		}
		######## TRANSPORTER CODE AUTO GENERATED###########
 		if(!$TRANSPORTER_DATA){
 			$TRANSPORTER_DATA = new TransporterMaster();
 			$TRANSPORTER_DATA->name = $NAME;
 			$TRANSPORTER_DATA->code = $newCode;
 			if($TRANSPORTER_DATA->save()){
 				$TRANSPOTER_ID = $TRANSPORTER_DATA->id;
 				if(!empty($newCreatedCode)){
					MasterCodes::updateMasterCode(MASTER_CODE_TRANSPORTER,$newCreatedCode);
				}
 			}
		}	
	}
	
}