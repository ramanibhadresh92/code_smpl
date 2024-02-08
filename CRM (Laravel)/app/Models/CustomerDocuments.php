<?php

namespace App\Models;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Traits\storeImage;
use DB;
class CustomerDocuments extends Model
{
	use storeImage;
	protected 	$table = 'customer_documents';

	public function mediaId() {
		return $this->belongsTo(MediaMaster::class,'media_id');
	}

	/**
	* Function Name : getCustomerDocuments
	* @param integer $CustomerID
	* @return array $arrResult
	* @author Kalpak Prajapati
	* @since 2022-08-22
	* @access public
	* @uses method used to get customer documentss
	*/
	public static function getCustomerDocuments($CustomerID=0)
	{
		$CustomerID	= intval($CustomerID);
		$arrResult 	= array();
		if (!empty($CustomerID)) {
			$arrResult = self::select(	"customer_documents.id",
										"customer_documents.media_id",
										DB::raw('parameter.para_value as DocumentType'),
										DB::raw('customer_documents.doc_title as DocumentTitle'),
										DB::raw("CONCAT(adminuser.firstname,' ',adminuser.lastname) AS UploadedBy"),
										DB::raw("DATE_FORMAT(customer_documents.created_at,'%Y-%m-%d %H:%i %p') as UploadedOn"))
							->leftJoin('parameter','parameter.para_id','=','customer_documents.doc_type')
							->leftJoin('adminuser','adminuser.adminuserid','=','customer_documents.created_by')
							->leftJoin('media_master','media_master.id','=','customer_documents.media_id')
							->where('customer_documents.customer_id',$CustomerID)
							->where('customer_documents.status',PARA_STATUS_ACTIVE)
							->whereNotNull('media_master.id')
							->orderBy("customer_documents.created_at","DESC")
							->get();
			if (!empty($arrResult)) {
				foreach($arrResult as $arrResultRow) {
					$arrResultRow->docurl = "";
					if (!empty($arrResultRow->media_id) && isset($arrResultRow->mediaId->id)) {
						$SERVER_PATH = public_path(DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR);
						$SERVER_PATH .= $arrResultRow->mediaId->image_path.DIRECTORY_SEPARATOR.basename($arrResultRow->mediaId->original_name);
						if (file_exists($SERVER_PATH)) {
							$arrResultRow->docurl = $arrResultRow->mediaId->original_name;
						}
						$arrResultRow->docpath = $SERVER_PATH;
					}
				}
			}
		}
		return $arrResult;
	}

	/**
	* Function Name : saveCustomerDocument
	* @param object $request
	* @return boolean $arrResult
	* @author Kalpak Prajapati
	* @since 2022-08-22
	* @access public
	* @uses method used to get customer documentss
	*/
	public static function saveCustomerDocument($request)
	{
		$NewRecord 				= new self;
		$NewRecord->customer_id = $request->customer_id;
		$NewRecord->doc_title 	= $request->doc_title;
		$NewRecord->doc_type 	= $request->doc_type;
		$NewRecord->status 		= PARA_STATUS_ACTIVE;
		$MEDIA_ID 				= 0;
		if($request->hasfile('doc_file')) {
			$MEDIARECORD 	= $NewRecord->uploadDoc($request,'doc_file',PATH_COMPANY,Auth()->user()->company_id,PATH_COMPANY_CUSTOMER."/".PATH_CUSTOMER_DOC."/".$request->customer_id,0);
			$MEDIA_ID 		= isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
		}
		$NewRecord->media_id 	= $MEDIA_ID;
		$NewRecord->created_by 	= Auth()->user()->adminuserid;
		$NewRecord->updated_by 	= Auth()->user()->adminuserid;
		$NewRecord->save();
		return $NewRecord->id;
	}
}
