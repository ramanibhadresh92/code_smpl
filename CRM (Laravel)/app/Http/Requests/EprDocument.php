<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\WmDispatch;
class EprDocument extends FormRequest
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
            // 'epr_billt'         => 'nullable|mimes:jpeg,jpg,png,pdf',
            // 'epr_challan'       => 'nullable|mimes:jpeg,jpg,png,pdf',
            // 'epr_way_bridge'    => 'nullable|mimes:jpeg,jpg,png,pdf',
            // 'epr_eway'          => 'nullable|mimes:jpeg,jpg,png,pdf',
          

            
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
        $validator->after(function ($validator) {
         
           
        });
    }
    public function attributes()
    {
        return [
            
        ];
    }
}
