<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\CustomerAddress;
class AddCustomerAddress extends FormRequest
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
            'address1'      => "required",
            'landmark'      => "required",
            'city'          => 'required',
            'zipcode'       => 'required',
            // 'gst_no'        => 'required|min:15|max:15',            
            //'status'        => 'required',
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

        });
    }
    public function attributes()
    {
        return [
            // 'app_date_time' => 'appointment date time',
            // 'para_status_id'  => 'status',
            // "vehicle_id" => "vehicle number"
        ];
    }
}
