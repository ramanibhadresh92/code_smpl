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

class CustomerSave extends FormRequest
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
        $rules['customer_id']       = 'required|exists:customer_master,customer_id';
        if($this->action == 'save_customer') {

            if (isset($this->ctype) && ($this->ctype == CUSTOMER_TYPE_COMMERCIAL || $this->ctype == CUSTOMER_TYPE_INDUSTRIAL)) {
            } else {
                $rules['first_name'] = 'required';
            }

            //$rules['mobile_no']           = 'required|unique:customer_master,mobile_no,'.$this->mobile_no.',customer_id';

            if (isset($this->ctype) && $this->ctype == CUSTOMER_TYPE_BOP) {
                $rules['price_group'] = 'required';
            } else {
                if (empty($this->add_from_app) && empty($this->price_group_title)) $rules['price_group_title'] = 'required';
                if (empty($this->add_from_app) && empty($this->arr_price_group_products)) $rules['arr_price_group_products'] = 'required';
                if (empty($this->mobile_no)) {
                    $rules['mobile_no'] = 'required';
                }
            }
            $rules['address1'] = 'required';
            $rules['cust_group'] = 'required';
        }

        if($this->action == 'save_customer_images') {
            $rules['profile_picture'] = 'required|mimes:jpeg,jpg,png';
        }

        if($this->action == 'save_customer_product'){
            $rules['arr_customer_products.*'] = 'required';
        }



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
        if($this->action == 'save_customer') {
            $validator->after(function ($validator) {
                if ($this->vat == "Y" && (empty($this->vat_val) || preg_match("/^[0-9\.]$/i", $this->vat_val) != false)) {
                    $validator->errors()->add('vat_val', 'Vat (%) is required field and must be valid numbers only.');
                }

                if (!empty($this->mobile_no) && $this->ctype == CUSTOMER_TYPE_BOP) {
                    $Mobile_Nos = explode(",", $this->mobile_no);
                    foreach ($Mobile_Nos as $Mobile_No) {
                        if ($this->ctype != CUSTOMER_TYPE_BOP) {
                            if (!empty($Mobile_No) && !isValidMobile($Mobile_No)) {
                                $validator->errors()->add('mobile_no', 'The Mobile No is Required');
                            } elseif (!empty($Mobile_No)) {
                                if (CustomerMaster::where('mobile_no', 'like', '%' . $this->mobile_no . '%')->where('customer_id', '!=', $this->customer_id)->first()) {
                                    $validator->errors()->add('mobile_no', 'Customer already exists with Mobile' . $this->mobile_no);
                                }
                            }
                        } else {
                            if (!empty($Mobile_No) && !isValidMobile($Mobile_No)) {
                                $validator->errors()->add('mobile_no', 'The Mobile No is Required');
                                break;
                            }
                        }
                    }
                }

            });
        }
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
