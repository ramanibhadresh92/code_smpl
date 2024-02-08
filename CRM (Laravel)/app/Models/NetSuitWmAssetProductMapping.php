<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use DB;
class NetSuitWmAssetProductMapping extends Model implements Auditable
{
	//
	protected 	$table 		=	'net_suit_wm_asset_product_mapping';
	protected 	$primaryKey =	"id"; // or null
	protected 	$guarded 	=	["id"];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts = [

    ];



}
