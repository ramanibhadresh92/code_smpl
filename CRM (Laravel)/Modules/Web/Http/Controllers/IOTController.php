<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\IOTSensorData;
class IOTController extends LRBaseController
{
	/**
	* Function Name : saveSensorData
	* @param mixed $request
	* @return
	* @author Kalpak Prajapati
	* @since 2022-05-04
	*/
	public function saveSensorData(Request $request)
	{
		$arrFields['row_data'] 	= isset($request['row_data'])?$request['row_data']:"";
		$arrFields['row_data'] 	= (!empty($arrFields['row_data'])?$arrFields['row_data']:json_encode($request->all()));
		IOTSensorData::AddNewRecord($arrFields);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_INSERTED")));
	}

	/**
	* Function Name : onsPaymentLog
	* @param mixed $request
	* @return
	* @author Kalpak Prajapati
	* @since 2022-05-04
	*/
	public function onsPaymentLog(Request $request)
	{
		dd($_POST);
		// dd($request->all());
	}
}
