<?php
namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmProductClientPriceMaster;
use App\Models\Parameter;
use App\Http\Requests\AddProductClientPrice;
use App\Http\Requests\UpdateProductClientPrice;
use Validator;
class ClientMasterController extends LRBaseController
{

	/*
	Use     : List Product Price Client wise
	Author  : Axay Shah
	Date 	: 19 June,2020
	*/
	public function ListProductClientPrice(Request $request){
		$data 		= WmProductClientPriceMaster::ListProductClientPrice($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]); 
	}

	/*
	Use     : Get By ID
	Author  : Axay Shah
	Date 	: 19 June,2020
	*/
	public function GetClientPriceById(UpdateProductClientPrice $request){
		$ID = (isset($request->id) && !empty($request->id)) ? $request->id : 0 ;
		$data 		= WmProductClientPriceMaster::GetClientPriceById($ID);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]); 
	}
	
	/*
	Use     : Add Product Client Wise Price
	Author  : Axay Shah
	Date 	: 19 June,2020
	*/
	public function AddClientProductPrice(AddProductClientPrice $request){
		$data 		= WmProductClientPriceMaster::AddClientProductPrice($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]); 
	}


	/*
	Use     : Update Product client wise Price 
	Author  : Axay Shah
	Date 	: 19 June,2020
	*/
	public function UpdateClientProductPrice(Request $request){
		$data 		= WmProductClientPriceMaster::UpdateClientProductPrice($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]); 
	}  


	/*
	Use     : Transporter Cost Drop down
	Author  : Axay Shah
	Date 	: 23 September,2021
	*/
	public function GetTransportCostDropDown(Request $request){
		$data 		= Parameter::parentDropDown(PARA_TRANSPORTER_NAME)->get();
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]); 
	} 
}
