<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\LocationMaster;

class AddLocation extends FormRequest
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
			'city'		=> 'required',
			'state_id'	=> 'required',
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

			$cityName	= (isset($this->city) && !empty($this->city) ? $this->city : strtolower($this->city));
			$location_id= (isset($this->location_id) && !empty($this->location_id) ? $this->location_id : 0);
			
			if(!empty($location_id)){
				$city_data	= LocationMaster::where('state_id',$this->state_id)->where('location_id','!=',$location_id)->where('city',$cityName)->count();
				if($city_data > 0){
					$validator->errors()->add('city', 'City already exists');
				}
			} else {
				$city_data	= LocationMaster::where('state_id',$this->state_id)->where('city',$cityName)->count();
				if($city_data > 0){
					$validator->errors()->add('city', 'City already exists');
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
