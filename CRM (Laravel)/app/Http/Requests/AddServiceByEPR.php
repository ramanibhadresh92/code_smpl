<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\WmServiceProductMaster;
use App\Models\WmServiceMaster;
use App\Models\WmServiceProductMapping;

class AddServiceByEPR extends FormRequest
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
		return ['product_list' 			=> 'required',
				'brand_lr_code' 		=> 'required|exists:wm_client_master,code',
				'existing_invoice_no' 	=> 'nullable|exists:wm_service_master,serial_no',
				'buyer_no' 				=> 'required'];
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
			$totalQty = 0;
			if(isset($this->product_list) && !empty($this->product_list)) {
				$salesProduct = $this->product_list;
				if(is_array($salesProduct) && empty($salesProduct)) {
					$validator->errors()->add('product_list', 'Atleast one product is required.');
				} else {
					$Valid = true;
					foreach($salesProduct as $Product) {
						$Record = WmServiceProductMaster::select('id')->where('id',$Product['product_id'])->where('status',PARA_STATUS_ACTIVE)->first();
						if (!isset($Record->id) || empty($Record->id)) {
							$validator->errors()->add('product_list', 'Please select valid product.');
							$Valid = false;
						}
						if (preg_match("/[^0-9\.]/",$Product['qty']) || empty($Product['qty'])) {
							$validator->errors()->add('product_list', 'Product quantity must be decimal value and should be greater than zero.');
							$Valid = false;
						}
						if (preg_match("/[^0-9\.]/",$Product['rate']) || empty($Product['rate'])) {
							$validator->errors()->add('product_list', 'Product rate must be decimal value and should be greater than zero.');
							$Valid = false;
						}
						$totalQty += (isset($Product['qty']) && !empty($Product['qty'])?$Product['qty']:0);
						if (!$Valid) break;
					}
				}
			}
			if (isset($this->dispatch_doc_no) && !empty($this->dispatch_doc_no)) {
				$wma_id = isset($this->wma_id)?$this->wma_id:0;
				$Record = WmServiceMaster::select('id')
										->where('dispatch_doc_no',$this->dispatch_doc_no)
										->where('epr_wma_id',$wma_id)
										->whereIn('approval_status',[ORDER_STATUS_PENDING,PARA_STATUS_ACTIVE])
										->first();
				if (isset($Record->id) && !empty($Record->id)) {
					$validator->errors()->add('dispatch_doc_no', 'Service Invoice is already generated for same Doc No.');
					$Valid = false;
				}
			}
			if (isset($this->existing_invoice_no) && !empty($this->existing_invoice_no)) {
				$Record = WmServiceMaster::select('id')
											->where('serial_no',$this->existing_invoice_no)
											->whereIn('approval_status',[ORDER_STATUS_PENDING,PARA_STATUS_ACTIVE])
											->first();
				if (isset($Record->id) && !empty($Record->id)) {
					$TotalQty = WmServiceProductMapping::select("quantity")->where("service_id",$Record->id)->sum("quantity");
					if ($TotalQty != $totalQty) {
						$validator->errors()->add('existing_invoice_no', 'Total quantity mis-matched with existing Invoice No.');
						$Valid = false;
					}
				} else {
					$validator->errors()->add('existing_invoice_no', 'Invalid Invoice No.');
					$Valid = false;
				}
			}
		});
	}
	
	public function attributes()
	{
		return [];
	}
}