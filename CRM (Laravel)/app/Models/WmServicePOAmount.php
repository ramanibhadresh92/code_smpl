<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmServicePOAmount extends Model
{
	protected $table 	= 'wm_po_amount';
	public $timestamps 	= false;

	/*
	Use 	: SavePoDetails
	Author 	: Kalpak Prajapati
	Date 	: 27 April 2021
	*/
	public static function SavePoDetails($wm_service_id=0,$po_no=0,$po_amount=0)
	{
		if (empty($po_no)) return;
		$ExistingRecord = self::select("id")->where("po_no",$po_no)->where("wm_service_id",$wm_service_id)->first();
		if (isset($ExistingRecord->id) && !empty($ExistingRecord->id)) {
			$R_Fields 	= array("po_amount"=>$po_amount,"updated_at"=>date("Y-m-d H:i:s"));
			$UpdatedRow = self::where("id",$ExistingRecord->id)->update($R_Fields);
		} else {
			$AddRecord 					= new self();
			$AddRecord->wm_service_id 	= $wm_service_id;
			$AddRecord->po_no 			= $po_no;
			$AddRecord->po_amount 		= $po_amount;
			$AddRecord->created_at 		= date("Y-m-d H:i:s");
			$AddRecord->updated_at 		= date("Y-m-d H:i:s");
			$AddRecord->save();
		}
	}
}