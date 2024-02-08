<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Log;
use App\Models\AdminTransactionGroups;
use App\Models\AdminUserRights;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class AdminTransaction extends Model implements Auditable
{
    //
    
    protected 	$table 		=	'admintransaction';
    protected 	$guarded 	=	['trngroupid'];
    protected 	$primaryKey =	'trngroupid'; // or null
    use AuditableTrait;
    /**
     * Get TransectionGroup by TransactionGroupId.
     * Author   : Axay Shah
     * Date     : 24 Aug,2018
     */
    public function transactionGroup(){
        return $this->belongsTo('App\Models\AdminTransactionGroups','trngroupid','trngroupid');
    }
    /**
     * Get user by transactionid
     * Author   : Axay Shah
     * Date     : 05 Sep,2018
     */
    public function current_user_rights(){
        return $this->hasMany(AdminUserRights::class,'trnid');
    }
    /**
     * getTrnidFromPageurl
     *
     * Behaviour : Public
     *
     * @param : passed request api url in pageurl   :
     *
     * @defination : Method is use fetch trnid from admintransaction table where pageurl equal to passed pageurl
     **/
    public static function getTrnidFromPageurl($pageurl)
    {
        $page_data=self::select('trnid')->where('pageurl',$pageurl)->where('showtrnflg','Y')->first();
    	if(!empty($page_data))
    	{
    		return $page_data->trnid;
    	}
    	return false;
    }
    /**
     *  Function    : getAdminTrnByGroup
     *  Behaviour   : Public
     *  @param      : passed transectionGroupId in request 
     *  @defination : is use to fetch record by its transection group id
     *  Author      : Axay Shah
     **/
    public static function getAdminTrnByGroup($groupIds)
    {
        return self::whereIn('trngroupid',array($groupIds));
    }
}
