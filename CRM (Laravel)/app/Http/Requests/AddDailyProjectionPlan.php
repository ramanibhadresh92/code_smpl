<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\DailyProjectionPlan;

class AddDailyProjectionPlan extends FormRequest
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
				'mrf_id' 			=> "required|exists:wm_department,id",
				'projection_date' 	=> "required|date_format:Y-m-d",
				'plan_data' 		=> "required"];
	}

	protected function failedValidation(Validator $validator)
	{
		$errors = (new ValidationException($validator))->errors();
		throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""
		], SUCCESS));
	}

	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			$plandate 	= strtotime($this->projection_date);
			$today 		= strtotime(date("Y-m-d"));
			if($plandate < $today) {
				$validator->errors()->add('month','Projection cannot be added for past date.');
			}
			$ProjectionPlan = DailyProjectionPlan::where("projection_date",$this->projection_date)
								->where("product_id",$this->product_id)
								->where("mrf_id",$this->mrf_id)
								->first();
			if (isset($ProjectionPlan->id) && !empty($ProjectionPlan->id)) {
				$validator->errors()->add('product_id','Duplicate record for same product for same MRF.');
			}
		});
	}

	public function attributes()
	{
		return [];
	}
}