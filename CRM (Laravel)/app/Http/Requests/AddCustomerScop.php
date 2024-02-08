<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddCustomerScop extends FormRequest
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
			'ctype'				=> 'required',
			'first_name'		=> 'required',
			'mobile_no'			=> 'required',
			'address1'			=> 'required',
			'city'				=> 'required',
			'zipcode'			=> 'required',
			'state'				=> 'required',
			'mobile_no'			=> 'required|min:10|max:10',
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
			if((empty($this->gst_no) && !empty($this->hasFile('gst_doc')))){

				$validator->errors()->add('gst_doc', 'GST No. Required.');
			}
			########## GST NUMBER IS CORRECT OR NOT VALIDATION ##############
            if(isset($this->state)){
                if(empty($this->state)){
                    $validator->errors()->add('state', 'Customer State is required');  
                }
                if(!empty($this->state) && isset($this->gst_no) && !empty($this->gst_no)){
                    $GST_NO = $this->gst_no;
                    if(!preg_match("/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[0-9]{1}[A-Z]{1}[A-Z0-9]{1}$/", $GST_NO)){
						    $validator->errors()->add('gst_no', 'Invalid GST Number.');  
						}else{
	                    $State  = StateMaster::find($this->state);
	                    if($State && isset($State->gst_state_code_id) && !empty($State->gst_state_code_id)){
	                        $RESULT = CheckValidGST($State->gst_state_code_id,$GST_NO);
	                        if(!$RESULT){
	                            $validator->errors()->add('gst_no', 'Invalid GST Number Or GST State Code.');    
	                        }else{
	                        	if(((isset($this->pan_no) && (!empty($this->pan_no))))){
	                        		$RESULT = CheckValidGSTwithPAN($this->pan_no,$GST_NO);	
		                        	if(!$RESULT){
		                        		$validator->errors()->add('gst_no', 'GST No. Does Not Match With PAN No.');    
		                        	}
	                        	} 	
	                        }

	                    }

                	}
                }
            }
            ########## GST NUMBER IS CORRECT OR NOT VALIDATION ##############

			if((!empty($this->gst_no) && empty($this->hasFile('gst_doc')))){

				$validator->errors()->add('gst_doc', 'GST Document Required.');
			}
			if(((isset($this->pan_no) && (!empty($this->pan_no))) && empty($this->hasFile('pan_doc')))){

				$validator->errors()->add('pan_doc', 'PAN Document Required.');
			}
			if( (empty($this->pan_no) && !empty($this->hasFile('pan_doc')))){

				$validator->errors()->add('pan_no', 'PAN No. Required.');
			}
			if((isset($this->pan_no)) && (!empty($this->pan_no))){
				if (!preg_match("/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/", $this->pan_no)) {
				  $validator->errors()->add('pan_no', 'Invalid PAN Number');
				}
			}
			if((!empty($this->msme_no) && empty($this->hasFile('msme_doc')))){

				$validator->errors()->add('msme_doc', 'MSME Document Required.');
			}
			if((empty($this->msme_no) && !empty($this->hasFile('msme_doc')))){

				$validator->errors()->add('msme_doc', 'MSME No. Required.');
			}
		});
	}
	public function attributes()
	{
		return [

		];
	}
}
