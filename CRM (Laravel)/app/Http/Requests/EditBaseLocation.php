<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;

class EditBaseLocation extends FormRequest
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
            "id"                => 'required|exists:base_location_master,id',
            'base_location_name'=> 'required',
            'city'              => "required",
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
        
    }
    public function attributes()
    {
        return [
            'id'                 => 'Base location id'
            'base_location_name' => 'Base location name',
            'city'               => 'Base Location city'
        ];
    }
}
