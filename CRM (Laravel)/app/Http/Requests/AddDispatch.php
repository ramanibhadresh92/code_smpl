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
use App\Models\TransporterDetailsMaster;
use App\Models\VehicleDocument;
use App\Models\WmDepartment;
use App\Models\WmClientMaster;
use App\Models\WmProductMaster;
class AddDispatch extends FormRequest
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
			// 'epr_billt'         => 'nullable|mimes:jpeg,jpg,png,pdf',
			// 'epr_challan'       => 'nullable|mimes:jpeg,jpg,png,pdf',
			// 'epr_way_bridge'    => 'nullable|mimes:jpeg,jpg,png,pdf',
			// 'epr_eway'          => 'nullable|mimes:jpeg,jpg,png,pdf',
			'transporter_po_id' => 'required',
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
			 'transporter_po_id' => 'required'

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

			$bill_from_mrf_id 	= (isset($this->bill_from_mrf_id) && !empty($this->bill_from_mrf_id)) ? $this->bill_from_mrf_id : 0;
			$cnt 	= WmDepartment::where("id",$bill_from_mrf_id)->where("is_virtual",1)->count();
			if($cnt > 0){
				$validator->errors()->add('bill_from_mrf_id', 'Invalid Bill From MRF.');
			}

			$PO_ID 			= (isset($this->transporter_po_id) && !empty($this->transporter_po_id)) ? $this->transporter_po_id : "";

			$DISPATCH_TYPE 	= (isset($this->dispatch_type) && !empty($this->dispatch_type)) ? $this->dispatch_type : "";
			$RATE 					= 0;
			$TRANS_DISPATCH_TYPE 	= 0;
			$PAID_BY_PARTY 			= 0;
			$TDM 					= TransporterDetailsMaster::where("id",$PO_ID)->first();
			if($TDM){
				$TRANS_DISPATCH_TYPE 	= $TDM->dispatch_type;
				$PAID_BY_PARTY 			= $TDM->paid_by_party;
			}
			if($DISPATCH_TYPE == NON_RECYCLEBLE_TYPE && !empty($PO_ID)) {
				IF($TDM){
					$RATE 			= $TDM->rate;
					$PAID_BY_PARTY 	= $TDM->paid_by_party;
					$TRANS_DISPATCH_TYPE = $TDM->dispatch_type;
				}
				if($RATE <= 0 && $PAID_BY_PARTY == 0) {
					$validator->errors()->add('transporter_po_id', 'Transporter PO amount can not be zero.');
				}
			}
			if($DISPATCH_TYPE == NON_RECYCLEBLE_TYPE && $this->vehicle_id > 0) {
				$VEHICLE_DOC = VehicleDocument::where(["vehicle_id"=>$this->vehicle_id,"document_type" => RC_BOOK_ID])->orderBy("id","DESC")->first();
				if(empty($VEHICLE_DOC)) {
					if(empty($this->rc_book_no) || !$this->hasFile('rc_book')){
						$validator->errors()->add('vehicle_id', 'Vehicle RC Book No. and Document Required.');
					}
				}
			}
			$DispatchDate =  (isset($this->dispatch_date) && !empty($this->dispatch_date)) ? date("Y-m-d",strtotime($this->dispatch_date)) : "";
			if(strtotime($DispatchDate) != strtotime(date("Y-m-d"))) {
				// $validator->errors()->add('dispatch_date', 'Dispatch date must not any past or future date.');
			}
			if(!isset($this->collection_cycle_term) || empty($this->collection_cycle_term)) {
				$validator->errors()->add('collection_cycle_term', 'Payment Terms is required.');
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
			if(isset($this->sales_product)) {
				$salesProduct = json_decode($this->sales_product);
				if(is_array($salesProduct) && empty($salesProduct)) {
					$validator->errors()->add('sales_product', 'Dispatch sales product required.');
				}
				$gross_amount = 0;
				if(is_array($salesProduct) && !empty($salesProduct)) {
					$totalQty 			= 0;
					$SalesproductIDS 	= array();
					foreach($salesProduct as $raw) {
						array_push($SalesproductIDS,$raw->product_id);
						$totalQty += ($raw->quantity > 0) ? _FormatNumberV2($raw->quantity) : 0;
						$gross_amount += _FormatNumberV2($raw->quantity * $raw->price); 

					}
					if($gross_amount == 0){
						$this->merge(['zero_gross_amount' => 1]);
					}
					$NetWeight = _FormatNumberV2($this->gross_weight - $this->tare_weight);
					if($totalQty > $NetWeight){
						$validator->errors()->add('sales_product', 'Products Weight must be the same or less then Net Wight.');
					}
					$DispatchTypeCnt = 0;
					if($PAID_BY_PARTY == 0){
						if($TRANS_DISPATCH_TYPE == NON_RECYCLEBLE_TYPE){
							$DispatchTypeCnt = WmProductMaster::where("recyclable",1)->whereIn("id",$SalesproductIDS)->count();
						}elseif($TRANS_DISPATCH_TYPE == RECYCLEBLE_TYPE){
							$DispatchTypeCnt = WmProductMaster::where("recyclable",0)->whereIn("id",$SalesproductIDS)->count();
						}
					}
					
					if($DispatchTypeCnt > 0){
						$validator->errors()->add('sales_product', 'Invalid PO Dispatch Product Type.');
					}
				}
			}
			if(!empty($this->eway_bill_no)) {
				$wayBill = WmDispatch::where("id","!=",$this->dispatch_id)->where("eway_bill_no",$this->eway_bill_no)->count();
				if($wayBill > 0){
					$validator->errors()->add('eway_bill_no', 'Duplicate eway bill number. Please verify.');
				}
			}

			/** Validate Client Credit Limit */
			if (defined("VALIDATE_CLIENT_CREDIT_LIMIT") && VALIDATE_CLIENT_CREDIT_LIMIT == 1) {
				$Today 				= date("Y-m-d");
				$TotalInvoiceAmount = WmDispatch::CalculateInvoiceAmount($this);
				$Message 			= WmClientMaster::CanGenerateInvoiceForClient($this->client_id,$TotalInvoiceAmount,$Today);
				if(!empty($Message)) {
					$validator->errors()->add('client_id',$Message);
				}
			}
			/** Validate Client Credit Limit */

			/** NOT ALLOW BILL FROM MRF FROM PIRANA */
			$BILL_FROM_MRF 	= (isset($this->bill_from_mrf_id) && !empty($this->bill_from_mrf_id)) ? $this->bill_from_mrf_id : "";
			$AdminUserID 	= Auth()->user()->adminuserid;
			if ($BILL_FROM_MRF == 11 && $AdminUserID != 513) {
				/** Removed this validation for Ketan Patel @since Jan 12, 2023, 2:08 PM Refer Mail from "Access to MRF Pirana for Disposal" by Samir Jani */
				$validator->errors()->add('bill_from_mrf_id', 'Dispatch from PIRANA is stopped.');
				/** Removed this validation for Ketan Patel @since Jan 12, 2023, 2:08 PM Refer Mail from "Access to MRF Pirana for Disposal" by Samir Jani */
			}
			/** NOT ALLOW BILL FROM MRF FROM PIRANA */
		});
	}
	public function attributes()
	{
		return [
			"transporter_po_id" => "transpoter PO"
		];
	}
}
