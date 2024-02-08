<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddAsset extends FormRequest
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
			'product_list'      => 'required',
			'from_mrf_id'       => 'required|exists:wm_department,id',
			'to_mrf_id'     	=> 'required|exists:wm_department,id',
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
			if(isset($this->product_list)){
				$salesProduct = json_decode($this->product_list);
				if(is_array($salesProduct) && empty($salesProduct)){
					$validator->errors()->add('product_list', 'Product Required.');
				}
			}
			if($this->from_mrf_id ==  $this->to_mrf_id){
				$validator->errors()->add('from_mrf_id', 'You can not transfer asset in same MRF.');
			}
		});
	}
	public function attributes()
	{
		return [

		];
	}
}
