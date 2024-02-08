<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;

class AmbajiPadYatraCollection extends Model
{
	protected 	$table 		= 'ambaji_yatra_collection';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;

	public function saveAmbajiPadYatraDetails($request)
	{
		$ExistingRow 	= self::where("collection_dt",date("Y-m-d",strtotime($request->collection_dt)))->first();
		if (empty($ExistingRow)) {
			$ExistingRow = new self();
		}
		$ExistingRow->collection_dt		= date("Y-m-d",strtotime($request->collection_dt));
		$ExistingRow->collection_qty	= floatval($request->collection_qty);
		$ExistingRow->save();
		return true;
	}
}