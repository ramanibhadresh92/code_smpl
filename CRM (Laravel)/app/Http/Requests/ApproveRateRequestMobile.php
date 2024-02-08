<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\WmDispatch;
use App\Models\WmProductMaster;
use App\Models\AdminUserRights;
class ApproveRateRequestMobile extends FormRequest
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
            //
        ];
    }

    public function messages()
    {
        return [
            "challan_no"        => 'required|unique:wm_dispatch,challan_no,'.$this->dispatch_id.',id',
            "eway_bill_no"      => 'unique:wm_dispatch,eway_bill_no',
            "totalPrice"        => 'required',
            "type_of_transaction"   => 'required'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        foreach($errors as $e){
            throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $e[0],"data"=>""
        ], VALIDATION_ERROR));
        }
       
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {


    	$RIGHTS = AdminUserRights::where("adminuserid",Auth()->user()->adminuserid)->where("trnid",RATE_APPROVAL_RIGHTS)->first();
    	if(!$RIGHTS){
    		$validator->errors()->add('dispatch_id', 'You are not authorize to access this menu.');    
    	}

            $dispatchID = WmDispatch::find($this->dispatch_id);
            if($this->approval_status == 1){
                if($dispatchID && $dispatchID->approval_status == 1){
                    $validator->errors()->add('dispatch_id', 'Dispatch is already approved.');    
                }
                if(isset($this->product) && !empty($this->product)){
                        $Product = json_decode($this->product,true);
                        $TotalGrossAmount   = 0;
                        $isFromSameState    = ($dispatchID->origin_state_code == $dispatchID->destination_state_code) ? true : false; 
                    foreach($Product as $pro){
                        $GST_ARR            = WmProductMaster::calculateProductGST($pro['product_id'],$pro['quantity'],$pro['rate'],$isFromSameState);
                        $TotalGrossAmount   = $TotalGrossAmount + $GST_ARR['TOTAL_NET_AMT'];
                    }
                    if ($TotalGrossAmount > EWAY_BILL_MIN_AMOUNT && empty($this->eway_bill_no)) {
                        $validator->errors()->add('eway_bill_no', 'Eway bill number is required.Total Amount including Tax amount is '._FormatNumberV2($TotalGrossAmount));
                    }
                }
                /* IF TRANSACTION TYPE IS RDF THEN EWAY BILL COMPALSARY REQUIRED - 16 JUNE 2020*/
                if($this->type_of_transaction == PARA_RDF && $this->approval_status == 1) {
                    if(empty($this->eway_bill_no)){
                        $validator->errors()->add('eway_bill_no', 'Eway Bill number is required.');    
                    }
                }
                if (!empty($this->eway_bill_no)) {
                    $wayBill = WmDispatch::where("id","!=",$this->dispatch_id)->where("eway_bill_no",$this->eway_bill_no)->count();
                    if($wayBill > 0){
                        $validator->errors()->add('eway_bill_no', 'Eway bill number already exits.Please enter unique Eway bill number.');    
                    }
                }elseif(!empty($this->challan_no)) {
                    $challan = WmDispatch::where("id","!=",$this->dispatch_id)->where("challan_no",$this->challan_no)->count();
                    if($challan > 0){
                        $validator->errors()->add('challan_no', 'Challan number already exits.Please enter unique challan number.');    
                    }
                }
            }



            
        });
    }
    public function attributes()
    {
        return [
            
        ];
    }
}
