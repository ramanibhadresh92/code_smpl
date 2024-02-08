<?php

namespace Modules\Mobile\Http\Requests;

use App\Models\CustomerMaster;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddCustomer extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {


        $rules = [];
        $rules['action']            = 'required';
        if($this->action == 'add') {

            if (isset($this->ctype) && ($this->ctype == CUSTOMER_TYPE_COMMERCIAL || $this->ctype == CUSTOMER_TYPE_INDUSTRIAL)) {
            } else {
                $rules['first_name'] = 'required';
            }

            //$rules['mobile_no']           = 'required|unique:customer_master,mobile_no,'.$this->mobile_no.',customer_id';

            if (isset($this->ctype) && $this->ctype == CUSTOMER_TYPE_BOP) {
                $rules['price_group'] = 'required';
            } else {
                // $rules['email']     = 'required';
                // $rules['mobile_no'] = 'required';
            }
            $rules['address1']      = 'required';
            $rules['cust_group']    = 'required';
        }

        $rules['profile_picture']   = 'sometimes|mimes:jpeg,jpg,png';


       return $rules;
    }

    public function messages()
    {
        return [
            'arr_customer_products.*.required' => 'Product array required'
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

    }
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        foreach($errors as $e){
            throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $e[0],"data"=>""
        ], VALIDATION_ERROR));
        }

    }
}
