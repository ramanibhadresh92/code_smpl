<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmServiceDocuments extends Model
{
	protected $table 	= 'wm_service_master_documents';
	public $timestamps 	= false;

	/*
	Use 	: SaveAgreementCopy
	Author 	: Kalpak Prajapati
	Date 	: 28 Dec 2021
	*/
	public static function SaveAgreementCopy($po_document,$wm_service_id=0)
	{
		$DBName 	= time()."-".basename($po_document);
		$filename 	= public_path("company/services/agreement/".$DBName);
		$fp 		= fopen($filename,"w+");
		fwrite($fp, @file_get_contents($po_document));
		fclose($fp);
		$AddRecord 					= new self();
		$AddRecord->wm_service_id 	= $wm_service_id;
		$AddRecord->name 			= $DBName;
		$AddRecord->created_at 		= date("Y-m-d H:i:s");
		$AddRecord->updated_at 		= date("Y-m-d H:i:s");
		$AddRecord->save();
	}

	/*
	Use 	: GetAgreementCopyURL
	Author 	: Kalpak Prajapati
	Date 	: 28 Dec 2021
	*/
	public static function GetAgreementCopyURL($id=0)
	{
		$AgreementCopyURL	= "";
		$AgreementCopy 		= self::where("wm_service_id",$id)->first();
		if (!empty($AgreementCopy)) {
			$AgreementCopyURL = env("APP_URL")."/view-document/service/".encode($id);
		}
		return $AgreementCopyURL;
	}
}