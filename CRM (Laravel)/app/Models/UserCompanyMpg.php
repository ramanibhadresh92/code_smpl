<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Model\AdminUser;
class UserCompanyMpg extends Model
{
    //
    protected 	$table 		=	'user_company_mpg';
    protected 	$guarded 	=	['id'];
    protected   $timestamp  =   true;
    protected 	$primaryKey =	'id'; // or null


    public function usercitys(){
        return $this->belongsTo(AdminUser::class,'adminuserid');
    }
    /**
     * setUserCompany
     *
     * Behaviour : Public
     *
     * @param : 
     *
     * @defination : 
     **/
    public static function setUserCompany()
    {
    	$page_data=self::select('*')->where('pageurl',$pageurl)->first();
    	if(!empty($page_data))
    	{
    		return $page_data->trnid;
    	}
    	return false;
    }
}