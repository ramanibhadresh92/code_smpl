<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
class AddPaymentCollection extends FormRequest
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
			'collection_date'   => 'required',
			'vendor_type'       => 'required',
			'achived_amt'    	=> 'required|min:0|not_in:0',
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
			$CURRENT_DATE 	= (isset($this->collection_date) && !empty($this->collection_date)) ? date("Y-m-d",strtotime($this->collection_date)) : "";
			$TODAY 		= date("Y-m-d");
			$START_DATE = date("Y-m")."-01";
			if((date('Y',strtotime($CURRENT_DATE )) != date('Y') || date('m',strtotime($CURRENT_DATE )) != date('m'))){
				$validator->errors()->add('collection_date', 'Allowed only Current Month target.');
			}elseif(date('d',strtotime($CURRENT_DATE )) > date("d") ){
				$validator->errors()->add('collection_date', 'Future date collection not Allowed.');
			}


		});
	}
	public function attributes()
	{
		return [
			"achived_amt" => "Received amount"
		];
	}
}
