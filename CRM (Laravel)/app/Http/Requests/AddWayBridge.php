<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class AddWayBridge extends FormRequest
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
            'rst_no'            => 'required|unique:waybridge_master',
            'vehicle_no'        => 'required',
            'mrf_id'            => 'required|exists:wm_department,id',
            'client_name'       => 'required',
            "gross_weight"      => 'required|between:0,99.99',
            "tare_weight"       => 'required|between:0,99.99',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->tare_weight > $this->gross_weight) {
                $validator->errors()->add('tare_weight', 'Tare weight can not be grater then gross weight.');
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
