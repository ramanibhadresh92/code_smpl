<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmDispatch;

class EprExpenseDetails extends Model
{
	protected 	$table 		= 'epr_expenses_details';
	protected 	$primaryKey = 'id';
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;

	public static function saveEPRExpenseDetails($request,$created_by=0,$updated_by=0)
	{
		$dispatch_id 		= $request->get("dispatch_id");
		$expense_details 	= $request->get("expense_details");
		if (empty($dispatch_id)) return;
		if (!empty($expense_details))
		{
			foreach ($expense_details as $expense_detail)
			{
				$para_expenses_id 	= isset($expense_detail['parameter_id'])?$expense_detail['parameter_id']:0;
				if (!empty($para_expenses_id)) {
					$EprExpenseDetails                      = self::firstOrNew(array('dispatch_id' => $dispatch_id,"para_expenses_id"=>$para_expenses_id));
					if ($EprExpenseDetails->id > 0) {
						$EprExpenseDetails->updated_by 		= $updated_by;
					} else {
						$EprExpenseDetails->created_by 		= $created_by;
						$EprExpenseDetails->updated_by 		= $updated_by;
					}
					$EprExpenseDetails->no_of_labour 		= (isset($expense_detail['selected_labour']) && !empty($expense_detail['selected_labour']))?$expense_detail['selected_labour']:0;
					$EprExpenseDetails->amount 				= (isset($expense_detail['amount_value']) && !empty($expense_detail['amount_value']))?$expense_detail['amount_value']:0;
					$EprExpenseDetails->save();
				}
			}
		}
	}

	public static function getEPRExpenseDetails($dispatch_id=0,$para_expenses_id=0)
	{
		$EprExpenseDetails = "";
		if (empty($dispatch_id)) return $EprExpenseDetails;
		if (empty($para_expenses_id)) return $EprExpenseDetails;
		$EprExpenseDetails = self::where('dispatch_id',$dispatch_id)->where("para_expenses_id",$para_expenses_id)->first();
		return $EprExpenseDetails;
	}
}