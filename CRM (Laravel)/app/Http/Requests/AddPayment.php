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
class AddPayment extends FormRequest
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
			
			'received_amount'  => 'required',
			'remarks'       => 'required',
			'payment_type'  => 'required',
			'invoice_amount'   => 'required',
			

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
			WmInvoices::where("id",$this->invoice_id)
			if(strtotime($DispatchDate) != strtotime(date("Y-m-d"))) {
				$validator->errors()->add('dispatch_date', 'Dispatch date must not any past or future date.');
			}
			
			
			
		});
	}
	public function attributes()
	{
		return [
			"transporter_po_id" => "transpoter PO"
		];
	}
}
