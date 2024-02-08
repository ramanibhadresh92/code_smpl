<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\ProjectionPlan;
class UpdateProjectionPlan extends FormRequest
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
		return ['id' 			=> 'required|exists:wm_projection_plan,id',
				'mrf_id' 		=> "required|exists:wm_department,id",
				'month' 		=> "required|min:1|max:2",
				'year' 			=> "required|min:4|max:4",
				'no_of_days' 	=> "required",
				'plan_data' 	=> "required"];
	}

	protected function failedValidation(Validator $validator)
	{
		$errors = (new ValidationException($validator))->errors();
		throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""], SUCCESS));
	}

	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			$ProjectionPlan = ProjectionPlan::where("id",$this->id)->first();
			if ($ProjectionPlan->month != $this->month || $ProjectionPlan->year != $this->year) {
				$plandate 	= strtotime($this->year."-".$this->month."-".date("d",strtotime("now")));
				$today 		= strtotime(date("Y-m-d"));
				if($plandate < $today) {
					$validator->errors()->add('month','Projection cannot be added for past date.');
				}
			}
			$ProjectionPlan = ProjectionPlan::whereNotIn("id",array($this->id))
								->where("month",$this->month)
								->where("year",$this->year)
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