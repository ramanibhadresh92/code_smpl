<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
class AddSalesTarget extends FormRequest
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
						'month'   => 'required',
						'year'    => 'required',
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
				$YEAR 				= (!empty($this->year)) ? $this->year : date("Y");
				$MONTH 				= (!empty($this->month)) ? $this->month : date("m");
				$MONTH 				= (strlen($MONTH) > 1) ? $MONTH : "0".$MONTH;
				$CURR_DATE 			= date("Y-m-d");
				$CURR_DATE 			= date("Ym", strtotime($CURR_DATE));
				$SEARCH_MONTH_YEAR 	= $YEAR."-".$MONTH."-01";
				$SEARCH_MONTH_YEAR 	= date("Ym", strtotime($SEARCH_MONTH_YEAR));
				$DISABLE 			= ($SEARCH_MONTH_YEAR >= $CURR_DATE) ? 0 : 1;
				if($DISABLE == 1){
						$validator->errors()->add('month', 'You can not add past month year sales target');
					}
				});
		}
		public function attributes()
		{
				return [

				];
		}
}
