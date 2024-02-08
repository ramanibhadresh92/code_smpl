<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskGroup extends Model
{
    //
    protected 	$table 		=	'task_group';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    public 		$timestamps = 	true;
    

    /*
	Use 	: Get user task group of company with defualt task group
	Author 	: Axay Shah

	*/
	public static function getTaskGroup(){
	return 	self::select('id', 'task_name','task_value','sort_order','company_id')
		// ->where(function($q) {
		// 	$q->orWhereNull('company_id')->where("company_id",auth()->user()->company_id);
			
		// })
		->where("status","A")
		->orderBy("sort_order","ASC")
		->get();
	}
}
