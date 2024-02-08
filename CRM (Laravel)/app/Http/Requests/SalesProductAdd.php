<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SalesProductAdd extends FormRequest
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
			'parent_id'             => 'nullable|exists:wm_product_master,id',
			'hsn_code'              => 'required',
			'title'                 => 'required',
			'description'           => 'required',
			'quality'               => 'in:1,2',

		];
	}



	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			if(!empty($this->product_tagging_id))
			{
				$product = explode(",",$this->product_tagging_id);
				if(count($product) > 1){
					if (in_array(PARA_2D_TAGGING, $product) && in_array(PARA_3D_TAGGING, $product))
					{
						return $validator->errors()->add('product_tagging_id', 'You cannot select 2D / 3D tag for the product, Please select only one of them.');
					}
				}
			}
			if(isset($this->epr_enabled) && !empty($this->epr_enabled) && empty($this->epr_product_type))
			{
				return $validator->errors()->add('epr_product_type', 'EPR Product type is required field.');
			}
		});
	}
	protected function failedValidation(Validator $validator)
	{
		$errors = (new ValidationException($validator))->errors();
		throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""], SUCCESS));
	}
}
