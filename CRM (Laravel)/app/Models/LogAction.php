<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;

class LogAction extends Model
{
	
	protected 	$table              = 'log_action';
	protected 	$guarded            = ['log_action_id'];
	protected 	$primaryKey         = 'log_action_id'; // or null
	public 		$timestamps 		= false;



	public static function LogActionListing(){
		return self::all();
	}
}