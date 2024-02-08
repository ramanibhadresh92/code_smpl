<?php

namespace Modules\Mobile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddFocAppointmentStatus extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];
        $rules['action_name']           = 'required|in:add_FOC_appointment_status,SAVE_FOC_APPOINTMENT';
        $rules['customer_id']           = 'required|exists:customer_master,customer_id';
        $rules['appointment_id']        = 'required|exists:foc_appointment,appointment_id';
        $rules['longitude']             = 'required';
        $rules['latitude']              = 'required';
        $rules['reach']                 = 'required';
        $rules['reach_time']            = 'required';
        $rules['complete_time']         = 'required';


        // $rules['collection_qty']        = '';

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
        // $validator->after(function ($validator) {
        //     if ($this->action_name  == 'USER_VISIBLE') {
               
        //     }
        // });
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
