<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\AppointmentCollectionDetail;
use App\Models\PurchaseCreditDebitNoteMaster;
use App\Models\PurchaseCreditDebitNoteDetailsMaster;

class PurchaseCreditDebitNote extends FormRequest
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
			'epr_way_bridge'    => 'nullable|mimes:jpeg,jpg,png',
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
			$flag =  false;
			$ProductList 	= (isset($this->product) && !empty($this->product)) ? json_decode($this->product,true)  : "";
			$collection_id	= (isset($this->collection_id) && !empty($this->collection_id)) ? $this->collection_id  : "";
			$array 			= array();
			if(!empty($ProductList)){
				$total_revised_rate=0;
				foreach($ProductList as $key=>$Raw){
					$CHANGE_IN 		= (isset($Raw['change_in']) && !empty($Raw['change_in'])) ?  $Raw['change_in'] 	: "";
					$PRODUCT_ID		= (isset($Raw['product_id']) && !empty($Raw['product_id'])) ?  $Raw['product_id'] 	: "";
					$gross_amt		= (isset($Raw['gross_amount']) && !empty($Raw['gross_amount'])) ?  $Raw['gross_amount'] : 0;
					$GROSS_AMOUNT 	= AppointmentCollectionDetail::where('collection_id',$collection_id)->where('product_id',$PRODUCT_ID)->sum('price');
					$NEW_GROSS_AMOUNT= 0;
					if(!empty($CHANGE_IN)){
						$flag = true;
					}
					$IDS 					= 	PurchaseCreditDebitNoteMaster::where("collection_id",$collection_id)->where("notes_type",$this->notes_type)->whereIn("status",[0,1])->pluck("id");

					$REVISE_GROSS_AMOUNT 	= 	PurchaseCreditDebitNoteDetailsMaster::where('collection_details_id',$this->collection_details_id)
												->whereIn("note_id",$IDS)
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
					if (!array_key_exists($Raw['product_id'],$array))
					{
						$array[$Raw['product_id']] = 0;
					}
					$array[$Raw['product_id']] += $NEW_GROSS_AMOUNT;
					$FINAL_AMT = _FormatNumberV2($REVISE_GROSS_AMOUNT + $array[$Raw['product_id']]);
					if($GROSS_AMOUNT < $FINAL_AMT){
						$validator->errors()->add('change_in', 'You can not add credit or debit note more then gross amount of invoice.');
					}
				}
				if(!$flag) {
					$validator->errors()->add('change_in', 'Please select at list one record to generate credit debit note.');
				}
				//exit;
			}
			
		});
	}
	public function attributes()
	{
		return [

		];
	}
}
