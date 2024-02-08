<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddAdminUserReading extends FormRequest
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
            'reading'       => 'required|regex:/^[0-9]+$/|max:'.KM_DIGIT_LENGTH,
            'no_of_member'  => 'required|regex:/^[0-9]+$/',
            'vehicle_id'    => 'required|regex:/^[0-9]+$/',
            'created'       => 'required|before:tomorrow',
            'dispatch_qty'  => 'regex:/^[0-9]+$/',
        ];
        
    }
    public function messages()
    {
        return [
            'reading.regex'             => 'Please enter valid KM Reading.',
            // 'reading.max'               => 'The reading length may not be greater than '.KM_DIGIT_LENGTH.' charector',
            'no_of_member.regex'        => 'Please enter valid No Of Member.',
            'dispatch_qty.regex'        => 'Please enter valid dispatch quantity.',
            'vehicle_id.required'       => 'Please enter valid Vehicle Details.',
            'created.required'          => 'Please enter KM Reading Date.',
            'created.before'            => 'Please enter valid Reading Date, you cannot select future reading date.',
            
            
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""
        ], SUCCESS));
    }
}
