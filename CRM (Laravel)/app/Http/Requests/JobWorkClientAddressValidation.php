<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\JobworkClientAddress;

class JobWorkClientAddressValidation extends FormRequest
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
     * @return array`
     */
    public function rules()
    
    {
        return [
            'address'               => 'required',
            'city_name'             => 'required',
            'state'                 => 'required',
            'pincode'               => 'required|max:6',
        ];
    }
    protected function failedValidation(Validator $validation)
    {
        $errors = (new ValidationException($validation))->errors();
        throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""
        ], SUCCESS));
    }

}
