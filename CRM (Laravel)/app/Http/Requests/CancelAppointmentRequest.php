<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Appoinment;
use App\Models\AppointmentCollection;
use App\Models\CustomerMaster;
use App\Facades\LiveServices;

class CancelAppointmentRequest extends FormRequest
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
            'appointment_id'           => 'required',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $AppointmentCollection      = Appoinment::select('appoinment.appointment_id')
                                          ->LEFTJOIN("appointment_collection as AC","appoinment.appointment_id","=","AC.appointment_id")
                                          ->whereIn("appoinment.appointment_id",$this->appointment_id)
                                          ->where('AC.audit_status','=',AUDIT_STATUS)
                                          // ->where(function ($query) {
                                        //  $query->where('appoinment.para_status_id','=',APPOINTMENT_COMPLETED)
                                        //        ->where('AC.audit_status','=',AUDIT_STATUS);
                                        //  })
                                        ->groupBy('appoinment.appointment_id');
            $AppointmentCollectionCount = $AppointmentCollection->count();
            $AppointmentCollectionData  = $AppointmentCollection->pluck('appoinment.appointment_id')->toArray();
            /* If appointment Request Id is Empty*/
            if (isset($this->appointment_id) && is_array($this->appointment_id) && empty($this->appointment_id)) {
                $validator->errors()->add('appointment_id', 'Please Select Appointment Id.');
            }
            if (isset($this->appointment_id) && is_array($this->appointment_id) && (!empty($this->appointment_id))) {
                if(!empty($AppointmentCollectionData)){
                    $AppointmentAuditData = implode(',',$AppointmentCollectionData);
                    $RequesterrorMessage  = trans("message.APPOINTMENT_CANCEL_VALIDATION_MSG",array("AppointmentAuditData" => $AppointmentAuditData));
                    $validator->errors()->add('appointment_id', $RequesterrorMessage);    
                }   
            }
        });
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""
        ], SUCCESS));
    }
}
