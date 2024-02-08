<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Classes\AwsOperation;
use App\Models\AdminUser;
use App\Models\Helper;
class UpdateDriver extends FormRequest
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
            'username'      => 'required|unique:adminuser,username,'.$this->adminuserid.',adminuserid',
            // 'password'      => "required|min:6",
            'mobile'        => "required|digits:10|unique:adminuser,mobile,".$this->adminuserid.",adminuserid",
            'firstname'     => 'required',
            'lastname'      => 'required',
            'city'          => 'required',
            'zip'           => 'required',
            'task_groups'   => 'required',
            'CFM_CODE'      => 'unique:adminuser,CFM_CODE,'.$this->adminuserid.',adminuserid|unique:helper_master,CFM_CODE'
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
            if($this->hasFile('profile_photo')){
                $awsResponse    = AwsOperation::searchFacesByImage($this->file('profile_photo'),env('AWS_DRIVER_COLLECTION'));
                if($awsResponse && isset($awsResponse['FaceMatches'][0]['Face']['FaceId'])){
                    $faceId         =  $awsResponse['FaceMatches'][0]['Face']['FaceId'];
                    if(!empty($faceId)){
                        $Face = Adminuser::where("face_id",$faceId)->whereNotIn("adminuserid",[$this->adminuserid])->first();
                        if($Face){
                            $validator->errors()->add('profile_photo',
                            'Profile photo already in user. Please upload unique photo'); 
                        }
                    }
                }   
            }
        });
    }
    public function attributes()
    {
        return [
            // 'app_date_time' => 'appointment date time',
            // 'para_status_id'  => 'status',
            // "vehicle_id" => "vehicle number",
            'CFM_CODE' => 'CFM CODE',
        ];
    }
}
