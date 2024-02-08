<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\WmInvoicesCreditDebitNotes;
use App\Models\WmInvoicesCreditDebitNotesDetails;
use App\Models\WmSalesMaster;
use App\Models\WmDispatchProduct;
class GenerateCreditDebitNote extends FormRequest
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
			'epr_way_bridge'    		 => 'nullable|mimes:jpeg,jpg,png',
			'first_level_approved_by'    => 'required',
			"dispatch_id" 				 => 'required'
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
			$array 			= array();
			$flag 			=  false;
			$dispatch_id 	= ($this->dispatch_id > 0) ? $this->dispatch_id : 0;
			$note_type 		= $this->notes_type;
			$CN_DATA = WmInvoicesCreditDebitNotes::where("dispatch_id",$dispatch_id)
					->where("invoice_no",$this->invoice_no)
					->where("status","!=",2)
					->count();
			if($CN_DATA > 0){
				// $validator->errors()->add('change_in', 'Already CN/DN generated For this invoice.Please check');
			}
			if(!is_array($this->product)){
				$ProductList =  (isset($this->product) && !empty($this->product)) ? json_decode($this->product,true)  : "";
			}else{
				$ProductList =  $this->product;
			}
			if(!empty($ProductList)){
				foreach($ProductList as $Raw){
					$dispatch_product_id = WmDispatchProduct::find($Raw['dispatch_product_id']);
					if(empty($dispatch_product_id)){
						$validator->errors()->add('change_in', 'Invalid dispatch Product data.');
					}
					$CHANGE_IN 	= (isset($Raw['change_in']) && !empty($Raw['change_in'])) ?  $Raw['change_in'] 	: "";
					if(!empty($CHANGE_IN)){
						$flag = true;
					}
					$NEW_GROSS_AMOUNT 	= 0;
					$GROSS_AMOUNT 	= WmSalesMaster::where("dispatch_product_id",$Raw['dispatch_product_id'])
					->where("dispatch_id",$dispatch_id)
					->sum("gross_amount");
					$GROSS_AMOUNT 	= ($GROSS_AMOUNT) ? _FormatNumberV2($GROSS_AMOUNT) : 0;
					$IDS 	= WmInvoicesCreditDebitNotes::where("dispatch_id",$dispatch_id)
							->where("notes_type",$note_type)
							->where("status","!=",2)
							->pluck("id");
					$REVISE_GROSS_AMOUNT 	= 	WmInvoicesCreditDebitNotesDetails::where("dispatch_product_id",$Raw['dispatch_product_id'])
						->whereIn("cd_notes_id",$IDS)
						->where("product_id",$Raw['product_id'])
						->sum("revised_gross_amount");

					switch ($Raw['change_in']) {
						case '1':
							$NEW_GROSS_AMOUNT = _FormatNumberV2($Raw["quantity"] * $Raw["revised_rate"]);
							break;
						case '2':
							$NEW_GROSS_AMOUNT = _FormatNumberV2($Raw["rate"] * $Raw["revised_quantity"]);
							break;
						case '3':
							$NEW_GROSS_AMOUNT = _FormatNumberV2($Raw["revised_rate"] * $Raw["revised_quantity"]);
							break;
						default:
							break;
					}
					if (!array_key_exists($Raw['dispatch_product_id'],$array))
					{
						$array[$Raw['dispatch_product_id']] = 0;
					}
					$array[$Raw['dispatch_product_id']] += $NEW_GROSS_AMOUNT;
					
					$FINAL_AMT = _FormatNumberV2($REVISE_GROSS_AMOUNT + $array[$Raw['dispatch_product_id']]);
					if($GROSS_AMOUNT < $FINAL_AMT){
						$validator->errors()->add('change_in', 'You can not add credit or debit note more then gross amount of invoice.');
					}
				}
				if(!$flag) {
					$validator->errors()->add('change_in', 'Please select at list one record to generate credit debit note.');
				}
			}
		});
	}
	public function attributes()
	{
		return [
			'first_level_approved_by'    => 'Approved By',
		];
	}
}
