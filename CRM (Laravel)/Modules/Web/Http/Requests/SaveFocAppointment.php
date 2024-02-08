<?php

namespace Modules\Web\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;

class SaveFocAppointment extends FormRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'customer_id'       => 'required|exists:customer_master,customer_id',
			'route'             => 'required|exists:company_parameter,para_id',
			'vehicle_id'        => 'exists:vehicle_master,vehicle_id',
			'app_date_time'     => 'required|Date',

		];
	}

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	public function attributes()
	{
		return [
			'appointment_id'    => 'appointment',
			'route'             => 'route',
			"vehicle_id"        => "vehicle number",
			"customer_id"       => "FOC customer",
			"app_date_time"     => "Appointment date time"
		];
	}
	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			$date = date("Y-m-d H:i:s");
			if ($this->app_date_time < $date) {
				$validator->errors()->add('app_date_time', 'Appointment can not be set for past date.');
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
