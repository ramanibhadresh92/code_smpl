<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\JobWorkMaster;
use App\Models\StockLadger;

class AddJobWorkDetails extends FormRequest
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
	 * @return array`
	 */
	public function rules()
	
	{
		return [
			'jobwork_date'        	=> 'required',
			'jobworker_address'     => 'required',
			'jobworker_state'       => 'required',
			'jobworker_city'        => 'required',
			'jobworker_pincode'     => 'required',
			'mrf_id'        		=> 'required',
			'product_type'        	=> 'required',
			'jobworker_id'        	=> 'required',
		];
	}


	public function withValidator($validator)
	{
		$validator->after(function ($validator) {

			$DATE 	= date("Y-m-d");
			$MRF_ID = $this->mrf_id;
			$product_type = $this->product_type;
			if(!empty($this->product)){
				$PRODUCTION = json_decode($this->product,true);
				$array 		= array();
				foreach($PRODUCTION as $key => $value){
					if (array_key_exists($value['product_id'],$array))
					{
						$array[$value['product_id']] = $value['quantity'] + $array[$value['product_id']] ;
					}else{
						$array[$value['product_id']] = $value['quantity'];
					}
				}
				if(!empty($array)){
					foreach($array as $key => $value){
						$PRODUCT_TYPE 	= ($product_type == 1) ? PRODUCT_PURCHASE : PRODUCT_SALES;
						$PRODUCT_ID  	= $key;
						$SEND_QTY 		= $value;
						if($PRODUCT_TYPE == PRODUCT_PURCHASE){
							$CURRENT_STOCK 	= StockLadger::GetPurchaseProductStock($MRF_ID,$PRODUCT_ID,$DATE);
						}
						if($PRODUCT_TYPE == PRODUCT_SALES){
							$CURRENT_STOCK 	= StockLadger::GetSalesProductStock($MRF_ID,$PRODUCT_ID,$DATE);
						}
						
						if((float)$CURRENT_STOCK < (float)$SEND_QTY){
							$validator->errors()->add('product_type', 'Current stock is less then entered quantity.Please check your product stock.');
						}
					}
				}
			}
		});
	}
	protected function failedValidation(Validator $validation)
	{
		$errors = (new ValidationException($validation))->errors();
		throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""
		], SUCCESS));
	}

}
