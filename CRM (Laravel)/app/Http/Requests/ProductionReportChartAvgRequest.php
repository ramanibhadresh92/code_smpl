<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductionReportChartAvgRequest extends FormRequest
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

		];
	}
	public function messages()
	{
		return [

		];
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
			$month 			= (isset($this->month) && !empty($this->month)) ? $this->month : 0;
			$year 			= (isset($this->year) && !empty($this->year)) ? $this->year : 0;
			$current_month 	= date('Y-m');
			$next_month 	= date('Y-m',strtotime('first day of +1 month'));
			if (strtotime($next_month) < strtotime($year.'-'.$month)) {
				return $validator->errors()->add('month', 'Month and year selection valid until current month and current year');
			} else if(strtotime($current_month) < strtotime($year.'-'.$month)){
				return $validator->errors()->add('month', 'Month and year selection valid until current month and current year');
			}
		});
	}
	public function attributes()
	{
		return [

		];
	}
}
