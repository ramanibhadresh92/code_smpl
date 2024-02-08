<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SchedularUpdate extends FormRequest
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
            'customer_id'       => 'required|exists:customer_master,customer_id',
            'appointment_type'  => 'required',
            
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            
            switch($this->appointment_type)
			{
                case "1": 
				{
                    
                    if(!isset($this->appointment_date) || empty($this->appointment_date)){
                        $validator->errors()->add('appointment_date', 'Appointment date filed is required.');
                    }else{
                        $array      = array("1"=>"Monday","2"=>"Tuesday","3"=>"Wednesday","4"=>"Thursday","5"=>"Friday","6"=>"Saturday");
                        $weekday	= date("w",strtotime($this->appointment_date));
                        $weekday 	= $weekday+1;
                        if(!isset($array[$weekday])){
                                $validator->errors()->add('appointment_date', 'Please select valid appointment day, Appointment day must be between Monday to Saturday.');
                        }else{
                            $this->appointment_on = array($weekday);
                        }
                    }
                    break;	
                }
                case "2" : {
                    if(!isset($this->appointment_date) || empty($this->appointment_date)){
                        $validator->errors()->add('appointment_date', 'Appointment date filed is required.');
                    }else{
                        $monthday = date("D",strtotime($this->appoinment_date));
                        $month_day = date("d",strtotime($this->appoinment_date));
                        if(empty($monthday) || strtolower($monthday) == "sun"){
                            $validator->errors()->add('appointment_date', 'Please select valid Appointment Day,Appointment Day must be between Monday to Saturday.');
                        }else{
                            $this->appointment_on = array($month_day);
                        }
                    }
                    break;
                }
                case "3" : {
                    break;
                }
                case "99" : {
                    if(empty($this->appointment_on)) {
                        $validator->errors()->add('appointment_on', 'Please select valid Appointment Day,Appointment Day must be between Monday to Saturday.');
                    }
                }
                default : {
                    $this->appointment_type = 0;
                    // $this->appointment_on   = array();
                }
            }
        });
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""
        ], SUCCESS));
    }
}
