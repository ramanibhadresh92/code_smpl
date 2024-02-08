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
use App\Models\CompanyProductMaster;
class InternalTransferAddRequest extends FormRequest
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
		return ["product_type" => "required","transfer_date" => "required"];
	}

	public function messages()
	{
		return [];
	}

	protected function failedValidation(Validator $validator)
	{
		$errors = (new ValidationException($validator))->errors();
		throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""], SUCCESS));
	}


	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			$MRF_ID = Auth()->user()->mrf_user_id;
			if(isset($this->product) && !empty($this->product)) {
				$productData = $this->product;
				if(!is_array($this->product)){
					$productData 	= json_decode($this->product,true);
				}
				$SEND_QTY 		= $productData[0]['sent_qty'];
				$PRODUCT_ID 	= $productData[0]['sent_product_id'];
				$R_PRODUCT_ID 	= $productData[0]['receive_product_id'];
				$DATE 			= date("Y-m-d");
				$CURRENT_STOCK 	= 0;
				if($this->product_type == 1) {
					$FromProduct 	= CompanyProductMaster::select("para_unit_id")->where("id",$PRODUCT_ID)->where("para_status_id",PRODUCT_STATUS_ACTIVE)->first();
					$ToProduct 		= CompanyProductMaster::select("para_unit_id")->where("id",$PRODUCT_ID)->where("para_status_id",PRODUCT_STATUS_ACTIVE)->first();
					if (empty($FromProduct) || empty($ToProduct)) {
						$validator->errors()->add('product_type', 'Please select valid product for transfer.');
					} else if (!isset($FromProduct->para_unit_id) || !isset($ToProduct->para_unit_id)) {
						$validator->errors()->add('product_type', 'Please select valid product for transfer.');
					} else if ($FromProduct->para_unit_id != $ToProduct->para_unit_id) {
						$validator->errors()->add('product_type', 'Selected product is not having same UOM for Internal Transfer Request.');
					} else {
						$PRODUCT_TYPE 	= PRODUCT_PURCHASE;
						$CURRENT_STOCK 	= StockLadger::GetPurchaseProductStock($MRF_ID,$PRODUCT_ID,$DATE);
					}
				}
				if($this->product_type == 2) {
					$CURRENT_STOCK = $PRODUCT_TYPE = PRODUCT_SALES;
					$CURRENT_STOCK = StockLadger::GetSalesProductStock($MRF_ID,$PRODUCT_ID,$DATE);
				}
				if($CURRENT_STOCK < $SEND_QTY) {
					$validator->errors()->add('product_type', 'Current stock is less then transfer quantity.');
				}
			}
		});
	}

	public function attributes()
	{
		return [];
	}
}
