<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\WmSalesToPurchaseMapping;
class CompanyProductUpdate extends FormRequest
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
        return  [
            'id'                        => 'bail|required|exists:company_product_master,id',
            'category_id'               => 'required',
            'para_status_id'            => 'required',
            'para_unit_id'              => 'required',
            'co2_saved'                 => 'required',
            'name'                      => 'required',
            'parameter_name'            => 'required',
            'para_group_id'             => 'required',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if(isset($this->stortable) && $this->sortable == 0)
            {
                $count = WmSalesToPurchaseMapping::where("purchase_product_id",$this->id)->count();
                if(count($count) > 1){
                    return $validator->errors()->add('id', 'Product Mapped with multiple Sales Product.Please map with only single sales product.');
                }
            }
            if(!empty($this->product_tagging_id))
            {
                $product = explode(",",$this->product_tagging_id);
                if(count($product) > 1){
                    return $validator->errors()->add('product_tagging_id', 'Only One product tagging will allow.');
                }
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
