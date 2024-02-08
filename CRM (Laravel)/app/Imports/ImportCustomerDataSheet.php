<?php

namespace App\Imports;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\CustomerMaster;
use App\Models\CompanyParameter;
use App\Models\Parameter;
use App\Models\LocationMaster;
use App\Models\PriceGroupMaster;
use App\Models\CountryMaster;
use App\Models\MasterCodes;
use App\Models\RequestApproval;
use App\Models\CompanyPriceGroupMaster;
class ImportCustomerDataSheet implements ToModel, WithStartRow
{
	/**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

	/**
	* @param array $row
	*
	* @return \Illuminate\Database\Eloquent\Model|null
	*/
	public function model(array $row)
	{
    	$ctype_data 				= @$row[0]; 
    	$salutation_data 			= @$row[1]; 
    	$first_name 				= @$row[2]; 
    	$address1 					= @$row[3];     	
    	$city_data 					= @$row[4];
    	$state_data 				= @$row[5];
    	$zipcode 					= @$row[6];
    	$country_data 				= @$row[7];
    	$type_of_collection_data 	= @$row[8];
    	$collection_site_data 		= @$row[9];
    	$cust_group_data 			= @$row[10];
    	$cust_status_data 			= @$row[11];
    	$price_group_data 			= @$row[12];
    	$para_payment_mode_id_data 	= @$row[13];  
    	$flag 						= true;
    	$cust_group 				= "";
    	$ctype 						= 0;
    	$salutation 				= 0;
    	$type_of_collection 		= "";
    	$collection_site 			= "";
    	$price_group 				= "";
    	\Log::info("########### CITY DATA NEED TO CHECK IN IMPORT EXCEL".$city_data." for customer ".$first_name);
    	$cityId 	= 0;
    	$stateID 	= 0;
    	$country 	= 0;
    	if(!empty($city_data)){
    		$cityId 			= LocationMaster::where("city",$city_data)->value('location_id');	
    		$stateID 			= LocationMaster::where("location_id",$cityId)->value('state_id');
    		$country 			= CountryMaster::where("country_name",$country_data)->value('country_id');
    	}
    	if($cityId > 0){
    		if(!empty($ctype_data)){
    			$ctype 				= Parameter::where("para_value",$ctype_data)->value('para_id');	
	    	}
	    	if(!empty($salutation_data)){
	    		$salutation 		= Parameter::where("para_value",$salutation_data)->where("para_parent_id",PARA_SALUTION)->value('para_id');
	    	}
	    	$PARA_TYPE_OF_COLLECTION_PARENT = 0;
	    	if(!empty($type_of_collection_data)){
	    		$PARA_TYPE_OF_COLLECTION_PARENT = CompanyParameter::where("para_type",PARA_TYPE_OF_COLLECTION)->where("city_id",$cityId)->value("para_parent_id");
	    		$type_of_collection = CompanyParameter::where("para_value",$type_of_collection_data)->where("para_parent_id",$PARA_TYPE_OF_COLLECTION_PARENT)->value('para_id');
	    	}
	    	$collection_site_parent = 0;
	    	if(!empty($collection_site_data)){
	    		$collection_site_parent = CompanyParameter::where("para_type",PARA_COLLECTION_SITE)->where("city_id",$cityId)->value("para_parent_id");
	    		$collection_site = CompanyParameter::where("para_value",$collection_site_data)->where("para_parent_id",$collection_site_parent)->value('para_id');
			}
			$cust_group_parent = 0;
	    	if(!empty($cust_group_data)){
	    		$cust_group_parent = CompanyParameter::where("para_type",PARA_CUSTOMER_GROUP)->where("city_id",$cityId)->value("para_parent_id");
	    		$cust_group 		= CompanyParameter::where("para_value",$cust_group_data)->where("para_parent_id",$cust_group_parent)->value('para_id');
		  	}
	    	if(!empty($cust_status_data)){
	    		
	    		$para_status_id 	= CUSTOMER_STATUS_PENDING;	
	    	}	
	    	if(!empty($price_group_data)){
	    		$price_group 		= CompanyPriceGroupMaster::where("group_value",$price_group_data)->where("is_default",'Y')->where("city_id",$cityId)->value('id');
	    	}
	    	if(!empty($para_payment_mode_id_data)){
	    		$para_payment_mode_id = Parameter::where("para_value",$para_payment_mode_id_data)->where("para_parent_id",PARA_CUSTOMER_PAYMENT_MODE)->value('para_id');		
	    	}	
	    	if(!empty($ctype_data) && !empty($city_data)){
				$lastCusCode 			= MasterCodes::getMasterCode(MASTER_CODE_CUSTOMER);
				$CustomerObject 		= new CustomerMaster;
				$newCreatedCode         = $lastCusCode->code_value + 1;
				$newCode                = $lastCusCode->prefix.''.$newCreatedCode;
				$CustomerObject->city   = $cityId;
				$stateID = 0;
				if($cityId > 0){
					$stateID = LocationMaster::where("location_id",$cityId)->value('state_id');
				}
				$CustomerObject->code                  	= $newCode; 
				$CustomerObject->ctype            	 	= $ctype; 
				$CustomerObject->salutation            	= $salutation; 
				$CustomerObject->first_name            	= $first_name; 
				$CustomerObject->address1              	= $address1; 
				$CustomerObject->city                  	= $cityId; 
				$CustomerObject->state                 	= $stateID; 
				$CustomerObject->country               	= $country; 
				$CustomerObject->zipcode               	= $zipcode; 
				$CustomerObject->para_status_id        	= $para_status_id; 
				$CustomerObject->price_group           	= $price_group; 
				$CustomerObject->cust_group            	= $cust_group; 
				$CustomerObject->type_of_collection    	= $type_of_collection; 
				$CustomerObject->collection_site       	= $collection_site; 
				$CustomerObject->para_payment_mode_id  	= $para_payment_mode_id; 
				$CustomerObject->company_id  			= '1'; 
				$CustomerObject->created_at  			= date('Y-m-d H:i:s');
				if($CustomerObject->save()){
					MasterCodes::updateMasterCode(MASTER_CODE_CUSTOMER,$newCreatedCode);
					$customerReqApproval 	= RequestApproval::saveDataChangeRequest(FORM_CUSTOMER_ID,FILED_NAME_CUSTOMER,$CustomerObject->customer_id,$CustomerObject,$cityId);		
				}	
				return $CustomerObject;
			}
    	}
	}
}