<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\AdminTransaction;
use App\Models\AdminUserRights;
use App\Facades\LiveServices;
class AdminTransactionGroups extends Authenticatable
{
	protected 	$table 		=	'admintrngroups';
	protected 	$guarded 	=	['trngroupid'];
	protected 	$primaryKey =	'trngroupid'; // or null

	/**
	 * Get Admintrnasaction by TransactionGroupId.
	 * Author 	: Axay Shah
	 * Date 	: 24 Aug,2018
	 */
	public function transaction()
	{
		return $this->hasMany('App\Models\AdminTransaction','trngroupid');
	}
	public function transactionData()
	{
		return $this->hasMany('App\Models\AdminTransaction','trngroupid');
	}
	/**
	 * get Transection group id by module id
	 * Author 	: Axay Shah
	 * Date 	: 
	 */
	public static function getTrnGroupByModule($moduleIds){
		return self::whereIn('module_id',array($moduleIds))->get();
	}
	
	/**
	* Function      : getMasterGroupID
	* @param        : integer $TrngroupId
	* @defination   : Get Master Group ID
	* Author        : Kalpak Prajapati
	**/
	public static function getMasterGroupID($TrngroupId=0)
	{
		$ReportQuerySql =  self::select("admintrngroups.trngroupid","admintrngroups.parent_id")->where('admintrngroups.trngroupid',$TrngroupId);
		$AdminTransactionGroups = $ReportQuerySql->first();
		if (isset($AdminTransactionGroups->parent_id)) {
			if($AdminTransactionGroups->parent_id == 0) {
				return $AdminTransactionGroups->trngroupid;
			} else {
				return self::getMasterGroupID($AdminTransactionGroups->parent_id);
			}
		}
	}
}