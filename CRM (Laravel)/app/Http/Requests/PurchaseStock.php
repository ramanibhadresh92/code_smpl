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
class PurchaseStock extends FormRequest
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
            'params.year'         		=> 'required',
            'params.mrf_id'       		=> 'required',
            'params.period'        		=> 'required',
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
           
            if($this['params']['period'] != 4 && empty($this['params']['sub_period'])){
                $validator->errors()->add('params.sub_period', 'Financial year sub period is required.');
            }
		});
    }
    public function attributes()
    {
        return [
        	"params.year" 		=> "Financial year",
        	"params.period" 		=> "Financial year period",
        	"params.sub_period" 	=> "Financial year sub period",
        	"params.mrf_id" 	=> "MRF"
        ];
    }
}
