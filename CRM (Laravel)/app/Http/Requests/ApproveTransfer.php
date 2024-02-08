<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\WmTransferMaster;
class ApproveTransfer extends FormRequest
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
            'transfer_id'         => 'required',
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
            if(!empty($this->transfer_id)) {
                $wayBill = WmTransferMaster::find($this->transfer_id);
                if($wayBill){
                    if(!in_array($wayBill->destination_mrf,Auth()->user()->assign_mrf_id)){
                        $validator->errors()->add('transfer_id', 'You can not approve this transfer');  
                    }
                      
                }
            }

            // if(isset($this->sales_product) && !empty($this->sales_product)){
            //    $salesProduct     = json_decode($this->sales_product,true);
            //     if(is_array($salesProduct) && empty($salesProduct)){
            //         foreach($salesProduct as $value){
            //             if($value['received_qty'] <= 0){
            //                 $validator->errors()->add('sales_product', 'Recived quantity required.'); 
            //             }
            //         }
            //     }
            // }
        });
    }
    public function attributes()
    {
        return [
            
        ];
    }
}
