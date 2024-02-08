<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\LocationMaster;
use App\Models\StateMaster;
use App\Models\CountryMaster;
use App\Facades\LiveServices;
use Validator;
use DB;
use JWTAuth;
use Log;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class CustomerAddress extends Model
{
	protected 	$table 		    = 'customer_address';
	public      $timestamps     = false;
	protected   $primaryKey     = 'id'; 
	protected   $guarded        = ['id'];
	// use AuditableTrait;
	/**
	 * Function Name : Customer Address List
	 * @param $request
	 * @return Json
	 * @author Hardyesh Gupta
	 * @date 8 May, 2023
	 */    
	public static function getCustomerAddressList($request)
	{
		$LocationMaster = new LocationMaster();
		$Location       = $LocationMaster->getTable();
		$StateMaster 	= new StateMaster();
		$StateTble      = $StateMaster->getTable();
		$Today          = date('Y-m-d');
		$CustomerAddressTbl = (new static)->getTable();
		$sortBy         = ($request->has('sortBy')              && !empty($request->input('sortBy')))    ? $request->input('sortBy')    : "id";
		$sortOrder      = ($request->has('sortOrder')           && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 5;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		/*
		$result = CustomerAddress::select("*","$Location.city as city_name")
			->join("$Location",'customer_address.city',"=","$Location.location_id")
			->where('company_id',Auth::user()->company_id);
		//print_r($result) ;
		*/
		$result =   CustomerAddress::select(					
					\DB::raw("$CustomerAddressTbl.id as id"),
					\DB::raw("$CustomerAddressTbl.customer_id as customer_id"),
					\DB::raw("$CustomerAddressTbl.address1 as address1"),
					\DB::raw("$CustomerAddressTbl.address2 as address2"),
					\DB::raw("$CustomerAddressTbl.landmark as landmark"),
					\DB::raw("$Location.city as city"),
					\DB::raw("$StateTble.state_name as state"),
					\DB::raw("$CustomerAddressTbl.zipcode as zipcode"),
					\DB::raw("$CustomerAddressTbl.gst_no as gst_no"),
					\DB::raw("$CustomerAddressTbl.longitude as longitude"),
					\DB::raw("$CustomerAddressTbl.lattitude as lattitude"),
					\DB::raw("$CustomerAddressTbl.status as status"),
					\DB::raw("DATE_FORMAT($CustomerAddressTbl.created_at,'%d-%m-%Y') as created_from"),
					\DB::raw("DATE_FORMAT($CustomerAddressTbl.created_at,'%d-%m-%Y') as created_to"))
					->leftjoin("$Location","$CustomerAddressTbl.city","=","$Location.location_id")
					->leftjoin("$StateTble","$CustomerAddressTbl.state","=","$StateTble.state_id");	
		
		if($request->has('params.id') && !empty($request->input('params.id')))
		{
			$result->where("$CustomerAddressTbl.id",$request->input('params.id'));
		}
		
		if($request->has('params.customer_id') && !empty($request->input('params.customer_id')))
		{
			$result->where("$CustomerAddressTbl.customer_id",$request->input('params.customer_id'));
		}
		
		if($request->has('params.gst_no') && !empty($request->input('params.gst_no')))
		{
			$result->where("$CustomerAddressTbl.gst_no",'like','%'.$request->input('params.gst_no').'%');
		}        
		if($request->has('params.address1') && !empty($request->input('params.address1')))
		{
			$result->where("$CustomerAddressTbl.address1",'like','%'.$request->input('params.address1').'%');
		}
		if($request->has('params.address2') && !empty($request->input('params.address2')))
		{
			$result->where("$CustomerAddressTbl.address2",'like','%'.$request->input('params.address2').'%');
		}
		if($request->has('params.landmark') && !empty($request->input('params.landmark')))
		{
			$result->where("$CustomerAddressTbl.landmark",'like','%'.$request->input('params.landmark').'%');
		}
		if($request->has('params.zipcode') && !empty($request->input('params.zipcode')))
		{
			$result->where("$CustomerAddressTbl.zipcode",'like','%'.$request->input('params.zipcode').'%');
		}
		if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to')))
		{
			$result->whereBetween("$CustomerAddressTbl.created_at",array(date("Y-m-d H:i:s", strtotime($request->input('params.created_from')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('params.created_to')." ".GLOBAL_END_TIME))));
		}else if(!empty($request->input('params.created_from'))){
		   $datefrom = date("Y-m-d", strtotime($request->input('params.created_from')));
		   $result->whereBetween("$CustomerAddressTbl.created_at",array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
		}else if(!empty($request->input('params.created_to'))){
		   $result->whereBetween("$CustomerAddressTbl.created_at",array(date("Y-m-d", strtotime($request->input('params.created_to'))),$Today));
		}
		if($request->has('params.city') && !empty($request->input('params.city')))
		{
			$result->where("$CustomerAddressTbl.city",$request->input('params.city'));
		}
		if($request->has('params.state') && !empty($request->input('params.state')))
		{
			$result->where("$CustomerAddressTbl.state",$request->input('params.state'));
		}
		if($request->has('params.status'))
		{
			$status = $request->input('params.status');
			if($status == "-1"){
				$status = 0;
				$data->where("$CustomerAddressTbl.status",$status);
			}elseif($status == 1){
				$status = 1;
				$data->where("$CustomerAddressTbl.status",$status);
			}
		}
		$bindings= LiveServices::toSqlWithBinding($result);
		$data = $result->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		return $data;
		
	}

	/**
	 * Function Name : CustomerWise Address List
	 * @param $request
	 * @return Json
	 * @author Hardyesh Gupta
	 * @date 8 May, 2023
	 */    
	public static function getCustomerWiseAddresslist($request)
	{
		$LocationMaster = new LocationMaster();
		$Location       = $LocationMaster->getTable();
		$StateMaster    = new StateMaster();
		$StateTble      = $StateMaster->getTable();
		$Today          = date('Y-m-d');
		$CustomerAddressTbl = (new static)->getTable();
		$customer_id    = (isset($request->customer_id)         && !empty($request->customer_id))        ? $request->customer_id : 0;
		$sortBy         = ($request->has('sortBy')              && !empty($request->input('sortBy')))    ? $request->input('sortBy')    : "id";
		$sortOrder      = ($request->has('sortOrder')           && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 100;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$result =   CustomerAddress::select(                    
					\DB::raw("$CustomerAddressTbl.id as id"),
					\DB::raw("$CustomerAddressTbl.customer_id as customer_id"),
					\DB::raw("$CustomerAddressTbl.address1 as address1"),
					\DB::raw("$CustomerAddressTbl.address2 as address2"),
					\DB::raw("$CustomerAddressTbl.landmark as landmark"),
					\DB::raw("$Location.city as city"),
					\DB::raw("$StateTble.state_name as state"),
					\DB::raw("$CustomerAddressTbl.zipcode as zipcode"),
					\DB::raw("$CustomerAddressTbl.gst_no as gst_no"),
					\DB::raw("$CustomerAddressTbl.longitude as longitude"),
					\DB::raw("$CustomerAddressTbl.lattitude as lattitude"),
					\DB::raw("$CustomerAddressTbl.status as status"),
					\DB::raw("DATE_FORMAT($CustomerAddressTbl.created_at,'%d-%m-%Y') as created_from"),
					\DB::raw("DATE_FORMAT($CustomerAddressTbl.created_at,'%d-%m-%Y') as created_to"))
					->leftjoin("$Location","$CustomerAddressTbl.city","=","$Location.location_id")
					->leftjoin("$StateTble","$CustomerAddressTbl.state","=","$StateTble.state_id"); 
		if(!empty($request->customer_id))
		{
			$result->where("$CustomerAddressTbl.customer_id",$request->customer_id);
		}
		// LiveServices::toSqlWithBinding($result);
		$data = $result->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		return $data;
	}

	/**
	 * Function Name : Create Customer Address
	 * @param $request
	 * @return Json
	 * @author Hardyesh Gupta
	 * @date 8 May, 2023
	 */
	public static function CreateCustomerAddress($request)
	{
		$msg = trans('message.RECORD_INSERTED');
		try{
				DB::beginTransaction();
				$CustomerAddress                 = new CustomerAddress();
				$CustomerAddress->customer_id = (isset($request->customer_id)   && !empty($request->customer_id)) ? $request->customer_id   :  0;
				$cityId                          = (isset($request->city) && !empty($request->city)) ? $request->city : 0  ;
				$stateID = 0;
				$countryID = 0;
				if($cityId > 0){
					$stateID    = LocationMaster::where("location_id",$cityId)->value('state_id');
					$countryID  = StateMaster::where("state_id",$stateID)->value('country_id');
				}
				$CustomerAddress->lattitude = (isset($request->lattitude) 	&& !empty($request->lattitude))	? $request->lattitude 	:  0;
				$CustomerAddress->longitude = (isset($request->longitude) 	&& !empty($request->longitude))	? $request->longitude 	:  0;
				$CustomerAddress->address1  = (isset($request->address1) 	&& !empty($request->address1)) 	? $request->address1 	: '';
				//$CustomerAddress->address2  = (isset($request->address2) 	&& !empty($request->address2)) 	? $request->address2 	: '';
				$CustomerAddress->city      = $cityId;
				$CustomerAddress->state     = $stateID;
				// $CustomerAddress->country   = (isset($request->country) 	&& !empty($request->country))  	? $request->country 	: '';
				$CustomerAddress->country   = $countryID;
				$CustomerAddress->zipcode 	= (isset($request->zipcode)	&& !empty($request->zipcode))  	? $request->zipcode    	:  0;
				$CustomerAddress->landmark  = (isset($request->landmark) 	&& !empty($request->landmark)) 	? $request->landmark 	: '';
				$CustomerAddress->gst_no    = (isset($request->gst_no)   	&& !empty($request->gst_no))   	? $request->gst_no 		: '';
				$CustomerAddress->status    = (isset($request->status)   	&& !empty($request->status))   	? $request->status 		: 0;
				$CustomerAddress->created_at= date('Y-m-d H:i:s');
				if($CustomerAddress->save()){
					$customer_id = $CustomerAddress->customer_id;
					/*Price Group Update if Same City have Multiple Address */
					$CusDataCountQry = self::where('customer_id',$customer_id)->where('city',$cityId);
					$CusDataCount 	 = $CusDataCountQry->count();	
					if($CusDataCount > 1){
						$CusAddData 	= $CusDataCountQry->first();
						$price_group 	= $CusAddData->price_group;
						$CustomerAddress->update(['price_group'=>$price_group]);

					}
				}
				DB::commit();
			// return $CustomerAddress;
				return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$CustomerAddress]);
		} catch (\Exception $e) {
				 DB::rollback();
				return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>$e->getMessage(),"data" =>""]);
			return $e;
		}
	}

	/**
	 * Function Name : Update Customer Address
	 * @param $request
	 * @return Json
	 * @author Hardyesh Gupta
	 * @date 8 May, 2023
	 */
	public static function UpdateCustomerAddress($request)
	{
		$msg        = trans('message.NO_RECORD_FOUND');
		try{
			DB::beginTransaction();
			$CustomerAddress = self::find($request->id);
			if($CustomerAddress){
				$CustomerAddress->customer_id = (isset($request->customer_id)   && !empty($request->customer_id)) ? $request->customer_id   :  0;
				$cityId              = (isset($request->city) && !empty($request->city)) ? $request->city : 0  ;
				$stateID = 0;
				if($cityId > 0){
					$stateID = LocationMaster::where("location_id",$cityId)->value('state_id');
					$countryID  = StateMaster::where("state_id",$stateID)->value('country_id');
				}
				$CustomerAddress->lattitude = (isset($request->lattitude) 	&& !empty($request->lattitude))	? $request->lattitude 	:  0;
				$CustomerAddress->longitude = (isset($request->longitude) 	&& !empty($request->longitude))	? $request->longitude 	:  0;
				$CustomerAddress->address1  = (isset($request->address1) 	&& !empty($request->address1)) 	? $request->address1 	: '';
				//$CustomerAddress->address2  = (isset($request->address2) 	&& !empty($request->address2)) 	? $request->address2 	: '';
				$CustomerAddress->city      = $cityId;
				$CustomerAddress->state     = $stateID;
				// $CustomerAddress->country   = (isset($request->country) 	&& !empty($request->country))  	? $request->country 	: '';
				$CustomerAddress->country   = $countryID;
				$CustomerAddress->zipcode 	= (isset($request->zipcode)	    && !empty($request->zipcode))  	? $request->zipcode    	:  0;
				$CustomerAddress->landmark  = (isset($request->landmark) 	&& !empty($request->landmark)) 	? $request->landmark 	: '';
				$CustomerAddress->gst_no    = (isset($request->gst_no)   	&& !empty($request->gst_no))   	? $request->gst_no 		: '';
				$CustomerAddress->status    = (isset($request->status)   	&& !empty($request->status))   	? $request->status 		: 0;
				$CustomerAddress->updated_at= date('Y-m-d H:i:s');
				if($CustomerAddress->save()){
					$msg        = trans('message.RECORD_UPDATED');
				}
			}      
			DB::commit();
			// return $CustomerAddress;        
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$CustomerAddress]);
		} catch (\Exception $e) {
			DB::rollback();
			// return $e;
			return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>$e->getMessage(),"data" =>""]);
		}
	}
	/**
	 * Function Name : Get Customer Address Details Get by Id
	 * @param $request
	 * @return Json
	 * @author Hardyesh Gupta
	 * @date 8 May, 2023
	 */
	public static function getById($id=0)
	{
		$CustomerAddress =  self::find($id);
		if($CustomerAddress){
			return $CustomerAddress;    
		}
	}

	
	public static function CustomerAddressDropDown($request){
		$customer_id= (isset($request->customer_id)   && !empty($request->customer_id)) ? $request->customer_id   :  0;
		$city		= (isset($request->city)   && !empty($request->city)) ? $request->city   :  0;
		$cityId     = GetBaseLocationCity();
		$self       = (new self)->getTable();
		$LCMaster   = new LocationMaster();
		$Location   = $LCMaster->getTable();
		$State    	= new StateMaster();
		$StateTbl   = $State->getTable();
		$Country    = new CountryMaster();
		$CountryTbl    = $Country->getTable();
		
		$query      = CustomerAddress::select(
					"$self.id",
					"$self.customer_id", 
					DB::raw("CONCAT($self.address1,', ',L.city,', ',S.state_name,'-',$self.zipcode) as full_address"),
					DB::raw("CONCAT($self.address1,'-',L.city) as address"),
					DB::raw("$self.city as location_id"),
					DB::raw("$self.city as city"),
					DB::raw("L.city as city_name"),
					DB::raw("$self.state as state"),
					DB::raw("S.state_name"),
					DB::raw("C.country_name"),
					"$self.zipcode",
					"$self.longitude",
					"$self.lattitude")
					->leftjoin("$Location as L","$self.city","=","L.location_id")
					->leftjoin("$StateTbl as S","$self.state","=","S.state_id") 
					->leftjoin("$CountryTbl as C","$self.country","=","C.country_id")
					->where("$self.customer_id", $customer_id)
					->where("$self.status", 1);
		if(!empty($city)){
			$query->where("$self.city", $city);
		}
		// LiveServices::toSqlWithBinding($query);
		return $query->get();

	}

	public static function getCustomerWiseAddresslist_COPY($request)
	{
		$LocationMaster = new LocationMaster();
		$Location       = $LocationMaster->getTable();
		$StateMaster    = new StateMaster();
		$StateTble      = $StateMaster->getTable();
		$Today          = date('Y-m-d');
		$CustomerAddressTbl = (new static)->getTable();
		$customer_id    = (isset($request->customer_id)         && !empty($request->customer_id))        ? $request->customer_id : 0;
		$sortBy         = ($request->has('sortBy')              && !empty($request->input('sortBy')))    ? $request->input('sortBy')    : "id";
		$sortOrder      = ($request->has('sortOrder')           && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 100;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$result =   CustomerAddress::select(                    
					\DB::raw("$CustomerAddressTbl.id as id"),
					\DB::raw("$CustomerAddressTbl.customer_id as customer_id"),
					\DB::raw("$CustomerAddressTbl.address1 as address1"),
					\DB::raw("$CustomerAddressTbl.address2 as address2"),
					\DB::raw("$CustomerAddressTbl.landmark as landmark"),
					\DB::raw("$Location.city as city"),
					\DB::raw("$StateTble.state_name as state"),
					\DB::raw("$CustomerAddressTbl.zipcode as zipcode"),
					\DB::raw("$CustomerAddressTbl.gst_no as gst_no"),
					\DB::raw("$CustomerAddressTbl.longitude as longitude"),
					\DB::raw("$CustomerAddressTbl.lattitude as lattitude"),
					\DB::raw("$CustomerAddressTbl.status as status"),
					\DB::raw("DATE_FORMAT($CustomerAddressTbl.created_at,'%d-%m-%Y') as created_from"),
					\DB::raw("DATE_FORMAT($CustomerAddressTbl.created_at,'%d-%m-%Y') as created_to"))
					->leftjoin("$Location","$CustomerAddressTbl.city","=","$Location.location_id")
					->leftjoin("$StateTble","$CustomerAddressTbl.state","=","$StateTble.state_id"); 
		if(!empty($request->customer_id))
		{
			$result->where("$CustomerAddressTbl.customer_id",$request->customer_id);
		}
		// LiveServices::toSqlWithBinding($result);
		$data = $result->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		return $data;
	}
	public static function CustomerAddressDropDown_ORG($request){
		$customer_id= (isset($request->customer_id)   && !empty($request->customer_id)) ? $request->customer_id   :  0;
		$cityId     = GetBaseLocationCity();
		$self       = (new self)->getTable();
		$query      = CustomerAddress::select("$self.id",
					"$self.customer_id", 
					DB::raw("CONCAT($self.address1,', ',L.city,', ',L.state_name,'-',$self.zipcode,', ',L.country_name) as full_address"),
					DB::raw("CONCAT($self.address1,'-',L.city) as address"),
					DB::raw("$self.city as location_id"),
					DB::raw("L.city as city_name"),
					DB::raw("L.state_name"),
					DB::raw("L.country_name"),
					"$self.zipcode")
					->leftjoin("view_city_state_contry_list as L","L.cityId","=","$self.city")
					->where("$self.customer_id", $customer_id)
					->where("$self.status", 1);
					// LiveServices::toSqlWithBinding($query);
		return $query->get();

	}


}
