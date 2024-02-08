<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\AppointmentCollection;
use App\Models\CompanyProductQualityParameter;
use App\Models\CustomerMaster;
use App\Models\AppointmentCollectionDetail;
use App\Http\Requests\AppointmentCollectionUpdateRequest;
use DB;
class CollectionController extends LRBaseController
{
	public function dropdown(Request $request){
		$data = "";
		(isset($request->isJson) && $request->isJson == 1) ? $request->isJson = true : $request->isJson = false;
		if(isset($request->param)){
			switch(strtolower($request->param))
			{
				case "category" :
				{
					$data = AppointmentCollection::getProductByCategory();
					break;
				}
				case "productquality" :
				{
					/*ID = PRODUCT ID* */
					$data = CompanyProductQualityParameter::getProductQuality($request->id);
					break;
				}
				case "productinert" :
				{
					/*ID : PRODUCT ID*/
					$data = CustomerMaster::getCustomerPrice($request->collection_id,$request->id);
					(!empty($data) && isset($data->product_inert)) ? $data['product_inert'] = $data->product_inert : $data['product_inert'] = 0 ;
					break;
				}
				case "paramdtls" :
				{
					/*ID : company_product_quality_id*/
					$data = CompanyProductQualityParameter::getParamDetail($request);
					break;
				}
			}
		}
		return response()->json(['code' => SUCCESS , "msg"=>trans("message.RECORD_FOUND"),"data"=>$data]);
	}
	/*
	Use     : Save and Add new collection (ORIGINAL FUNCTION )
	Author  : Axay Shah
	Date    : 30 Nov,2018
	*/
	// public function saveAndAddNew(AppointmentCollectionUpdateRequest $request){
	//     try{
	//         DB::beginTransaction();
	//         $data = AppointmentCollection::updateCollection($request);
	//         DB::commit();
	//     }catch(\Exception $e){
	//         // dd($e);
	//         DB::rollback();
	//         $data = $e;
	//     }
	//     return response()->json(['code' => SUCCESS , "msg"=>trans("message.RECORD_FOUND"),"data"=>$data]);
	// }

	/*
	Use     : Save and Add new collection
	Author  : Axay Shah
	Date    : 11 JULY 2019
	*/
	public function saveAndAddNew(AppointmentCollectionUpdateRequest $request){
		try{
			DB::beginTransaction();
			$data = AppointmentCollection::updateCollection($request,true);
			DB::commit();
		}catch(\Exception $e){
			// dd($e);
			DB::rollback();
			$data = $e;
		}
		return response()->json(['code' => SUCCESS , "msg"=>trans("message.RECORD_FOUND"),"data"=>$data]);
	}
	/*
	Use     : Save and Add new collection
	Author  : Axay Shah
	Date    : 30 Nov,2018
	*/
	// public function finalize(Request $request){
	//     DB::beginTransaction();
	//     try{

	//         $data = AppointmentCollection::FinalizeCollection($request);
	//         $msg  = trans("message.RECORD_INSERTED");
	//         $code = SUCCESS;
	//         DB::commit();
	//     }catch(\Exception $e){
	//         // dd($e);
	//         DB::rollback();
	//        $data = "";
	//     //    $msg  = trans("message.SOMETHING_WENT_WRONG");
	//        $msg = $e->getMessage()." ".$e->getFile();
	//        $code = INTERNAL_SERVER_ERROR;
	//     }
	//     return response()->json(['code' => $code , "msg"=>$msg,"data"=>$data]);
	// }

	/*
	Use     : FINALIZE COLLECTION
	Author  : Axay Shah
	Date    : 11 JULY 2019
	*/
	public function finalize(Request $request){
		DB::beginTransaction();
		try{
			$data   = AppointmentCollection::FinalizeCollection($request,true);
			$msg    = trans("message.RECORD_INSERTED");
			$code   = SUCCESS;
			DB::commit();
		}catch(\Exception $e){
			DB::rollback();
			$data   = "";
			$msg    = trans("message.SOMETHING_WENT_WRONG");
			$msg    = $e->getMessage()." ".$e->getFile();
			$code   = INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code' => $code , "msg"=>$msg,"data"=>$data]);
	}

	public function retrieveAllCollectionDetails(Request $request){
		$data = "";
		try{
			$data = AppointmentCollectionDetail::retrieveAllCollectionDetails($request,true);
			$msg  = trans("message.RECORD_FOUND");
		}catch(\Exception $e){
			$msg =  trans("message.SOMETHING_WENT_WRONG");
		}
		return response()->json(['code' => SUCCESS , "msg"=>trans("message.RECORD_FOUND"),"data"=>$data,"add_multiple"=>LUMSUM_ADD_MULTIPLE]);
	}
	/*
	Use     : GET GPS REPORT
	Author  : Axay Shah
	Date    : 27 Mar,2019
	*/
	public function getGPSReport(Request $request){
		$data = "";
		try{
			$data = AppointmentCollection::getGPSReport($request);
			$msg  = trans("message.RECORD_FOUND");
		}catch(\Exception $e){
			$msg =  trans("message.SOMETHING_WENT_WRONG");
		}
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/*
	Use     : Update collection Details
	Author  : Axay Shah
	Date    : 27 Mar,2019
	*/
	public function updateCollectionById(Request $request){
		$data = array();
		try{
			$vehicle_id             = (isset($request->vehicle_id)) ? $request->vehicle_id : 0;
			$quantity               = (isset($request->quantity)) ? $request->quantity : 0;
			$collection_details_id  = (isset($request->collection_detail_id)) ? $request->collection_detail_id : 0;
			$collectionId           = (isset($request->collection_id)) ? $request->collection_id : 0;
			$data                   = AppointmentCollectionDetail::updateCollectionById($collectionId,$collection_details_id,$vehicle_id,$quantity);
			$msg                    = trans("message.RECORD_UPDATED");
		}catch(\Exception $e){
			$msg = $e;
		}
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}


}
