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
use App\Models\IOTDeviceMaintenanceReasonActions;
use App\Models\WmDepartment;
class BreakdownCloseRequest extends FormRequest
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
		return ['reason_id'			=> 'required|exists:wm_iot_device_maintanance_reason_corrective_action,id',
				'action_id' 		=> 'required|exists:wm_iot_device_maintanance_reason_corrective_action,id',
				'id' 				=> 'required|exists:wm_iot_device_maintanance_details,id',
				'closed_datetime'	=> 'required|date_format:Y-m-d H:i:s'];
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
			$reason_id 					= $this->reason_id;
			$reason_text 				= isset($this->reason_text)?$this->reason_text:"";
			$action_text 				= isset($this->action_text)?$this->action_text:"";
			$action_id 					= $this->action_id;
			$company_id 				= Auth()->user()->company_id;
			$IOTDeviceBreakdownDetails 	= IOTDeviceBreakdownDetails::where("id",$this->id)->where("company_id",$company_id)->first();
			if (empty($IOTDeviceBreakdownDetails)) {
				$validator->errors()->add('reason_id', 'Please select valid breakdown details.');
			} else if ($IOTDeviceBreakdownDetails->status == 1) {
				$validator->errors()->add('reason_id', 'Selected Record is already closed.');
			} else {
				$DeviceDetails 	= IOTDeviceMaintenanceParameters::where("id",$IOTDeviceBreakdownDetails->device_id)->first();
				$GroupID 		= $DeviceDetails->para_equipment_group_id;
				if(isset($this->reason_id) && !empty($this->reason_id) && $this->reason_id == NOTINLIST && empty($reason_text)) {
					$validator->errors()->add('reason_text', 'Please enter reason for breakdown.');
				} else if(isset($this->reason_id) && !empty($this->reason_id) && $this->reason_id != NOTINLIST) {
					$ReasonDetails 	= IOTDeviceMaintenanceReasonActions::select("id")->where(["id"=>$reason_id,
																							"company_id"=>$company_id,
																							"type"=>1,
																							"para_equipment_group_id"=>$GroupID,
																							"status" => 1])->count();
					if(empty($ReasonDetails)) {
						$validator->errors()->add('reason_id', 'Please select valid reason from given options.');
					}
				}
				if(isset($this->action_id) && !empty($this->action_id) && $this->action_id == NOTINLIST && empty($action_text)) {
					$validator->errors()->add('action_text', 'Please enter corrective action taken to resolve the breakdown.');
				} else if(isset($this->action_id) && !empty($this->action_id) && $this->action_id != NOTINLIST) {
					$ActionDetails = IOTDeviceMaintenanceReasonActions::select("id")->where(["id"=>$action_id,
																							"reason_id" => $this->reason_id,
																							"company_id" => $company_id,
																							"status" => 1,
																							"para_equipment_group_id"=>$GroupID,
																							"type"=>2])->count();
					if(empty($ActionDetails)) {
						$validator->errors()->add('action_id', 'Please select valid action taken to resolve the breakdown from given options.');
					}
				}
				if(isset($this->closed_datetime) && !empty($this->closed_datetime)) {
					$CurrentDate 	= strtotime(date("Y-m-d H:i:s"));
					$ClosedDateTime = strtotime($this->closed_datetime);
					if($ClosedDateTime > $CurrentDate) {
						$validator->errors()->add('closed_date', 'Action Date & Time cannot be greater than Current Date & Time.');
					}
				} else {
					$validator->errors()->add('closed_date', 'Action Date & Time cannot be greater than Current Date & Time.');
				}
			}
		});
	}

	public function attributes()
	{
		return ["closed_date" 	=> "Action Date",
				"closed_time" 	=> "Action Time",
				"id" 			=> "Breakdown Record",
				"reason_id" 	=> "Reason",
				"action_id" 	=> "Corrective Action"];
	}
}
