<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\StateMaster;
use App\Models\GSTStateCodes;
class CustomerUpdateRequest extends FormRequest
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
            'customer_id'           => 'bail|required|exists:customer_master,customer_id',
            'ctype'                 => 'required|exists:parameter,para_id',
            'salutation'            => 'required|exists:parameter,para_id',
            'first_name'            => 'required',
            'address1'              => 'required',
            'country'               => 'required',
            'zipcode'               => 'required',
            'state'                 => 'required',
            // 'mobile_no'             => 'required',
            'type_of_collection'    => 'required|exists:company_parameter,para_id',
            'collection_site'       => 'required|exists:company_parameter,para_id',
            'cust_group'            => 'required|exists:company_parameter,para_id',
            'cust_status'           => 'required|exists:parameter,para_id',
            // 'is_new_price_group'    => 'required',
            // 'appointment_type'   => 'min:0|in:0,1,2,3,99',
            // 'appointment_date'      => 'required',
            // 'appointment_time'      => 'sometimes',
            // 'appointment_on'        => 'required',
            'para_payment_mode_id'  =>  'required|exists:parameter,para_id',
            'gst_no'                => 'nullable|unique:customer_master,gst_no,'.$this->customer_id.",customer_id",
            'pan_no'                => 'nullable|unique:customer_master,pan_no,'.$this->customer_id.",customer_id",
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            ########## GST NUMBER IS CORRECT OR NOT VALIDATION ##############
            if(isset($this->state)){
                if(empty($this->state)){
                    $validator->errors()->add('state', 'Customer State is required');  
                }
                if(!empty($this->state) && isset($this->gst_no) && !empty($this->gst_no)){
                    $GST_NO = $this->gst_no;
                    $State  = StateMaster::find($this->state);
                    if($State && isset($State->gst_state_code_id) && !empty($State->gst_state_code_id)){
                        $RESULT = CheckValidGST($State->gst_state_code_id,$GST_NO);
                        if(!$RESULT){
                            $validator->errors()->add('gst_no', 'Invalid GST In no Or GST State Code.');    
                        }
                    }
                }
            }
            ########## GST NUMBER IS CORRECT OR NOT VALIDATION ##############
            if ($this->ctype != CUSTOMER_TYPE_BOP ) {
                if (isset($this->mobile_no) && empty($this->mobile_no)) {
                    $validator->errors()->add('mobile_no', 'Mobile number filed is required.');
                }
            }
            /* If appointment type is "ON CALL" then no need to check appointment date and time*/
            if ($this->appointment_type != TYPE_ON_CALL ) {
                if (isset($this->appointment_date) && empty($this->appointment_date)) {
                    $validator->errors()->add('mobile_no', 'Appointment date field is required.');
                }
                if (isset($this->appointment_time) && empty($this->appointment_time)) {
                    $validator->errors()->add('appointment_time', 'Appointment time field is required.');
                }
                if (isset($this->appointment_on) && empty($this->appointment_on)) {
                    $validator->errors()->add('appointment_on', 'Appointment on field is required.');
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
