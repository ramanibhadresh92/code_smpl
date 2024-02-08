<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTokenInfo extends Model
{
    protected 	$table 		=	'user_token_info';
	protected 	$guarded 	=	['id'];
	protected 	$primaryKey =	'id'; // or null
	public 		$timestamps = 	true;
	
}
