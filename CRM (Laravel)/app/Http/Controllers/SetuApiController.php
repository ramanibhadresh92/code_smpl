<?php
namespace App\Http\Controllers;
use App\Http\Controllers\LRBaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Classes\SetuApi;
class SetuApiController extends LRBaseController
{
	/*
	Use     : Generate Token project wise
	Author  : Kalpak Prajapati
	Date    : 03-11-2023
	*/
	public function GenerateTokenSetuApi(Request $request)
	{
		$client_id  	= (isset($request->client_id) && !empty($request->client_id)) ? $request->client_id : "";
		$client_secret 	= (isset($request->client_secret) && !empty($request->client_secret)) ? $request->client_secret : "";
		$SetuAPI 		= new SetuAPI();
		$response 		= $SetuAPI->GenerateTokenSetuApi($client_id,$client_secret);
		return response()->json($response);
	}

	/*
	Use     : Give GST number status
	Author  : Kalpak Prajapati
	Date    : 03-11-2023
	*/
	public function VerifyGSTDetails(Request $request)
	{
		$SetuAPI 	= new SetuAPI();
		$response 	= $SetuAPI->VerifyGSTDetails($request);
		return response()->json($response);
	}

	/*
	Use     : Give CIN Details
	Author  : Kalpak Prajapati
	Date    : 03-11-2023
	*/
	public function VerifyCINDetails(Request $request)
	{
		$SetuAPI 	= new SetuAPI();
		$response 	= $SetuAPI->VerifyCINDetails($request);
		return response()->json($response);
	}

	/*
	Use     : Give CIN Details
	Author  : Kalpak Prajapati
	Date    : 03-11-2023
	*/
	public function VerifyCompanyDirectorDetailsByCIN(Request $request)
	{
		$SetuAPI 	= new SetuAPI();
		$response 	= $SetuAPI->VerifyCompanyDirectorDetailsByCIN($request);
		return response()->json($response);
	}
}