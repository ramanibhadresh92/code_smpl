<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\CompanyMaster;
use App\Models\AdminUser;
use Validator;
class CompanyFranchiseController extends LRBaseController
{
    /**
     * list
     *
     * Behaviour : Public
     *
     * @param : post size parameter for per page record, companyId, companyName, companyCode, companyEmail, contactNumber, status (Active/In-active), period (1,2,3), startDate, endDate 
     *
     * @defination : Display list of company
     **/
    public function list(Request $request)
    {
        $data       = [];
        $msg        = trans('message.RECORD_FOUND');

        $validation = Validator::make($request->all(), [
            'companyId' => 'sometimes',
            'size'      => 'sometimes',  
        ]);
        if ($validation->fails()) {
            return response()->json(["code" => INTERNAL_SERVER_ERROR , "msg" => $validation->messages(),"data" => ""
            ]);
        }
        try {
            
            $data = CompanyMaster::getCompanyList($request);
            if($data->isEmpty()){
                $msg = trans('message.RECORD_NOT_FOUND');
            }
        }
        catch (\Exception $e) {
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
        }
        
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
   	public function create(Request $request)
    {
        $data                   = [];
        $msg                    = trans('message.RECORD_FOUND');
        return CompanyMaster::addCompany($request->all());
    }
    /**
     * edit
     * @param : post company_email, company_name, company_owner_name, address1, status, city, state, username, password, zipcode parameters in     order to add company
     * @defination : Add company/franchise
     **/
    public function getById(Request $request)
    {
        $data                   = [];
        $msg                    = trans('message.RECORD_FOUND');
        return CompanyMaster::getCompanyDetails($request->company_id,$request->all());
    }
    /**
     * use  : Update company
     * Date : 12 Sep,2018  
    */    
    public function update(Request $request)
    {
        $data                   = [];
        $msg                    = trans('message.RECORD_FOUND');
        return CompanyMaster::editCompany($request->all());
    }
    
}
