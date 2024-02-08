<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\AdminUser;
use App\Models\ViewCollectionSearch;
use App\Models\MasterCodes;
class GroupMaster extends Authenticatable
{
    //
    protected 	$table 		=	'groupmaster';
    protected 	$primaryKey =	'group_id'; // or null
    protected 	$guarded 	=	['group_id'];
    /*
    Use     : Scope query for GroupMaster model
    Author  : Axay Shah
    Date    : 22 Nov,2018
    */
    public function scopeCompany($query){
        return $query->where('company_id',Auth()->user()->company_id);
    }
    public function scopeGroupStatus($query){
        return $query->where('status','Active');
    }
    /**
    * Use       : Get user list
    * Author    : Axay Shah
    * Date      : 31 Aug,2018
    */

    public function userType()
    {
        return $this->hasMany(AdminUser::class,'user_type');
    }
    
    public static function getUserType(){
		return self::where('company_id',Auth()->user()->company_id)->where('status','Active')->select('group_id','group_desc','group_code')->get();
	}
    public static function addDefaultGroup($company_id){
        $groupObj = self::create([
            'group_desc'    => 'Admin',
            'company_id'    => $company_id,
            'status'        => 'Active',
            'group_code'    => 'A',
            'created_by'    => Auth()->user()->adminuserid,
            'updated_by'    => Auth()->user()->adminuserid,
        ]);
        return $groupObj->group_id;
    }
    public static function insertOtherUserType($company_id){
        
        $groupInsert = self::whereIn("group_code",array(FRU,CRU,GDU,CLFS,SUPV,CLAG))->groupBy('group_code')->get();
        if($groupInsert){
            foreach($groupInsert as $g){
                $groupObj = self::create([
                    'group_desc'    => $g->group_desc,
                    'company_id'    => $company_id,
                    'status'        => $g->status,
                    'group_code'    => $g->group_code,
                    'created_by'    => Auth()->user()->adminuserid,
                    'updated_by'    => Auth()->user()->adminuserid,
                ]);
            }
        }
    }
    /**
    * Use       : Get group by id
    * Author    : Axay Shah
    * Date      : 22 Nov,2018
    */
    public static function retriveGroupById($code){
        return self::where('group_id',$code)->company()->groupStatus()->first();
    }
    /**
    * Use       : Get group by Code
    * Author    : Axay Shah
    * Date      : 22 Nov,2018
    */
    public static function retriveGroupByCode($code = ""){
        return self::whereIn('group_code',array($code))->company()->groupStatus()->get();
    }

    public static function AddOrUpdateGroup($request){
        $id                 = (isset($request['group_id']) && !empty($request['group_id']))? $request['group_id']  : 0;
        if($id > 0){
            $add = self::find($id);
        }else{
            $add              = new self;
            $add->created_by  = Auth()->user()->adminuserid;  
        }
        if($id == 0){
            $code               = MasterCodes::getMasterCode(GROUP_CODE);
            $code_number        = $code->code_value + 1;
            $add->group_code    = $code->prefix.$code_number;   
        }
        
        
        $add->group_desc    = (isset($request['group_desc']) && !empty($request['group_desc']))? $request['group_desc']  : "";    
        $add->company_id    = (isset($request['company_id']) && !empty($request['company_id']))? $request['company_id']  : Auth()->user()->company_id;    
        $add->status        = (isset($request['status']    ) && !empty($request['status']    ))? $request['status']      : "Active";        
         
        $add->updated_by    = Auth()->user()->adminuserid;   
        if($add->save()){
            if(empty($id)){
                MasterCodes::updateMasterCode(GROUP_CODE,$code_number);
            }
            
            return $add->group_id;
        }
        return $id;
    }
    /**
    * Use       : Group List
    * Author    : Axay Shah
    * Date      : 30 Nov,2022
    */
    public static function ListGroupMaster($request){
        $Group                  = (new static)->getTable();
        $Today                  = date('Y-m-d');
        $sortBy                 = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy')    : "id";
        $sortOrder              = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
        $recordPerPage          = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
        $pageNumber             = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
        $data                   = self::select("$Group.*");
        if($request->has('params.group_id') && !empty($request->input('params.group_id')))
        {
            $id     = $request->input('params.group_id');
            if(!is_array($request->input('params.group_id'))){
                $id = explode(",",$request->input("params.group_id"));
            }
            $data->where("$Group.group_id",$id);
        }
        if($request->has('params.group_desc') && !empty($request->input('params.group_desc')))
        {
            $data->where("$Group.group_desc","like","%".$request->input('params.group_desc')."%");
        }
        if($request->has('params.group_code') && !empty($request->input('params.group_code')))
        {
            $data->where("$Group.group_code","like","%".$request->input('params.group_code')."%");
        }
        if($request->has('params.status') && !empty($request->input('params.status')))
        {
            $data->where("$Group.status",$request->input('params.status'));
        }
        $data->where("company_id",Auth()->user()->company_id);
        $result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
        return $result;
    }
    
}
