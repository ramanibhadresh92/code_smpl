<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\ProjectionPlan;
use App\Models\ProjectionPlanDetails;
use App\Models\WmDepartment;
use App\Models\WmClientMaster;
class UpdateProjectionPlanDetails extends FormRequest
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
		return ['id' 					=> "required|exists:wm_projection_plan_details,id",
				'wm_projection_plan_id' => "required|exists:wm_projection_plan,id",
				'plan_type' 			=> "required|integer|min:1|max:1|between:1,2",
				'plan_to' 				=> "required|integer",
				'qty'					=> "required|min:1|max:6|regex:/^[0-9\.]*$/",
				'rate'					=> "required|min:1|max:6|regex:/^[0-9\.]*$/"];
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
			if ($this->plan_type == 1) {
				$WmClientMaster = WmClientMaster::where("id",$this->plan_to)->first();
				if (!$WmClientMaster) {
					$validator->errors()->add('plan_to','Please select valid Client.');
				}
			} else {
				$WmDepartment = WmDepartment::where("id",$this->plan_to)->first();
				if (!$WmDepartment) {
					$validator->errors()->add('plan_to','Please select valid MRF.');
				}
			}
		});
	}

	public function attributes()
	{
		return [];
	}
}