<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\WmDispatch;
class DirectDispatch extends FormRequest
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
			'client_id'         => 'required|exists:wm_client_master,id',
			'collection_id'     => 'required|exists:appointment_collection,collection_id',
			'vehicle_id'        => 'exists:vehicle_master,vehicle_id',
			"dispatch_date" 	=> 'required|date',
			"unload_date" 		=> 'required|date',
			"dispatch_type" 	=> 'required',
			"collection_by" 	=> 'required|exists:adminuser,adminuserid',
			"department_id" 	=> 'required|exists:wm_department,id',
			"origin"	 		=> 'required',
			"destination" 		=> 'required',


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
			// if ($this->challan_no) {
			// 	$date 		= date('Y-m-d',strtotime($this->dispatch_date));
			// 	$Month 		= date("m",strtotime($date));
			// 	$Year 		= date("Y",strtotime($date));
			// 	if (intval($Month) >= 1 && intval($Month) <= 3) {
			// 		$StartDate 	= ($Year-1)."-04-01";
			// 		$EndDate 	= $Year."-03-31";
			// 		$count 		= WmDispatch::whereBetween("dispatch_date",array($StartDate,$EndDate))->where("challan_no",$this->challan_no)->count();
			// 	} else {
			// 		$StartDate 	= $Year."-04-01";
			// 		$count 		= WmDispatch::where("dispatch_date",">=",$StartDate)->where("challan_no",$this->challan_no)->count();
			// 	}
			// 	if($count > 0){
			// 		$validator->errors()->add('challan_no', 'Duplicate challan no. Please verify.');
			// 	}
			// }else
			$DispatchDate =  (isset($this->dispatch_date) && !empty($this->dispatch_date)) ? date("Y-m-d",strtotime($this->dispatch_date)) : "";
			if(strtotime($DispatchDate) != strtotime(date("Y-m-d"))) {
				$validator->errors()->add('dispatch_date', 'Dispatch date must not any past or future date.');
			}
			if(!empty($this->eway_bill_no)) {
                $wayBill = WmDispatch::where("eway_bill_no",$this->eway_bill_no)->count();
                if($wayBill > 0){
                    $validator->errors()->add('eway_bill_no', 'Duplicate eway bill number. Please verify.');
                }
            }
		});
	}
	public function attributes()
	{
		return [
			"dispatch_type" => "waste type"
		];
	}
}
