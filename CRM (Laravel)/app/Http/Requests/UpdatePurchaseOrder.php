<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\WmClientPurchaseOrders;
class UpdatePurchaseOrder extends FormRequest
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
		return ['record_id' 				=> 'required|exists:wm_client_master_po_details,id',
				'mrf_id' 					=> "required|exists:wm_department,id",
				'wm_client_id' 				=> "required|exists:wm_client_master,id",
				'wm_client_shipping_id' 	=> "required|exists:shipping_address_master,id",
				'wm_product_id' 			=> "required|exists:wm_product_master,id",
				'vehicle_type_id' 			=> "required|exists:vehicle_type_master,id",
				'start_date' 				=> "required|date_format:Y-m-d",
				'end_date' 					=> "required|date_format:Y-m-d|after_or_equal:start_date"];
	}

	protected function failedValidation(Validator $validator)
	{
		$errors = (new ValidationException($validator))->errors();
		throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""], SUCCESS));
	}

	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			$ClientPurchaseOrder 	= new WmClientPurchaseOrders;
			$WmClientPurchaseOrders = WmClientPurchaseOrders::where("id",$this->record_id)->first();
			if ($WmClientPurchaseOrders->start_date != $this->start_date) {
				$plandate 	= strtotime($this->start_date);
				$today 		= strtotime(date("Y-m-d"));
				if($plandate < $today) {
					$validator->errors()->add('start_date','Start Date cannot be added for past date.');
				}
			} else if ($WmClientPurchaseOrders->end_date != $this->end_date) {
				$plandate 	= strtotime($this->end_date);
				$today 		= strtotime(date("Y-m-d"));
				if($plandate < $today) {
					$validator->errors()->add('end_date','End Date cannot be added for past date.');
				}
			}
			$WmClientPurchaseOrders = WmClientPurchaseOrders::whereNotIn("id",array($this->id))
										->where("wm_client_id",$this->wm_client_id)
										->where("wm_product_id",$this->wm_product_id)
										->where("mrf_id",$this->mrf_id)
										->whereNotIn("status",[$ClientPurchaseOrder->CANCELLED,$ClientPurchaseOrder->REJECTED])
										->get();
			if (!empty($WmClientPurchaseOrders)) {
				foreach ($WmClientPurchaseOrders as $WmClientPurchaseOrder) {
					if ((strtotime($this->start_date) >= strtotime($WmClientPurchaseOrder->start_date)) && (trtotime($this->start_date) <= strtotime($WmClientPurchaseOrder->end_date))) {
						$validator->errors()->add('wm_product_id','Duplicate record for same product for same MRF for same client for selected date range.');
						break;
					} else if ((strtotime($this->end_date) >= strtotime($WmClientPurchaseOrder->start_date)) && (trtotime($this->end_date) <= strtotime($WmClientPurchaseOrder->end_date))) {
						$validator->errors()->add('wm_product_id','Duplicate record for same product for same MRF for same client for selected date range.');
						break;
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