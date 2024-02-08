<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException
use App\Facades\LiveServices;
class AddProductionReport extends FormRequest
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
			'product_id'        => 'required',
			'production_date'   => 'required',
			
			
		];
	}
	public function withValidator($validator)
	{

		$validator->after(function ($validator) {
		   
		  	if(empty($this->mrf_id)){
		  		return $validator->errors()->add('mrf_id', "You don't have any MRF assign.Please assign MRF first");
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
