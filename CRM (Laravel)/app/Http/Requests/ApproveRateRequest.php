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
use App\Models\TransporterDetailsMaster;
class ApproveRateRequest extends FormRequest
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
            // "challan_no"   => 'required',
        ];
    }

    public function messages()
    {
        return [
            // "challan_no.required"   => 'Please enter challan no.',
            "eway_bill_no"          => 'unique:wm_dispatch,eway_bill_no',
            "totalPrice"            => 'required',
            "type_of_transaction"   => 'required'
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
            $dispatchID = WmDispatch::find($this->dispatch_id);
            if($dispatchID && empty($dispatchID->shipping_address_id)){
                $validator->errors()->add('eway_bill_no', 'Shipping Address is not updated.Please update it first');
            }
            $PO_ID          =  (isset($dispatchID->transporter_po_id) && !empty($dispatchID->transporter_po_id)) ? $dispatchID->transporter_po_id : "";

            $DISPATCH_TYPE  =  (isset($dispatchID->dispatch_type) && !empty($dispatchID->dispatch_type)) ? $dispatchID->dispatch_type : "";
            if(empty($PO_ID)){
                $validator->errors()->add('transporter_po_id', 'Transporter PO is required.');
            }
            $RATE       = 0;
            if($DISPATCH_TYPE == NON_RECYCLEBLE_TYPE && !empty($PO_ID)){
                $TRANS   = TransporterDetailsMaster::where("id",$PO_ID)->first();
                if($TRANS){
                    $RATE           = $TRANS->rate;
                    $THIRD_PARTY    = $TRANS->paid_by_party;
                    if($RATE <= 0 && $THIRD_PARTY == 0){
                        $validator->errors()->add('transporter_po_id', 'Transporter PO amount can not be zero.');
                    }    
                }
            }
            if($this->approval_status == 1){
                if($dispatchID && $dispatchID->approval_status == 1){
                    $validator->errors()->add('dispatch_id', 'Dispatch is already approved.');    
                }
                if(isset($this->product) && !empty($this->product)){
                        $TotalGrossAmount   = 0;
                        $isFromSameState    = ($dispatchID->origin_state_code == $dispatchID->destination_state_code) ? true : false; 
                    foreach($this->product as $pro){
    
                        $GST_ARR        = WmProductMaster::calculateProductGST($pro['product_id'],$pro['quantity'],$pro['rate'],$isFromSameState);
                        $TotalGrossAmount   = $TotalGrossAmount + $GST_ARR['TOTAL_NET_AMT'];
                    }
                    if ($TotalGrossAmount > EWAY_BILL_MIN_AMOUNT && empty($this->eway_bill_no)) {
                            // $validator->errors()->add('eway_bill_no', 'Eway bill number is required.Total Amount including Tax amount is '._FormatNumberV2($TotalGrossAmount));
                    }
                }
                /* IF TRANSACTION TYPE IS RDF THEN EWAY BILL COMPALSARY REQUIRED - 16 JUNE 2020*/
                if($this->type_of_transaction == PARA_RDF && $this->approval_status == 1) {
                    if(empty($this->eway_bill_no)){
                        // $validator->errors()->add('eway_bill_no', 'Eway Bill number is required.');    
                    }
                }
                if (!empty($this->eway_bill_no)) {
                    $wayBill = WmDispatch::where("id","!=",$this->dispatch_id)->where("eway_bill_no",$this->eway_bill_no)->count();
                    if($wayBill > 0){
                        $validator->errors()->add('eway_bill_no', 'Eway bill number already exits.Please enter unique Eway bill number.');    
                    }
                }elseif(!empty($this->challan_no)) {
                    // $dispatchID = WmDispatch::find($this->dispatch_id);
                    if($dispatchID){
                        $date       = date('Y-m-d',strtotime($this->dispatch_date));
                        $Month      = date("m",strtotime($date));
                        $Year       = date("Y",strtotime($date));
                        if (intval($Month) >= 1 && intval($Month) <= 3) {
                            $StartDate  = ($Year-1)."-04-01 00:00:00";
                            $EndDate    = $Year."-03-31 23:59:59";
                            $count      = WmDispatch::whereBetween("dispatch_date",array($StartDate,$EndDate))->where("challan_no",$this->challan_no)->where("id","!=",$this->dispatch_id)->count();
                        } else {
                            $StartDate  = $Year."-04-01";
                            $count      = WmDispatch::where("dispatch_date",">=",$StartDate)->where("challan_no",$this->challan_no)->where("id","!=",$this->dispatch_id)->count();
                        }
                        // $challan = WmDispatch::where("id","!=",$this->dispatch_id)->where("challan_no",$this->challan_no)->count();
                        if($count > 0){
                            $validator->errors()->add('challan_no', 'Challan number already exits.Please enter unique challan number.');    
                        } 
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
