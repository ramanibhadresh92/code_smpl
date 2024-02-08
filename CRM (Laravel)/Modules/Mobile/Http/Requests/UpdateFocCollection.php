<?php

namespace Modules\Mobile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateFocCollection extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];
        $rules['action_name']           = 'required|in:update_foc_collection';
        $rules['appointment_id']        = 'required|exists:appoinment,appointment_id';
        $rules['quantity']              = 'required';

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
