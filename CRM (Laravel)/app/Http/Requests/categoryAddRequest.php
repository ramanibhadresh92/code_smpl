<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class categoryAddRequest extends FormRequest
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
            'category_name'  => 'required|unique:category_master|regex:/(^([A-Za-z0-9\s._-]+)(\d+)?$)/u',
            'select_img'     => 'nullable|image|mimes:jpg,png,gif,jpeg',
            'normal_img'     => 'nullable|image|mimes:jpg,png,gif,jpeg',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
           $count = \App\Models\CategoryMaster::where("category_name",$this->category_name)
           ->count();
           if($count > 0){
                $validator->errors()->add('category_name', 'The category name has already been taken.');
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
