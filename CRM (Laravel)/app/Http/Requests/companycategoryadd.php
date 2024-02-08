<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class companycategoryadd extends FormRequest
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
            'category_name'  => 'required|unique:company_category_master|regex:/(^([A-Za-z0-9\s._-]+)(\d+)?$)/u',
            'select_img'     => 'nullable|image|mimes:jpg,png,gif,jpeg',
            'normal_img'     => 'nullable|image|mimes:jpg,png,gif,jpeg',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""
        ], SUCCESS));
    }
}
