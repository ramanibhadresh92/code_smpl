<?php

namespace Modules\Mobile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;

class CorporateRegister extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];
        $rules['clscustomer_hdnaction']     = 'required|in:sign_up_mobile';
        $rules['clscustomer_first_name']    = 'required';
        $rules['clscustomer_mobile']        = 'required';
        $rules['clscustomer_password']      = 'required';
        $rules['clscustomer_email']         = 'required';
        $rules['clscustomer_city']          = 'required';
        $rules['clscustomer_zipcode']       = 'required';
       
   
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

    public function messages()
    {
        return [
            'clscustomer_hdnaction.required'    => 'Action is Required.',
            'clscustomer_first_name.required'   => 'Firstname is Required.',
            'clscustomer_mobile.required'       => 'Mobile is required field.',
            'clscustomer_password.required'     => 'Password is required field.',
            'clscustomer_email.required'        => 'Email is required field.',
            'clscustomer_email.email'           => 'Please enter valid email address.',
            'clscustomer_city.required'         => 'City is required field.',
            'clscustomer_zipcode.required'      => 'Pincode is required field.',
        ];
    }
    
     public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (preg_match("/[^a-z0-9\- ]/i",$this->clscustomer_city) != false) {
                $validator->errors()->add('city', 'City is contain only letters, numbers, or dashes.');
            }

            if (preg_match("/[^0-9]/i",$this->zipcode) != false) {
                $validator->errors()->add('zipcode', 'Zipcode should contain only numbers.');
            }
        });
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
            foreach($errors as $e){
                throw new HttpResponseException(response()->json(['tyoe' => TYPE_ERROR,'msg' => $e[0],"data"=>""
            ], VALIDATION_ERROR));
        }
       
    }
}
