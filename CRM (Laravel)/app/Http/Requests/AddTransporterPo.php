<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddTransporterPo extends FormRequest
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
			
			if($this->collection_partner == 1 && COLLECTION_PARTNER_FLAG == 0){
				$validator->errors()->add('collection_partner', 'For Now Collection Partner Functionlity not allowed in LR');		
			}
			if($this->paid_by_party != 1 && $this->rate <= 0 ) {
				$validator->errors()->add('rate', 'Transporter cost is required and must be greater then zero.');
			}
			if(empty($this->po_detail_id) && $this->rate > 0 && $this->paid_by_party != 1 && $this->collection_partner == 0) {
				$validator->errors()->add('rate', 'PO Required when rate is greater then zero');
			}
			$transporter_id  = (isset($this->transporter_id) && !empty($this->transporter_id)) ? $this->transporter_id : 0;
			if(isset($this->po_detail_id) && !empty($this->po_detail_id)){
				if($transporter_id > 0) {
					
				}
			}
			if($this->paid_by_party == 1 && $this->po_detail_id > 0) {
				$validator->errors()->add('paid_by_party', 'Transporter PO Not allowed when transport cost paid by party.');
			}
		});
	}
	public function attributes()
	{
		return [
			
		];
	}
}
