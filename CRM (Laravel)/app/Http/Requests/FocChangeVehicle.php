<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\VehicleDriverMappings;
class FocChangeVehicle extends FormRequest
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
			'vehicle_id'        	=> 'required',
			'appointment_id'    	=> 'required',
			'map_appointment_id'   	=> 'required',
			'remarks'          		=> 'required',
			'route' 				=> 'required',
			'customer_id' 			=> 'required',
		


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
			$collection_by  = VehicleDriverMappings::getVehicleMappedCollectionBy($this->vehicle_id);
			if($collection_by == 0){
				$validator->errors()->add('vehicle_id', 'Driver is not mapped with vehicle.Please Map driver with selected vehicle first.');
			}
		});
	}
	public function attributes()
	{
		return [
			"vehicle_id" => "Vehicle"
		];
	}
}
