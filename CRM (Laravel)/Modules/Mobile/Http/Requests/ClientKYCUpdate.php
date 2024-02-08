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
class ClientKYCUpdate extends FormRequest
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
			'gst_state_code'    => 'nullable|exists:GST_STATE_CODES,id',
			// 'gstin_no'          => "nullable",
            // 'pan_no'            => "nullable",
            // 'adhar_no'          => "nullable"
		];
	}

	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			$client_id 			= (\Auth::check()) ? Auth()->user()->id :  0;
			$pan_doc_id 		= (\Auth::check()) ? Auth()->user()->pan_doc_id :  0;
			$gst_doc_id 		= (\Auth::check()) ? Auth()->user()->gst_doc_id :  0;
			$adhar_doc_id 		= (\Auth::check()) ? Auth()->user()->adhar_doc_id :  0;
			$gst_state_code 	= (\Auth::check()) ? Auth()->user()->gst_state_code :  0;
			// if(isset($this->gstin_no) && !empty($this->gstin_no)) {
			// 	if(isset($gst_state_code) && empty($gst_state_code)){
			// 		$validator->errors()->add('gstin_no', 'GST State code is empty.');    
			// 	}
			// 	$GST_NO = $this->gstin_no;
            //     $RESULT = CheckValidGST($gst_state_code,$GST_NO);

            //     if(!$RESULT){
            //         $validator->errors()->add('gst_state_code', 'Invalid GST In no Or GST State Code.');    
            //     }
            //     if(!empty($GST_NO)){
            //         $SetuApi        = new SetuApi();
            //         $SetuResult     = $SetuApi->GetTaxPayerGSTInfo($GST_NO);
            //         if(!empty($SetuResult)){
            //             if(isset($SetuResult->error) && !empty($SetuResult->error)){
            //                 $validator->errors()->add('gstin_no', $SetuResult->error_description);
            //             }
            //         }
            //     }
			// }
			if(empty($this->gstin_no) && empty($this->pan_no) &&  empty($this->adhar_no)){
				// $validator->errors()->add('required', 'GST or PAN or Adhar Number Required with Document');
				$validator->errors()->add('required','GST or PAN or Adhar Number Required with Document');
			}else{
				if( (empty($this->pan_no) && !empty($this->hasFile('pan_doc_id')))){
					$validator->errors()->add('pan_no', 'PAN No. Required.');
				}
				if((isset($this->pan_no)) && (!empty($this->pan_no))){
					if (!preg_match("/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/", $this->pan_no)) {
					  	$validator->errors()->add('pan_no', 'Invalid PAN Number');
					}else if((empty($this->hasFile('pan_doc_id')) && (empty($pan_doc_id)))){
						$validator->errors()->add('pan_doc_id', 'PAN Document Required.');
					}
				}
				if((isset($this->gstin_no)) && (!empty($this->gstin_no))){
					if (!preg_match("/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[0-9]{1}[A-Z]{1}[A-Z0-9]{1}$/", $this->gstin_no)) {
					  $validator->errors()->add('gst_no', 'Invalid GST Number');
					}else if(((isset($this->pan_no) && (!empty($this->pan_no))))){
                            $RESULT = CheckValidGSTwithPAN($this->pan_no,$this->gstin_no);  
                            if(!$RESULT){
                                $validator->errors()->add('gst_no', 'GSTN & PAN No. Does Not Match');
                            }else if((empty($this->hasFile('gst_doc_id')) && (empty($gst_doc_id)))){ 
								$validator->errors()->add('gst_doc_id', 'GST Document Required.');
							}
					}
				}
				if( (empty($this->gstin_no) && !empty($this->hasFile('gst_doc_id')))){
					$validator->errors()->add('gst_no', 'GST No. Required.');
				}

				if( (empty($this->adhar_no) && !empty($this->hasFile('adhar_doc_id')))){
					$validator->errors()->add('adhar_no', 'Adhar No. Required.');
				}
				if((isset($this->adhar_no)) && (!empty($this->adhar_no))){
					if (!preg_match("/^([0-9]){12}$/", $this->adhar_no)) {
					  $validator->errors()->add('adhar_no', 'Invalid Adhar Number');
					}else if((empty($this->hasFile('adhar_doc_id')) && (empty($adhar_doc_id)))){
						$validator->errors()->add('adhar_doc_id', 'Aadhar Document Required.');
					}
				}
				// if(((isset($this->adhar_no) && (!empty($this->adhar_no))) && empty($this->hasFile('adhar_doc_id')))){
				// 	$validator->errors()->add('adhar_doc_id', 'Adhar Document Required.');
				// }
				
			}
		});
	}
	protected function failedValidation(Validator $validator)
	{
		$errors 	= (new ValidationException($validator))->errors();
		$messages   = $validator->errors()->first(); 
		foreach($errors as $e){
			throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $e[0],"data"=>""
			], VALIDATION_ERROR));
		}
	}
}
