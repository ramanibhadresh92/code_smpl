<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\WmProductClientPriceMaster;
class AddProductClientPrice extends FormRequest
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
            'client_id'         => 'required|exists:wm_client_master,id',
            // 'product_id'        => 'required|exists:wm_product_master,id',
            "rate_date"       	=> 'required|date',
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
        // $validator->after(function ($validator) {
        // 	$DATA = WmProductClientPriceMaster::where("client_id",$this->client_id)->where("product_id",$this->product_id)->where("rate_date",date("Y-m-d",strtotime($this->rate_date)))->where("rate",$this->rate)->count();
        //     if($DATA > 0){
        //     	$validator->errors()->add('product_id', 'Rate already added for this product');    
        //     }
            
        // });
    }
    public function attributes()
    {
        return [
            "product_id" => "sales product"
        ];
    }
}

