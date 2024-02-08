<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\DailyProjectionPlan;
use App\Models\DailyProjectionPlanDetails;
use App\Models\WmDepartment;
use App\Models\WmClientMaster;
class AddDailyProjectionPlanDetails extends FormRequest
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
		return ['wm_daily_projection_plan_id' 	=> "required|exists:wm_daily_projection_plan,id",
				'projection_plan_list'			=> "required"];
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

			if (!empty($this->projection_plan_list)) {
				$projection_plan_lists = json_decode($this->projection_plan_list);
				if (!empty($projection_plan_lists)) {
					foreach ($projection_plan_lists as $projection_plan_list) {
						$valid = true;
						if (!in_array($projection_plan_list->plan_type,array(1,2))) {
							$validator->errors()->add('plan_type','Please select valid plan type.');
							$valid = false;
						} else if ($projection_plan_list->plan_type == 1) {
							$WmClientMaster = WmClientMaster::where("id",$projection_plan_list->plan_to)->first();
							if (!$WmClientMaster) {
								$validator->errors()->add('plan_to','Please select valid Client.');
								$valid = false;
							}
						} else if ($projection_plan_list->plan_type == 2) {
							$WmDepartment = WmDepartment::where("id",$projection_plan_list->plan_to)->first();
							if (!$WmDepartment) {
								$validator->errors()->add('plan_to','Please select valid MRF.');
								$valid = false;
							}
						} else if (!preg_match("/[^0-9\.]/",$projection_plan_list->qty) || strlen($projection_plan_list->qty) > 6|| strlen($projection_plan_list->qty) < 1) {
							$validator->errors()->add('qty','Please enter valid Quantity.');
							$valid = false;
						} else if (!preg_match("/[^0-9\.]/",$projection_plan_list->rate) || strlen($projection_plan_list->rate) > 6|| strlen($projection_plan_list->rate) < 1) {
							$validator->errors()->add('qty','Please enter valid Rate.');
							$valid = false;
						}
						if (!$valid) break;
					}
				} else {
					$validator->errors()->add('plan_to','At-least one details required.');
				}
			} else {
				$validator->errors()->add('plan_to','At-least one details required.');
			}
		});
	}

	public function attributes()
	{
		return [];
	}
}