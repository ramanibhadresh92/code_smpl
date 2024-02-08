<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\GSTStateCodes;
use App\Models\WmClientMaster;

class ClientAdd extends FormRequest
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
			'client_name'       => 'required',
			'address'           => 'required',
			'gst_state_code'    => 'nullable|exists:GST_STATE_CODES,id',
			'gstin_no'          => "nullable|unique:wm_client_master,gstin_no",
			'pan_no'            => "nullable|unique:wm_client_master,pan_no"
		];
	}

	public function withValidator($validator)
	{
		$validator->after(function ($validator) {

			/** Added By Kalpak @Since 2023-06-27 validate duplicate Net Suit Code */
			if (isset($this->net_suit_code) && !empty($this->net_suit_code)) {
				if (isset($this->id) && !empty($this->id)) {
					$ExistingRow = WmClientMaster::select("id")->where("id","!=",$this->id)->where("net_suit_code",$this->net_suit_code)->count();
				} else {
					$ExistingRow = WmClientMaster::select("id")->where("net_suit_code",$this->net_suit_code)->count();
				}
				if (!empty($ExistingRow)) {
					$validator->errors()->add('net_suit_code', 'Duplicate Netsuit Code. Client already exits with same code.');
				}
			}
			/** Added By Kalpak @Since 2023-06-27 validate duplicate Net Suit Code */

			if(isset($this->gstin_no) && !empty($this->gstin_no)) {
				if(isset($this->gst_state_code) && empty($this->gst_state_code)){
					$validator->errors()->add('gst_state_code', 'GST State code is required.');
				}
				$GST_NO = $this->gstin_no;
				$RESULT = CheckValidGST($this->gst_state_code,$GST_NO);
				if(!$RESULT){
					$validator->errors()->add('gst_state_code', 'Invalid GST In no Or GST State Code.');
				}
			}
		});
	}
	protected function failedValidation(Validator $validator)
	{
		$errors = (new ValidationException($validator))->errors();
		throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""], SUCCESS));
	}
}
