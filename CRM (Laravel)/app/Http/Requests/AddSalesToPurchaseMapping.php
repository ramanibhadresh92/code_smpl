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
use App\Models\CompanyProductMaster;
class AddSalesToPurchaseMapping extends FormRequest
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
            // "sales_product"         => 'required',
          

            
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
            $array = array();
            if(isset($this->purchase_product) && !empty($this->purchase_product)){
                foreach($this->purchase_product as $pro){
                    array_push($array,$pro['purchase_product_id']);
                }
                if(!empty($array)){
                   $count =  CompanyProductMaster::where("sortable",0)->whereIn("id",$array)->count();
                   if($count > 1){
                        $validator->errors()->add('purchase_product_id','One of the selected product is marked as non-sortable in Product Master.');    
                   }
                }
            }
        });
    }
    public function attributes()
    {
        return [
            "purchase_product_id" => "Purchase Product"
        ];
    }
}
