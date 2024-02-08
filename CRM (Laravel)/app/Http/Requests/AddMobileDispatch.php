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
class AddMobileDispatch extends FormRequest
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
            // 'client_id'         => 'required|exists:wm_client_master,id',
            // // 'dispatch_id'       => 'required|exists:wm_dispatch,id',
            // // 'collection_id'     => 'required|exists:appointment_collection,collection_id',
            // 'vehicle_id'        => 'exists:vehicle_master,vehicle_id',
            // "dispatch_date"     => 'required|date',
            // // "unload_date"       => 'required|date',
            // "challan_no"        => 'required|unique:wm_dispatch,challan_no',
            // // "collection_by"     => 'required|exists:adminuser,adminuserid',
            // // "master_dept_id"     => 'required|exists:wm_department,id',
            // "origin"            => 'required',
            // "destination"       => 'required',


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
        foreach($errors as $e){
            throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $e[0],"data"=>""
        ], VALIDATION_ERROR));
        }

    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $DispatchDate =  (isset($this->dispatch_date) && !empty($this->dispatch_date)) ? date("Y-m-d",strtotime($this->dispatch_date)) : "";
            if(strtotime($DispatchDate) != strtotime(date("Y-m-d"))) {
                $validator->errors()->add('dispatch_date', 'Dispatch date must not any past or future date.');
            }
            if(isset($this->gross_weight) && $this->gross_weight <= 0) {
                $validator->errors()->add('gross_weight', 'Gross Weight required.');
            }
            if(isset($this->tare_weight) && $this->tare_weight <= 0) {
                $validator->errors()->add('tare_weight', 'Tare Weight required.');
            }
            if($this->gross_weight < $this->tare_weight) {
                $validator->errors()->add('tare_weight', 'Tare Weight must be less then gross Weight.');
            }
            if(isset($this->sales_product)){
                $salesProduct = json_decode($this->sales_product);
                if(is_array($salesProduct) && empty($salesProduct)){
                    $validator->errors()->add('sales_product', 'Dispatch sales product required.');
                }
                if(is_array($salesProduct) && !empty($salesProduct)){
                    $totalQty = 0;
                    foreach($salesProduct as $raw){
                        $totalQty += ($raw->quantity > 0) ? _FormatNumberV2($raw->quantity) : 0;
                    }
                    $NetWeight = _FormatNumberV2($this->gross_weight - $this->tare_weight);
                    if($totalQty > $NetWeight){
                        $validator->errors()->add('sales_product', 'Products Weight must be the same or less then Net Wight.');
                    }
                }
            }
            if(!empty($this->eway_bill_no)) {
                $wayBill = WmDispatch::where("id","!=",$this->dispatch_id)->where("eway_bill_no",$this->eway_bill_no)->count();
                if($wayBill > 0){
                    $validator->errors()->add('eway_bill_no', 'Duplicate eway bill number. Please verify.');
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
