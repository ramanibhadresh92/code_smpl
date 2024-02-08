<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AutoprocessAuditLogCreateRequest extends FormRequest
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
            'autoprocess_info_id'  => 'required',
            
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
           $count = \App\Models\AutoprocessInfoAuditLog::where("autoprocess_info_id",$this->autoprocess_info_id)
           ->where("audited_by",Auth()->user()->company_id)
           ->count();
           if($count > 0){
                $validator->errors()->add('autoprocess_info_id', 'The Autoprocess Info Log Record has already been created.');
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
