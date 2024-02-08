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
use App\Models\VehicleDriverMappings;

class AppoinmentUpdateRequest extends FormRequest
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
            'appointment_id'    => 'required|exists:appoinment,appointment_id',
            'customer_id'       => 'required|exists:customer_master,customer_id',
            'para_status_id'    => 'required|exists:parameter,para_id',
            'old_app_date'      => 'required|date',
            'app_date_time'     => 'required|date',
            'vehicle_id'        => 'exists:vehicle_master,vehicle_id',
            
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->para_status_id != APPOINTMENT_CANCELLED) {
                $date = date("Y-m-d H:i:s");
                if(strtotime($this->app_date_time) < strtotime($date)){
                    $validator->errors()->add('app_date_time', 'Appointment can not be set for past date.');
                }
                if (($this->para_status_id != APPOINTMENT_NOT_ASSIGNED || $this->para_status_id != APPOINTMENT_CANCELLED ) && !isset($this->vehicle_id) && empty($this->vehicle_id))  {
                    $validator->errors()->add('vehicle_id', 'Appointment vehicle number is required.');
                }else if ($this->para_status_id == APPOINTMENT_NOT_ASSIGNED && $this->vehicle_id > 0) {
                    $validator->errors()->add('para_status_id', "Appointment status can not be 'Pending Assignment'.");
                }
            }
            if(!empty($this->vehicle_id)){
                $countVehicle = VehicleDriverMappings::where("vehicle_id",$this->vehicle_id)->count();
                if($countVehicle == 0){
                    $validator->errors()->add('vehicle_id', "Please Map your vehicle with driver first.");   
                }
            }
        });
    }
    public function attributes()
    {
        return [
            'app_date_time' => 'appointment date time',
            'para_status_id'  => 'status',
            "vehicle_id" => "vehicle number"
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""
        ], SUCCESS));
    }
}
