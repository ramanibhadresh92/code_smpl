<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
class ClientChargesMapping extends Model
{
	//
	protected 	$table 		= 'client_charges_mapping';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;
	protected $casts = [];	
}