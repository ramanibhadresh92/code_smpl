<?php


namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class categoryEditRequest extends FormRequest
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
            'category_id'    => 'required|exists:category_master,category_id',
            'category_name'  => 'required|regex:/(^([A-Za-z0-9\s._-]+)(\d+)?$)/u|unique:category_master,category_name,'.$this->category_id.',category_id',
            'select_img'     => 'nullable|image',
            'normal_img'     => 'nullable|image',
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
