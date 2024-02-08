<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;

class AmbajiPadYatraSorting extends Model
{
	protected 	$table 		= 'ambaji_yatra_sorting_details';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;

	public function saveAmbajiPadYatraDetails($request)
	{
		$ExistingRow 	= self::where("sorting_dt",date("Y-m-d",strtotime($request->collection_dt)))->first();
		if (empty($ExistingRow)) {
			$ExistingRow = new self();
		}
		$ExistingRow->sorting_dt		= date("Y-m-d",strtotime($request->collection_dt));
		$ExistingRow->paper				= floatval($request->paper);
		$ExistingRow->plastic			= floatval($request->plastic);
		$ExistingRow->metal				= floatval($request->metal);
		$ExistingRow->mix_waste			= floatval($request->mix_waste);
		$ExistingRow->non_recyclable	= floatval($request->non_recyclable);
		$ExistingRow->save();
		return true;
	}
}