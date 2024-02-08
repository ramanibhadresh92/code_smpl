<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Auth;
class WmClientPurchaseOrderStopLog extends Model
{
	protected 	$table 		=	'wm_client_master_po_stopped_log';
	protected 	$guarded 	=	['id'];
	protected 	$primaryKey =	'id'; // or null
	public 		$timestamps = 	false;
}