<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\WmProductionReportMaster;
use App\Models\WmProcessedProductMaster;
use App\Models\StockLadger;

class ProductionReportAdd extends FormRequest
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
            'production_date'   => 'required',
            'processing_qty'    => 'required',
            'product_id'        => 'required',
            'shift_id'          => 'required',
            'mrf_id'            => 'required',
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
            if(Auth()->user()->adminuserid != 1 && $this->mrf_id == 3){
               // return $validator->errors()->add('mrf_id', "Production report temporary stop for 15 to 20 minite. Kindly Wait for some time.");
            }
            ###### VALIDATION ADDED AS PER SAMIR SIR REVIEW - 29-10-2021
            $RM_QTY = (isset($this->processing_qty) ? $this->processing_qty : 0 );
            $FG_QTY = 0;
            if(isset($this->id) && $this->id > 0){
                $FG_QTY = WmProcessedProductMaster::where("production_id",$this->id)->sum("qty");
            }else{
                $PRODUCT_ID     = $this->product_id;
                $MRF_ID         = $this->mrf_id;
                $PRODUCT_TYPE   = PRODUCT_PURCHASE;
                $DATE           = date("Y-m-d");
                $CURRENT_STOCK  = StockLadger::GetPurchaseProductStock($MRF_ID,$PRODUCT_ID,$DATE);
                if($CURRENT_STOCK < $RM_QTY){
                    $validator->errors()->add('product_id', 'Current stock is less then processing quantity.Please refresh the page.');
                }
            }
            

            if(!empty($this->sales_product)){
                foreach($this->sales_product as $raw){
                    $FG_QTY         += $raw['qty'];
                }
            }
            
            if($this->finalize == 1){
                if(round($FG_QTY) != round($RM_QTY)){
                    $validator->errors()->add('production_date', "Total production QTY must not be less than total processing QTY.");      
                }
            }else{
                 if($FG_QTY > $RM_QTY){
                    $validator->errors()->add('production_date', "Total production QTY must not be less than total processing QTY.");      
                }
            }
            ###### VALIDATION ADDED AS PER SAMIR SIR REVIEW - 29-10-2021

            if(!empty($this->production_date)) {
                $ProductionDate = date("Y-m-d",strtotime($this->production_date));
                $PreviousDate   = date('Y-m-d', strtotime('-1 day', strtotime($ProductionDate)));
                if(strtotime(PRODUCTION_REPORT_START_DATE) > strtotime($ProductionDate)){
                   $validator->errors()->add('production_date', 'You can not fill production report before '.PRODUCTION_REPORT_START_DATE);        
                }
                if(strtotime(PRODUCTION_REPORT_START_DATE) < strtotime($ProductionDate)){
                    $Exits = WmProductionReportMaster::where("production_date",$PreviousDate)->where("mrf_id",$this->mrf_id)->first();
                    if(!$Exits)
                    $validator->errors()->add('production_date', 'Please fill previous day production report');        
                }
                
            }
        });
    }
    public function attributes()
    {
        return [
            'production_date'   => 'Production Date',
            'processing_qty'    => 'Process Quantity',
            'product_id'        => 'Product',
            'shift_id'          => 'Shift',
            "mrf_id"            => "MRF"
        ];
    }
}
