<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\StockLadger;
class AddTransfer extends FormRequest
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
			'destination_mrf'   => 'required|exists:wm_department,id|not_in:'.Auth()->user()->mrf_user_id,
			'transfer_date'     => 'required',
			'vehicle_id'        => 'required|exists:vehicle_master,vehicle_id',
			'sales_product'     => 'required',
			'product_type'      => 'required',
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
			if(empty(Auth()->user()->mrf_user_id)) {
				$validator->errors()->add('destination_mrf', 'Default MRF is not assign.Please assign default MRF first.');
			}
			if(!empty($this->destination_mrf) && $this->destination_mrf == 11) {
				$validator->errors()->add('destination_mrf', 'Transfer to PIRANA is stopped.');
			}
			$DATE     = date("Y-m-d");
			$MRF_ID = Auth()->user()->mrf_user_id;
			if(!empty($this->sales_product)) {
				$PRODUCTION = json_decode($this->sales_product,true);
				$array      = array();
				foreach($PRODUCTION as $key => $value) {
					if($this->product_type == 1) {
						if (array_key_exists($value['product_id'],$array)) {
							$array[$value['product_id']] = $value['quantity'] + $array[$value['product_id']] ;
						} else {
							$array[$value['product_id']] = $value['quantity'];
						}
					} else {
						if (array_key_exists($value['sales_product_id'],$array)) {
							$array[$value['sales_product_id']] = $value['quantity'] + $array[$value['sales_product_id']] ;
						} else {
							$array[$value['sales_product_id']] = $value['quantity'];
						}
					}
				}
				if(!empty($array)) {
					foreach($array as $key => $value) {
						if($this->product_type == 1) {
							$PRODUCT_TYPE   = PRODUCT_PURCHASE;
							$PRODUCT_ID     = $key;
							$SEND_QTY       = $value;
							$CURRENT_STOCK  = StockLadger::GetPurchaseProductStock($MRF_ID,$PRODUCT_ID,$DATE);
						} else {
							$PRODUCT_TYPE   = PRODUCT_SALES;
							$PRODUCT_ID     = $key;
							$SEND_QTY       = $value;
							$CURRENT_STOCK  = StockLadger::GetSalesProductStock($MRF_ID,$PRODUCT_ID,$DATE);
						}
						if((float)$CURRENT_STOCK < (float)$SEND_QTY) {
							$validator->errors()->add('product_type', 'Current stock is less then transfer quantity.Please check your product stock.');
						}
					}
				}
			}
		});
	}

	public function attributes()
	{
		return [];
	}
}
