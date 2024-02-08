<?php

namespace Modules\Mobile\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\GSTStateCodes;
use App\Classes\SetuApi;
use Auth;
class ClientAdd extends FormRequest
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
			'client_name'       => 'required',
			'address'           => 'required',
			'gst_state_code'    => 'nullable|exists:GST_STATE_CODES,id',
			'gstin_no'          => "nullable|unique:wm_client_master,gstin_no",
            'pan_no'            => "nullable|unique:wm_client_master,pan_no",
            'adhar_no'          => "nullable|unique:wm_client_master,adhar_no",
            'email'           	=> 'required',
            'mobile_no'         => "required|unique:wm_client_master,mobile_no"
		];
	}

	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			if(isset($this->gstin_no) && !empty($this->gstin_no)) {
				if(isset($this->gst_state_code) && empty($this->gst_state_code)){
					$validator->errors()->add('gst_state_code', 'GST State code is required.');    
				}
				$GST_NO = $this->gstin_no;
                $RESULT = CheckValidGST($this->gst_state_code,$GST_NO);

                if(!$RESULT){
                    $validator->errors()->add('gst_state_code', 'Invalid GST In no Or GST State Code.');    
                }
                // if(!empty($GST_NO)){
                //     $SetuApi        = new SetuApi();
                //     $SetuResult     = $SetuApi->GetTaxPayerGSTInfo($GST_NO);
                //     if(!empty($SetuResult)){
                //         if(isset($SetuResult->error) && !empty($SetuResult->error)){
                //             $validator->errors()->add('gstin_no', $SetuResult->error_description);
                //         }
                //     }
                // }
			}
			if((!empty($this->gstin_no) && empty($this->hasFile('gst_doc_id')))){

				$validator->errors()->add('gst_doc_id', 'GST Document Required.');
			}
			if(((isset($this->pan_no) && (!empty($this->pan_no))) && empty($this->hasFile('pan_doc_id')))){

				$validator->errors()->add('pan_doc', 'PAN Document Required.');
			}

			if( (empty($this->pan_no) && !empty($this->hasFile('pan_doc_id')))){

				$validator->errors()->add('pan_no', 'PAN No. Required.');
			}
			if((isset($this->pan_no)) && (!empty($this->pan_no))){
				if (!preg_match("/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/", $this->pan_no)) {
				  $validator->errors()->add('pan_no', 'Invalid PAN Number');
				}
			}
			if((isset($this->adhar_no)) && (!empty($this->adhar_no))){
				if (!preg_match("/^(([0-9]){12}?$/", $this->adhar_no)) {
				  $validator->errors()->add('adhar_no', 'Invalid Adhar Number');
				}
			}
			if(((isset($this->adhar_no) && (!empty($this->adhar_no))) && empty($this->hasFile('adhar_doc_id')))){

				$validator->errors()->add('adhar_doc_id', 'Adhar Document Required.');
			}
			
			if( (empty($this->adhar_no) && !empty($this->hasFile('adhar_doc_id')))){

				$validator->errors()->add('adhar_no', 'Adhar No. Required.');
			}
			
		});
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
