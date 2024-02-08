<?php

namespace Modules\Mobile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;

class Login extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'username' => 'nullable|max:255',
            // 'password'=> 'required',
            // "registration_id"=>"required",
            "device_id"=>"required",
            "version"=>"required",
            "device_type" => "sometimes", 
            "device_type" => "required|in:1,2"
        ];
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
            // if (isset($this->face_code) && !empty($this->face_code)) {
            //     if(isset($this->username) && empty($this->username)){
            //         $validator->errors()->add('password', 'The username field is required.');
            //     }
            //     if(isset($this->password) && empty($this->password)){
            //         $validator->errors()->add('password', 'The password field is required.');
            //     }
            // }elseif(isset($this->username) && isset($this->password) && empty($this->username) && empty($this->password)) {
            //     $validator->errors()->add('face_code', 'The FaceId field is required.');
            // }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""
        ], VALIDATION_ERROR));
    }
}
