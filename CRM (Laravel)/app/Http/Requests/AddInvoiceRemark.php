<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\WmDispatch;

class AddInvoiceRemark extends FormRequest
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
		return ['invoice_no'        => 'required|exists:wm_dispatch,challan_no',
				'remark'       		=> 'required',
				'reason'    		=> 'required'];
	}
	public function messages()
	{
		return ["invoice_no.required"=>"Invoice No is required.",
				"invoice_no.exists"=>"Please enter valid Invoice No."];
	}

	protected function failedValidation(Validator $validator)
	{
		$errors = (new ValidationException($validator))->errors();
		throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""], SUCCESS));
	}


	public function withValidator($validator)
	{
		
	}
	public function attributes()
	{
		return [];
	}
}
