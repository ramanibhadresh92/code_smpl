<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class MasterPriceGroupRequest extends FormRequest
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
        
        switch($this->method())
        {
            case 'POST':
            {
                return  [
                    'group_value'       => 'required',
                    'group_desc'        => 'required',
                    'group_tech_desc'   => 'required',
                    'group_type'        => 'required|in:F,V',
                    'flag_show'         => 'sometimes|in:0,1',
                    'sort_order'        => 'sometimes|in:0,1,2',
                    'status'            => 'sometimes|in:A,I',
                ];
            }
            case 'PUT':
            {
                return  [
                    'id'                => 'bail|required|exists:price_group_master,id',
                    'group_value'       => 'required',
                    'group_desc'        => 'required',
                    'group_tech_desc'   => 'required',
                    'group_type'        => 'required|in:F,V',
                    'flag_show'         => 'sometimes|in:0,1',
                    'sort_order'        => 'sometimes|in:0,1,2',
                    'status'            => 'sometimes|in:A,I',
                ];
               
            }
            default:break;
        }
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(response()->json(['code' => VALIDATION_ERROR,'msg' => $errors,"data"=>""
        ], SUCCESS));
    }
}
