<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Appoinment;
use App\Models\AdminUserRights;
class AppoinmentAutoAddRequest extends FormRequest
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
            'customer_id'       => 'required|exists:customer_master,customer_id',
            // // 'para_status_id'    => 'required|exists:parameter,para_id',
            'app_date_time'     => 'required|date',
            'vehicle_id'        => 'exists:vehicle_master,vehicle_id',
            
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
            $count = AdminUserRights::where("adminuserid",Auth()->user()->adminuserid)->where("trnid",ADD_PAST_APPOINTMENT)->count();
            if($count == 0){
                if(strtotime($this->app_date_time) < strtotime('now')){
                    $validator->errors()->add('app_date_time', 'Appointment can not be set for past date.');
                }
            }
        });
    }
    public function attributes()
    {
        return [
            // 'app_date_time' => 'appointment date time',
            // 'para_status_id'  => 'status',
            // "vehicle_id" => "vehicle number"
        ];
    }
}
