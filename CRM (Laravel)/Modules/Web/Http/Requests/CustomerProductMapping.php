<?php

namespace Modules\Web\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\ViewCompanyProductMaster;
class CustomerProductMapping extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_id'       => 'required|exists:customer_master,customer_id',
            'customer_products' => 'required'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (is_array($this->customer_products) && !empty($this->customer_products)) {
                foreach ($this->customer_products as $map_id) {
                   
                    if (count($map_id) == 3) {
                           
                        $product_quality_para_id	= isset($map_id['product_quality_para_id'])  ? $map_id['product_quality_para_id']   : 0;
                        $product_id					= isset($map_id['product_id'])               ? $map_id['product_id']                : 0;
                        $category_id				= isset($map_id['category_id'])              ? $map_id['category_id']               :0;
                        $SelectSql					= ViewCompanyProductMaster::where('id',$product_id)
                                                    ->where('category_id',$category_id)
                                                    ->where('company_product_quality_id',$product_quality_para_id)
                                                    ->where("company_id",Auth()->user()->company_id)
                                                    ->first();
                        if(!$SelectSql) {
                            $validator->errors()->add('product_id', 'Please select valid product.');
                        }
                    } else {
                        $validator->errors()->add('product_id', 'Please select valid product.');
                    }
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
    public function attributes()
    {
        return [
            'customer_id'       => 'customer',
            'customer_products' => 'product'
        ];
    }
    public function messages()
    {
        return[
            'customer_products.required' => 'Please select at-least one product.'
        ];
    }
  
}
