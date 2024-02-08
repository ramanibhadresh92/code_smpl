<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\ShiftTimingMaster;
use App\Facades\LiveServices;
class ShiftAdd extends FormRequest
{
	
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{

		return [
			'start_date'        => 'required',
			'end_date'          => 'required|after_or_equal:start_date',
			'start_time'        => 'required',
			'end_time'          => 'required',
			
		];
	}
	public function withValidator($validator)
	{

		$validator->after(function ($validator) {
		   
		   	$START_DATE  = date(("Y-m-d"),strtotime($this->start_date)); 
		  	$END_DATE    = date(("Y-m-d"),strtotime($this->end_date)); 
		  	$START_TIME  = date(("H:i:s"),strtotime($this->start_time)); 
		  	$END_TIME    = date(("H:i:s"),strtotime($this->end_time)); 
		  	$ISEXITS = ShiftTimingMaster::where("start_date",$START_DATE)->where("shift_id",$this->shift_id)->where("mrf_id",$this->mrf_id)->first();
		  	if($ISEXITS){
		  		return $validator->errors()->add('start_date', 'Shift already exits in define date.');
		  	}
		  	if($END_DATE == $START_DATE){
		  		if(strtotime($this->end_time) < strtotime($this->start_time)){
		  			return $validator->errors()->add('end_time', 'Shift end time must be greter then or equal to start time');
		  		}
		  	}
		  	
		  	$START_DATE_TIME 	= $START_DATE." ".$START_TIME;
  			$END_DATE_TIME 		= $END_DATE." ".$END_TIME; 

  			$SQL_1 = "SELECT id FROM shift_timing_master
				WHERE  shift_id =".$this->shift_id." and mrf_id=".$this->mrf_id." and startdatetime < '".$START_DATE_TIME."' AND enddatetime > '".$END_DATE_TIME."'";

			$SQL_2 = "SELECT id FROM shift_timing_master
				WHERE shift_id =".$this->shift_id." and mrf_id=".$this->mrf_id." and startdatetime < '".$START_DATE_TIME."' AND enddatetime > '".$START_DATE_TIME."'";

			$SQL_3 = "SELECT id FROM shift_timing_master
				WHERE shift_id =".$this->shift_id." and mrf_id=".$this->mrf_id." and startdatetime < '".$END_DATE_TIME."' AND enddatetime > '".$END_DATE_TIME."'";
			
			$SQL_RAW_1 = \DB::SELECT($SQL_1);
			$SQL_RAW_2 = \DB::SELECT($SQL_2);
			$SQL_RAW_3 = \DB::SELECT($SQL_3);
			
			if(COUNT($SQL_RAW_1) > 0 || COUNT($SQL_RAW_2) > 0 || COUNT($SQL_RAW_3) > 0){
				return $validator->errors()->add('start_time', 'Shift already exits in define time.');
			}
		});
	}
	protected function failedValidation(Validator $validator)
	{
		$errors = (new ValidationException($validator))->errors();
		throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""
		], SUCCESS));
	}
}
