<?php

namespace App\Http\Controllers;
use App\Http\Controllers\LRBaseController;
use Illuminate\Http\Request;
use App\Models\AdminUser;
use App\Models\AdminUserRights;
use App\Models\WmServiceDocuments;
use JWTFactory;
use JWTAuth;
use Validator;
use Response;
use File;
use Storage;
use Input;
use DB;

class WmServiceController extends LRBaseController
{
	private function SetVariables($request)
	{
		
	}

	public function viewdocument($wm_service_id=0)
	{
		$WmServiceDocuments = WmServiceDocuments::where("wm_service_id",decode($wm_service_id))->first();
		if ($WmServiceDocuments->id > 0) {
			$File_Path = public_path("company/services/agreement/".$WmServiceDocuments->name);
			if (file_exists($File_Path)) {
				$extension = pathinfo($File_Path, PATHINFO_EXTENSION);
				header("Content-Length: " . filesize($File_Path));
				header("Content-type:".mime_content_type($File_Path));
				header("Content-disposition: inline;filename=AgreementCopy".$extension);
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				readfile($File_Path);
				die;
			} else {
				echo "<br /><center>Invalid Request !!!</center>";
				die;
			}
		} else {
			echo "<br /><center>Invalid Request !!!</center>";
			die;
		}
	}
}