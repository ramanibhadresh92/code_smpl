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
use App\Models\WmDispatchPlan;
use App\Models\WmDispatchPlanProduct;
use App\Facades\LiveServices;
class AddSalesOrderPlan extends FormRequest
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
			'dispatch_plan_date'   	=> 'required|date|after_or_equal:'.date("Y-m-d"),
			'valid_last_date'   	=> 'required|date|after_or_equal:dispatch_plan_date',
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
			if(isset($this->direct_dispatch) && !empty($this->direct_dispatch)){
				if(empty($this->origin)){
					$validator->errors()->add('origin', "Vendor name is required");
				}
			}
			if(!isset($this->collection_cycle_term) || empty($this->collection_cycle_term)) {
				$validator->errors()->add('collection_cycle_term', "Payment Terms is required");
			}
			if(isset($this->dispatch_plan_date) && !empty($this->dispatch_plan_date) && isset($this->valid_last_date) && !empty($this->valid_last_date))
			{
				$Today  		= date("Y-m-d");
				$date   		= date("Y-m-d",strtotime($this->dispatch_plan_date));
				$lastDate   	= date("Y-m-d",strtotime($this->valid_last_date));
				$days 			= 30;
				$next   		= date('Y-m-d', strtotime($date. "+ $days days"));
				$validationFlag = false;
				if(strtotime($Today) > strtotime($date)){
				   	$validationFlag = true;
				}elseif(strtotime($date) > strtotime($lastDate)){
					$validationFlag = true;  
				}elseif(strtotime($lastDate) > strtotime($next)){
					$validationFlag = true;  
				}
				if($validationFlag){
					$validator->errors()->add('dispatch_plan_date', "The Dispatch plan date and valid last date diffrence must be a between $days days.");
				}
			}
			if(isset($this->sales_product) && !empty($this->sales_product))
			{
				$Products 				=  json_decode($this->sales_product,true);	
				$ClientID 				=  (isset($this->client_master_id) && !empty($this->client_master_id)) ? $this->client_master_id : 0;
				$OriginID 				=  (isset($this->origin) && !empty($this->origin)) ? $this->origin : 0;
				$WmDispatchPlan 		= new WmDispatchPlan();
				$TblDispatchPlan 		= $WmDispatchPlan->getTable();
				$WmDispatchPlanProduct 	= new WmDispatchPlanProduct();
				$TblPlanProduct 		= $WmDispatchPlanProduct->getTable();
				$DispatchPlanDate 		= (isset($this->dispatch_plan_date) && !empty($this->dispatch_plan_date)) ? $this->dispatch_plan_date : "";
				$LastPlanDate 			= (isset($this->valid_last_date) && !empty($this->valid_last_date)) ? $this->valid_last_date : "";
				// $LastPlanDate 			= (isset($this->valid_last_date) && !empty($this->valid_last_date)) ? $this->valid_last_date : "";
				$MRFID 					= (isset($this->master_dept_id) && !empty($this->master_dept_id)) ? $this->master_dept_id : "";
				if(!empty($Products)){
					foreach($Products as $raw){
						$GET_RATE 	= WmDispatchPlan::join("$TblPlanProduct as WDPP","$TblDispatchPlan.id","=","WDPP.dispatch_plan_id")
						->where(function ($query) use($TblDispatchPlan,$DispatchPlanDate,$LastPlanDate) {
						  		$query->where(function($res) use($TblDispatchPlan,$DispatchPlanDate){
									$res->where("$TblDispatchPlan.dispatch_plan_date","<=",$DispatchPlanDate);
									$res->where("$TblDispatchPlan.valid_last_date",">=",$DispatchPlanDate);		  	
								});
								$query->orWhere(function($res) use($TblDispatchPlan,$LastPlanDate){
									$res->where("$TblDispatchPlan.dispatch_plan_date","<=",$LastPlanDate);		
									$res->where("$TblDispatchPlan.valid_last_date",">=",$LastPlanDate);		
								});
						})
						->where("WDPP.sales_product_id",$raw['sales_product_id'])
						->where("$TblDispatchPlan.client_master_id",$ClientID)
						->where("$TblDispatchPlan.approval_status","1")
						->where("$TblDispatchPlan.master_dept_id",$MRFID);

						if($OriginID > 0 && ($this->direct_dispatch))
						{
							$GET_RATE->where("$TblDispatchPlan.origin",$OriginID);
						} else {
							$GET_RATE->where("$TblDispatchPlan.direct_dispatch","0");
						}
						
						$GET_RATE->orderBy("WDPP.id","DESC");
						$RES =  $GET_RATE->get()->toArray();
						if(!empty($RES)){
							$validator->errors()->add('sales_product', "Rate already added for this product between entered date.");
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
