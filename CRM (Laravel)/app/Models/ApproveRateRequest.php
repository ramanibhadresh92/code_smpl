<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApproveRateRequest extends FormRequest
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
            //
        ];
    }

    public function messages()
    {
        return [
            "challan_no"        => 'required|unique:wm_dispatch,challan_no,'.$this->dispatch_id.',id',
            "eway_bill_no"      => 'unique:wm_dispatch,eway_bill_no,'.$this->dispatch_id.',id',
            "total_price"       => 'required'
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
            if ($this->total_price > 50000 && empty($this->eway_bill_no)) {
                $validator->errors()->add('eway_bill_no', 'Eway bill no is required.');
            }
        });
    }
    public function attributes()
    {
        return [
            
        ];
    }
}
