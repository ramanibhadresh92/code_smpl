<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\WmClientPurchaseOrders;
class ManagePurchaseOrderSchedule extends FormRequest
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
		return ['record_id' 				=> "required|exists:wm_client_master_po_details,id",
				'plan_qty' 					=> "required|integer",
				'vehicle_no' 				=> "required|integer",
				'vehicle_type_id' 			=> "sometimes|exists:vehicle_type_master,id",
				'para_quality_type_id' 		=> "sometimes|exists:parameter,para_id",
				'plan_end_date' 			=> "required|date_format:Y-m-d"];
	}

	protected function failedValidation(Validator $validator)
	{
		$errors = (new ValidationException($validator))->errors();
		throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""], SUCCESS));
	}

	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			$WmClientPurchaseOrders = WmClientPurchaseOrders::where("id",$this->record_id)->first();
			if (!empty($WmClientPurchaseOrders)) {
				if ((strtotime($this->plan_end_date) < strtotime($WmClientPurchaseOrders->start_date))) {
					$validator->errors()->add('plan_end_date','Plan End Date must be after PO Start Date.');
				}
			}
		});
	}

	public function attributes()
	{
		return [];
	}
}