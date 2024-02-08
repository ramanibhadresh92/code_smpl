<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class AppointmentCollectionUpdateRequest extends FormRequest
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
            'appointment_id'             =>  'required|exists:appoinment,appointment_id',
            'collection_id'              =>  'required|exists:appointment_collection,collection_id',
            'category_id'                =>  'required|exists:company_category_master,id',
            'product_id'                 =>  'required|exists:company_product_master,id',
            'company_product_quality_id' =>  'required|exists:company_product_quality_parameter,company_product_quality_id',
            'para_quality_price'         =>  'required|regex:/^[-+]?[0-9]*\.?[0-9]+$/',
            'quantity'                   =>  'required|gt:0',
        ];
    }
    public function attributes()
    {
        return [
            'appointment_id'             => 'appointment',
            'collection_id'              => 'collection',
            "category_id"                => "category",
            'product_id'                 => 'product',
            'company_product_quality_id' => 'product quality',
            'para_quality_price'         => 'product quality price',
            'quantity'                   => 'Collection quantity'
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // if ($this->quantity * $this->para_quality_price == 0 ) {
            //     $validator->errors()->add('para_quality_price', 'Collection Price is required Field.');
            // }
            // if ($this->para_quality_price < 0 ) {
            //     $validator->errors()->add('para_quality_price', 'Collection Price is required Field.');
            // }
            if ($this->para_quality_price < 0 ) {
                $validator->errors()->add('para_quality_price', 'Collection Price not less then zero.');
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
