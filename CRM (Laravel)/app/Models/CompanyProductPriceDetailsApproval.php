<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CompanyProductPriceDetail;
use App\Models\CustomerMaster;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductQualityParameter;
use App\Models\CompanyCategoryMaster;
use App\Facades\LiveServices;
use DB;
class CompanyProductPriceDetailsApproval extends Model
{
    protected 	$table 		=	'company_product_price_details_approval';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    public 		$timestamps = 	true;

    /*
	Use 	: Add Price Group changes in approval
	Author 	: Axay shah
	Date 	: 18 June,2019
	*/
	public static function AddPriceGroupRequestApproval($request,$customerId=0,$trackId=0,$cityId=0,$detailsId = 0){
		try{
			$OldPrice 			= 0.00;
			$OldInert 			= 0.00;
			$OldFactoryPrice	= 0.00;
			$isDefault 			= "N";
			$PriceGroup = CompanyPriceGroupMaster::find($request->para_waste_type_id);
			if($PriceGroup){
				$isDefault = $PriceGroup->is_default;
			}
			$CheckProductExiets	= CompanyProductPriceDetail::where("para_waste_type_id",$request->para_waste_type_id)->where("product_id",$request->product_id)->first();
			if($CheckProductExiets){
				$OldInert 			= $CheckProductExiets->product_inert; 
				$OldPrice 			= $CheckProductExiets->price;
				$OldFactoryPrice 	= $CheckProductExiets->factory_price;
			}
			$priceDetail = new self();
			$priceDetail->customer_id        = $customerId;
			$priceDetail->city_id        	 = $cityId;
			$priceDetail->company_id         = Auth()->user()->company_id;
			$priceDetail->details_id         = $detailsId;
			$priceDetail->track_id 			 = $trackId;
			$priceDetail->product_id 	     = (isset($request->product_id) && !empty($request->product_id)) ? $request->product_id : 0;
			$priceDetail->para_waste_type_id = (isset($request->para_waste_type_id) && !empty($request->para_waste_type_id)) ? $request->para_waste_type_id  : 0;
			$priceDetail->new_product_inert  = (isset($request->product_inert) && !empty($request->product_inert)) ? $request->product_inert       : 0;
			$priceDetail->new_factory_price  = (isset($request->factory_price) && !empty($request->factory_price)) ? $request->factory_price : 0;
			$priceDetail->new_price          = (isset($request->price) && !empty($request->price)) ? $request->price : 0;
			$priceDetail->old_product_inert  = $OldInert;
			$priceDetail->is_default 		 = $isDefault;
			$priceDetail->old_factory_price  = $OldFactoryPrice;
			$priceDetail->old_price          = $OldPrice;
			$priceDetail->created_by         = Auth()->user()->adminuserid;
			$priceDetail->save();
       		return $priceDetail;
		}catch(\Exception $e) {
           	return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
	}

	/*
	Use 	: List price group changes record
	Author 	: Axay Shah
	Date 	: 18 June,2019
	*/

	public static function ListPriceGroupApproval($request){
		$table 				= (new static)->getTable();
		$CustomerMaster 	= new CustomerMaster();
		$AdminUser 			= new AdminUser();
		$LocationMaster		= new LocationMaster();
		$PriceGroupMaster 	= new CompanyPriceGroupMaster();
		$Location 			= $LocationMaster->getTable();
		$Admin 				= $AdminUser->getTable();
		$Customer 			= $CustomerMaster->getTable();
		$PriceGroup			= $PriceGroupMaster->getTable();
		$Today          	= date('Y-m-d');
		$cityId         	= GetBaseLocationCity();

		$sortBy         	= ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "created_at";
		$sortOrder      	= ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  	= !empty($request->input('size'))       ?   $request->input('size')         : 10;
		$pageNumber     	= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';

		$list 	= 	self::select("$table.customer_id",
					"$table.track_id",
					"$table.city_id",
					"$table.para_waste_type_id",
					"$table.approved_by",
					"$table.approved_date",
					"$table.created_by",
					"$table.created_at",
					"$Customer.company_id",
					"$PriceGroup.group_value",
					"$PriceGroup.group_type",
					DB::raw("(CASE $table.approve_status
						WHEN ".PRICE_GROUP_PENDING." THEN 'Pending'
						WHEN ".PRICE_GROUP_ACCEPT." THEN 'Approved'
						WHEN ".PRICE_GROUP_REJECT."  THEN 'Reject'
						END ) AS approve_status_name"),
					"$table.approve_status",
					DB::raw("$Location.city as city_name"),
					DB::raw("concat(U1.firstname,' ',U1.lastname) as approved_by_name"),
					DB::raw("concat(U2.firstname,' ',U2.lastname) as created_by_name"),
					DB::raw("CONCAT($Customer.first_name,' ',$Customer.last_name) as customer_name")
				)
				->LeftJOIN("$Customer","$table.customer_id","=","$Customer.customer_id")
				->LEFTJOIN("$PriceGroup","$table.para_waste_type_id","=","$PriceGroup.id")
				->LEFTJOIN("$Admin as U1","$table.approved_by","=","U1.adminuserid")
				->LEFTJOIN("$Admin as U2","$table.created_by","=","U2.adminuserid")
				->LEFTJOIN("$Location","$Customer.city","=","$Location.location_id");

				if($request->has("params.city_id") && !empty($request->input("params.city_id")))
				{
						$list->where("$table.city_id",$request->input("params.city_id"));
				}
				// else{
				// 	$list->whereIn("$table.city_id",$cityId);
				// }
				if($request->has("params.track_id") && !empty($request->input("params.track_id")))
				{
					$trackId = explode(",",$request->input("params.track_id"));
					$list->whereIn("$table.track_id",$trackId);
				}
				
				if($request->has("params.approve_status"))
				{
					if($request->input("params.approve_status") == 0){
						$list->where("$table.approve_status",PRICE_GROUP_PENDING);	
					}
					if($request->input("params.approve_status") == PRICE_GROUP_ACCEPT || $request->input("params.approve_status") == PRICE_GROUP_REJECT){
						$STATUS = $request->input("params.approve_status");
						$list->where("$table.approve_status",$STATUS);	
					}
				}

				if($request->has("params.group_value") && !empty($request->input("params.group_value")))
				{
					$list->where("$PriceGroup.group_value","like","%".$request->input('params.group_value')."%");
				}
				if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
				{
					$list->whereBetween("$table.created_at",array(date("Y-m-d H:i:s", strtotime($request->input('params.startDate')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('params.endDate')." ".GLOBAL_END_TIME))));
				}else if(!empty($request->input('params.startDate'))){
				   $datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
				   $list->whereBetween("$table.created_at",array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
				}else if(!empty($request->input('params.endDate'))){
				   $list->whereBetween("$table.created_at",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
				}
				if($request->has("params.customer_name") && !empty($request->input("params.customer_name")))
				{
					$list->where("$Customer.first_name","like","%".$request->input('params.customer_name')."%")->orWhere("$Customer.last_name","like","%".$request->input('params.customer_name')."%");
				}

				$list->where("$table.company_id",Auth()->user()->company_id);

				$list->groupBy("track_id")->orderBy($sortBy,$sortOrder);
				// LiveServices::toSqlWithBinding($list);
		$data   = $list->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		
		return $data;					

	}

	/*
	Use 	: Get record by track id
	Author 	: Axay Shah
	Date 	: 18 June,2019
	*/
	public static function getByTrackId($trackId){
		$data = array();
		if(!empty($trackId)){
			$table 				= (new static)->getTable();
			$ProductMaster 		= new CompanyProductMaster();
			$Product 			= $ProductMaster->getTable();
			$QualityMaster 		= new CompanyProductQualityParameter();
			$Quality 			= $QualityMaster->getTable();
			$CategoryMaster		= new CompanyCategoryMaster();
			$Category 			= $CategoryMaster->getTable();
			$AdminUser 			= new AdminUser();
			$Admin 				= $AdminUser->getTable();
			$data 				= self::select("$table.*",'Q.parameter_name',"$Product.name","C.category_name",DB::raw("CONCAT($Product.name,'-',Q.parameter_name) as product_name"))
			->leftjoin("$Product","$table.product_id","=","$Product.id")
	        ->leftjoin("$Quality as Q","$Product.id","=","Q.product_id")
	        ->leftjoin("$Category as C","$Product.category_id","=","C.id")
			->where("track_id",$trackId)
			->get();
		}
		return $data;
	}

	/*
	Use 	: Approve Price Group
	Author 	: Axay Shah
	Date 	: 19 June,2019
	*/
	// public static function ApprovePriceGroup($trackId,$priceGroupId,$approveStatus,$isDefault = "N"){
	// 	$date 		= date("Y-m-d H:i:s"); 
	// 	$data 		= array();
	// 	$customerId = 0;
 // 		if(!empty($trackId) && !empty($approveStatus)){
	// 		if($approveStatus == PRICE_GROUP_ACCEPT){
	// 			if($isDefault == "Y"){
	// 				$trackData = self::where("track_id",$trackId)->where("para_waste_type_id",$priceGroupId)->where("approve_status",PRICE_GROUP_PENDING)->get();	
	// 				if($trackData){
	// 					foreach ($trackData as $value) {
	// 						$priceDetail = CompanyProductPriceDetail::where("para_waste_type_id",$value->para_waste_type_id)->where("product_id",$value->product_id)->get();
	// 						if($priceDetail){
	// 							// $priceDetail->product_id 	     = $value->product_id;
	// 							// $priceDetail->para_waste_type_id = $value->para_waste_type_id;
	// 							// $priceDetail->product_inert      = $value->new_product_inert;
	// 							// $priceDetail->factory_price      = $value->new_factory_price;
	// 							// $priceDetail->price              = $value->new_price;
	// 							CompanyProductPriceDetail::where("para_waste_type_id",$value->para_waste_type_id)->where("product_id",$value->product_id)->where("details_id",$value->details_id)->update([
	// 								"product_inert" =>$value->new_product_inert,
	// 								"factory_price" =>$value->new_factory_price,
	// 								"price" =>$value->new_price
	// 							]);
	// 						}
	// 					}	
	// 				}
	// 			}else{
	// 				$trackData = self::where("track_id",$trackId)->where("para_waste_type_id",$priceGroupId)->where("approve_status",PRICE_GROUP_PENDING)->get();	
	// 				if(!empty($trackData)){
	// 					$customerId = $trackData[0]->customer_id;
	// 					CompanyProductPriceDetail::where("para_waste_type_id",$priceGroupId)->delete();
	// 					foreach ($trackData as $value) {
	// 						$priceDetail = new CompanyProductPriceDetail();
	// 						$priceDetail->product_id 	     = $value->product_id;
	// 						$priceDetail->para_waste_type_id = $value->para_waste_type_id;
	// 						$priceDetail->product_inert      = $value->new_product_inert;
	// 						$priceDetail->factory_price      = $value->new_factory_price;
	// 						$priceDetail->price              = $value->new_price;
	// 						$priceDetail->save();
	// 		        	}
	// 		        	CustomerMaster::where("customer_id",$customerId)->update(["price_group"=>$priceGroupId]);
	// 		    	}	
	// 			}
	// 		}
	// 		$data = self::where("track_id",$trackId)->update(["approved_by"=>Auth()->user()->adminuserid,"approved_date"=>$date,"approve_status"=>$approveStatus]);
	// 	}
	// 	return $data;
	// }

	/*
	Use 	: Approve Price Group
	Author 	: Axay Shah
	Date 	: 19 June,2019
	*/

	/*
	Use 	: Approve Price Group
	Author 	: Axay Shah
	Date 	: 19 June,2019
	*/
	public static function ApprovePriceGroup($trackId,$priceGroupId,$approveStatus,$isDefault = "N"){
		$date 		= date("Y-m-d H:i:s"); 
		$data 		= array();
		$customerId = 0;
 		if(!empty($trackId) && !empty($approveStatus)){
			if($approveStatus == PRICE_GROUP_ACCEPT){
				if($isDefault == "Y"){
					$trackData = self::where("track_id",$trackId)->where("para_waste_type_id",$priceGroupId)->where("approve_status",PRICE_GROUP_PENDING)->get();	
					if($trackData){
						foreach ($trackData as $value) {
							$priceDetail = CompanyProductPriceDetail::where("para_waste_type_id",$value->para_waste_type_id)->where("product_id",$value->product_id)->get();
							if($priceDetail){
								// $priceDetail->product_id 	     = $value->product_id;
								// $priceDetail->para_waste_type_id = $value->para_waste_type_id;
								// $priceDetail->product_inert      = $value->new_product_inert;
								// $priceDetail->factory_price      = $value->new_factory_price;
								// $priceDetail->price              = $value->new_price;
								CompanyProductPriceDetail::where("para_waste_type_id",$value->para_waste_type_id)->where("product_id",$value->product_id)->where("details_id",$value->details_id)->update([
									"product_inert" =>$value->new_product_inert,
									"factory_price" =>$value->new_factory_price,
									"price" =>$value->new_price
								]);
							}
						}	
					}
				}else{
					$trackData = self::where("track_id",$trackId)->where("para_waste_type_id",$priceGroupId)->where("approve_status",PRICE_GROUP_PENDING)->get();	
					if(!empty($trackData)){
						$customerId = $trackData[0]->customer_id;
						$cityID 	= $trackData[0]->city_id;
						$isDefault 	= $trackData[0]->is_default;
						$companyId 	= $trackData[0]->company_id;
							if($priceGroupId == 0){
								$priceGroupId = CompanyPriceGroupMaster::where("customer_id",$customerId)->where("is_default",$isDefault)->value("id");

								if(empty($priceGroupId)){
									$lastCusCode =   MasterCodes::getMasterCode(MASTER_CODE_CUSTOMER);
									if($lastCusCode){
										$newCreatedCode             = $lastCusCode->code_value + 1;
										$newCode                    = $lastCusCode->prefix.''.$newCreatedCode;
										$newPriceGroup              = PRICE_GROUP_PRIFIX.''.$newCode;
										$PriceGroup 				= new CompanyPriceGroupMaster();
										$PriceGroup->group_value   	= $newPriceGroup;
										$PriceGroup->group_desc    	= $newPriceGroup;
										$PriceGroup->group_tech_desc= $newPriceGroup;
										$PriceGroup->group_type    	= DEFAULT_PRICE_GROUP_TYPE;
										$PriceGroup->status        	= DEFAULT_PRICE_GROUP_ACTIVE_STATUS;
										$PriceGroup->city_id       	= $cityID;
										$PriceGroup->is_default    	= $isDefault;
										$PriceGroup->company_id    	= $companyId;
										$PriceGroup->customer_id 	= $customerId;
										if($PriceGroup->save()){
											$priceGroupId =  $PriceGroup->id;
											// CustomerMaster::where("customer_id",$customerId)->update(["price_group"=>$priceGroupId]);
											CustomerAddress::where('customer_id',$customerId)->where('city',$cityID)->update(["price_group"=>$priceGroupId]);
											MasterCodes::updateMasterCode(MASTER_CODE_CUSTOMER,$newCreatedCode);
											
											foreach ($trackData as $value) {
												$priceDetail = new CompanyProductPriceDetail();
												$priceDetail->product_id 	     = $value->product_id;
												$priceDetail->para_waste_type_id = $priceGroupId;
												$priceDetail->product_inert      = $value->new_product_inert;
												$priceDetail->factory_price      = $value->new_factory_price;
												$priceDetail->price              = $value->new_price;
												$priceDetail->save();
								        	}
										}
									}
								}
							}else{
								CompanyProductPriceDetail::where("para_waste_type_id",$priceGroupId)->delete();
								foreach ($trackData as $value) {

									$priceDetail = new CompanyProductPriceDetail();
									$priceDetail->product_id 	     = $value->product_id;
									$priceDetail->para_waste_type_id = $value->para_waste_type_id;
									$priceDetail->product_inert      = $value->new_product_inert;
									$priceDetail->factory_price      = $value->new_factory_price;
									$priceDetail->price              = $value->new_price;
									$priceDetail->save();
					        	}
						}
						// CustomerMaster::where("customer_id",$customerId)->update(["price_group"=>$priceGroupId]);
						CustomerAddress::where('customer_id',$customerId)->where('city',$cityID)->update(["price_group"=>$priceGroupId]);
			    	}
				}
			}
			
			$data = self::where("track_id",$trackId)->update(["approved_by"=>Auth()->user()->adminuserid,"approved_date"=>$date,"approve_status"=>$approveStatus]);
		}
		return $data;
	}
	public static function ApprovePriceGroup_org($trackId,$priceGroupId,$approveStatus,$isDefault = "N"){
		$date 		= date("Y-m-d H:i:s"); 
		$data 		= array();
		$customerId = 0;
 		if(!empty($trackId) && !empty($approveStatus)){
			if($approveStatus == PRICE_GROUP_ACCEPT){
				if($isDefault == "Y"){
					$trackData = self::where("track_id",$trackId)->where("para_waste_type_id",$priceGroupId)->where("approve_status",PRICE_GROUP_PENDING)->get();	
					if($trackData){
						foreach ($trackData as $value) {
							$priceDetail = CompanyProductPriceDetail::where("para_waste_type_id",$value->para_waste_type_id)->where("product_id",$value->product_id)->get();
							if($priceDetail){
								// $priceDetail->product_id 	     = $value->product_id;
								// $priceDetail->para_waste_type_id = $value->para_waste_type_id;
								// $priceDetail->product_inert      = $value->new_product_inert;
								// $priceDetail->factory_price      = $value->new_factory_price;
								// $priceDetail->price              = $value->new_price;
								CompanyProductPriceDetail::where("para_waste_type_id",$value->para_waste_type_id)->where("product_id",$value->product_id)->where("details_id",$value->details_id)->update([
									"product_inert" =>$value->new_product_inert,
									"factory_price" =>$value->new_factory_price,
									"price" =>$value->new_price
								]);
							}
						}	
					}
				}else{
					$trackData = self::where("track_id",$trackId)->where("para_waste_type_id",$priceGroupId)->where("approve_status",PRICE_GROUP_PENDING)->get();	
					if(!empty($trackData)){
						$customerId = $trackData[0]->customer_id;
						$cityID 	= $trackData[0]->city_id;
						$isDefault 	= $trackData[0]->is_default;
						$companyId 	= $trackData[0]->company_id;
							if($priceGroupId == 0){
								$priceGroupId = CompanyPriceGroupMaster::where("customer_id",$customerId)->where("is_default",$isDefault)->value("id");

								if(empty($priceGroupId)){
									$lastCusCode =   MasterCodes::getMasterCode(MASTER_CODE_CUSTOMER);
									if($lastCusCode){
										$newCreatedCode             = $lastCusCode->code_value + 1;
										$newCode                    = $lastCusCode->prefix.''.$newCreatedCode;
										$newPriceGroup              = PRICE_GROUP_PRIFIX.''.$newCode;
										$PriceGroup 				= new CompanyPriceGroupMaster();
										$PriceGroup->group_value   	= $newPriceGroup;
										$PriceGroup->group_desc    	= $newPriceGroup;
										$PriceGroup->group_tech_desc= $newPriceGroup;
										$PriceGroup->group_type    	= DEFAULT_PRICE_GROUP_TYPE;
										$PriceGroup->status        	= DEFAULT_PRICE_GROUP_ACTIVE_STATUS;
										$PriceGroup->city_id       	= $cityID;
										$PriceGroup->is_default    	= $isDefault;
										$PriceGroup->company_id    	= $companyId;
										$PriceGroup->customer_id 	= $customerId;
										if($PriceGroup->save()){
											$priceGroupId =  $PriceGroup->id;
											CustomerMaster::where("customer_id",$customerId)->update(["price_group"=>$priceGroupId]);
											MasterCodes::updateMasterCode(MASTER_CODE_CUSTOMER,$newCreatedCode);
											
											foreach ($trackData as $value) {
												$priceDetail = new CompanyProductPriceDetail();
												$priceDetail->product_id 	     = $value->product_id;
												$priceDetail->para_waste_type_id = $priceGroupId;
												$priceDetail->product_inert      = $value->new_product_inert;
												$priceDetail->factory_price      = $value->new_factory_price;
												$priceDetail->price              = $value->new_price;
												$priceDetail->save();
								        	}
										}
									}
								}
							}else{
								CompanyProductPriceDetail::where("para_waste_type_id",$priceGroupId)->delete();
								foreach ($trackData as $value) {

									$priceDetail = new CompanyProductPriceDetail();
									$priceDetail->product_id 	     = $value->product_id;
									$priceDetail->para_waste_type_id = $value->para_waste_type_id;
									$priceDetail->product_inert      = $value->new_product_inert;
									$priceDetail->factory_price      = $value->new_factory_price;
									$priceDetail->price              = $value->new_price;
									$priceDetail->save();
					        	}
						}
						CustomerMaster::where("customer_id",$customerId)->update(["price_group"=>$priceGroupId]);
			    	}
				}
			}
			
			$data = self::where("track_id",$trackId)->update(["approved_by"=>Auth()->user()->adminuserid,"approved_date"=>$date,"approve_status"=>$approveStatus]);
		}
		return $data;
	}
}
