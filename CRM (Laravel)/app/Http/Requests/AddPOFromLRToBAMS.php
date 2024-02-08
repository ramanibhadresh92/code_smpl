<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\TransporterPoDetailsMaster;
class AddPOFromLRToBAMS extends FormRequest
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
			// 'epr_billt'         => 'nullable|mimes:jpeg,jpg,png,pdf',
			// 'epr_challan'       => 'nullable|mimes:jpeg,jpg,png,pdf',
			// 'epr_way_bridge'    => 'nullable|mimes:jpeg,jpg,png,pdf',
			// 'epr_eway'          => 'nullable|mimes:jpeg,jpg,png,pdf',
			// 'transporter_po_id' => 'required',
			// 'transporter_po_id' => 'required'

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
			$orange_code = Auth()->user()->orange_code;
			if(empty($orange_code)){
				$validator->errors()->add('id', 'HRMS Code is not updated. Kindly Please update your HRMS Code in LR and BAMS Also.');
			}
			$mrf_id = (isset($this->mrf_id) && !empty($this->mrf_id)) ? $this->mrf_id : 0;
			if(!in_array($mrf_id,MRF_MAPPED_WITH_BAMS)){
				$validator->errors()->add('id', 'MRF is not mapped in BAMS. Kindly Mapped with BAMS First.');
			}
			$ID = (isset($this->id) && !empty($this->id)) ? $this->id : 0;
			if($ID > 0){
				$DATA = TransporterPoDetailsMaster::where("id",$ID)->where("status","!=",0)->first();
				if($DATA){
					$validator->errors()->add('id', 'Already Approved Or Reject From BAMS. Kindly Please refresh the page.');
				}
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
