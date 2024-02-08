<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\ShippingAddressMaster;
use App\Models\AdminUserRights;
use App\Models\GroupRightsTransaction;
class AddService extends FormRequest
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
			'product_list'      => 'required',
			'client_id'         => 'required|exists:wm_client_master,id',
			'mrf_id'     		=> 'required|exists:wm_department,id',
			'billing_address_id'    => 'required|exists:shipping_address_master,id',
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
			if(isset($this->product_list)){
				$salesProduct = json_decode($this->product_list);
				if(is_array($salesProduct) && empty($salesProduct)){
					$validator->errors()->add('product_list', 'Product Required.');
				}
			}
			$billing_address_id =  $this->billing_address_id;
			$USER_ID 			= (isset(Auth()->user()->user_type)) ?  Auth()->user()->user_type : 0;
			$COUNT 				= 0;
			if($USER_ID > 0){
				// $COUNT = AdminUserRights::where("adminuserid",$USER_ID)->where("trnid",ADMIN_RIGHT_WITHOUT_GST_NO_IN_SERVICE)->count();
				// $COUNT = AdminUserRights::where("trnid",ADMIN_RIGHT_WITHOUT_GST_NO_IN_SERVICE)->pluck("adminuserid")->toArray();
				$COUNT = GroupRightsTransaction::where("group_id",$USER_ID)->where("trn_id",ADMIN_RIGHT_WITHOUT_GST_NO_IN_SERVICE)->count();
				// if(!in_array($USER_ID,$COUNT)){
				// 	$COUNT = 0;
				// }
			}
			if($billing_address_id){
				$data = ShippingAddressMaster::find($billing_address_id);
				if((empty($data->gst_no) || empty($data->state_code)) && $COUNT == 0){
					$validator->errors()->add('gst_no', 'Gst No or State is missing in billing_address_id.Please update.');
				}
			}
		});
	}
	public function attributes()
	{
		return [

		];
	}
}
