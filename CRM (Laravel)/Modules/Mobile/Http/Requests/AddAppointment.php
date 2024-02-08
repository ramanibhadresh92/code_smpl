<?php

namespace Modules\Mobile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddAppointment extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];
        $rules['action_name']       = 'required|in:add_appointment,appointment,auto_add_appointment,get_existing_dustbin';
        $rules['appointment_id']    = 'sometimes|exists:appoinment,appointment_id';
        $rules['customer_id']       = 'required|exists:customer_master,customer_id';
        $rules['vehicle_id']        = 'required|exists:vehicle_master,vehicle_id';
        if ($this->action_name == 'auto_add_appointment') {
            unset($rules['vehicle_id']);
        }

       return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->action_name != 'auto_add_appointment') {
                if(!isset($this->appointment_id) || empty($this->appointment_id)){
                    $validator->errors()->add('appointment_id', 'Appointment id filed is required.');
                }


            }
        });
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        foreach($errors as $e){
            throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $e[0],"data"=>""
        ], VALIDATION_ERROR));
        }
       
    }
}
