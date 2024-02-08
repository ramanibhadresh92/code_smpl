<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\IOTDeviceMaintenanceParameters;
use App\Models\IOTDeviceBreakdownDetails;
use App\Models\WmDepartment;
class BreakdownAddRequest extends FormRequest
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
		return ['mrf_id'			=> 'required|exists:wm_department,id',
				'device_id' 		=> 'required|exists:wm_iot_device_maintanance_parameters,id',
				'breakdown_datetime'=> 'required|date_format:Y-m-d H:i:s'];
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
			$device_id 		= $this->device_id;
			$mrf_id 		= $this->mrf_id;
			$company_id 	= Auth()->user()->company_id;
			if(isset($this->device_id) && !empty($this->device_id)) {
				$DeviceDetails = IOTDeviceMaintenanceParameters::select("id")->where(["id"=>$device_id,"mrf_id" => $mrf_id,"company_id" => $company_id])->count();
				if(empty($DeviceDetails)) {
					$validator->errors()->add('device_id', 'Please select valid device from the given options.');
				}
			}
			if(isset($this->mrf_id) && !empty($this->mrf_id)) {
				$MRFDetails = WmDepartment::select("id")->where(["id"=>$mrf_id,"company_id" => $company_id,"iot_enabled" => 1])->count();
				if(empty($MRFDetails)) {
					$validator->errors()->add('mrf_id', 'Selected plant is not enabled with IOT Devices.');
				}
			}
			if(isset($this->breakdown_datetime) && !empty($this->breakdown_datetime)) {
				$CurrentDate 	= strtotime(date("Y-m-d H:i:s"));
				$BreakdownDate 	= strtotime($this->breakdown_datetime);
				if($BreakdownDate > $CurrentDate) {
					$validator->errors()->add('breakdown_date', 'Breakdown Date & Time cannot be greater than Current Date & Time.');
				}
			} else {
				$validator->errors()->add('breakdown_date', 'Breakdown Date & Time cannot be greater than Current Date & Time.');
			}
		});
	}

	public function attributes()
	{
		return ["breakdown_date" 			=> "Breakdown Date",
				"breakdown_time" 			=> "Breakdown Time",
				"mrf_id" 					=> "MRF",
				"device_id" 				=> "IOT Device"];
	}
}
