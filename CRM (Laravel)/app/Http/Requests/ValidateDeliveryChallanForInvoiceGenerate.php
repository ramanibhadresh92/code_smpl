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
use App\Models\TransporterDetailsMaster;
use App\Models\WmDispatchProduct;
use App\Models\VehicleDocument;
class ValidateDeliveryChallanForInvoiceGenerate extends FormRequest
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
			$DISPATCH_ID 	= (isset($this->dispatch_id) && !empty($this->dispatch_id)) ? $this->dispatch_id : "";
			if(!empty($DISPATCH_ID)) {
				$DATA = WmDispatch::select("id","bill_from_mrf_id","dispatch_type","client_master_id")->whereIn("id",$DISPATCH_ID)->groupBy("bill_from_mrf_id","dispatch_type","client_master_id")->get()->toArray();
				if(count($DATA) > 1){
					$validator->errors()->add('dispatch_id','Selected Dispatch Data must be the same. Dispatch Type or Billing MRF or Client Are not same');
				}
				$PRODUCT_DATA = WmDispatchProduct::whereIn("dispatch_id",$DISPATCH_ID)->groupBy("product_id")->pluck("product_id")->toArray();
				if(count($PRODUCT_DATA) > 1){
					$validator->errors()->add('product_id', 'Only same product allowed to generate Invoice for this deliery challan');
				}
			}
		});
	}
	public function attributes()
	{
		return [
			"dispatch_id" => "Dispatch Data"
		];
	}

	


}
