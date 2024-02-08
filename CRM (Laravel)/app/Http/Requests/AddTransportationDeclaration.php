<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddTransportationDeclaration extends FormRequest
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
	//'vehicle_id'    		=> 'required|exists:vehicle_master,vehicle_id',
	// 'vehicle_no'    		=> 'required',
	public function rules()
	{
		return [
			'vehicle_number'		=> 'required',
			'source'     			=> 'required',
			'destination'			=> 'required',
			'type_of_transportation'=> 'required',
			'transporter'			=> 'required'
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
		throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""], SUCCESS));
	}


	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			
			
			
		});
	}

	public function attributes()
	{
		return [];
	}
}
