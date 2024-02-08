<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class LrEprMappingMaster extends Model implements Auditable
{
    protected 	$table 		=	'lr_epr_mapping_master';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    public 		$timestamps = 	true;
    use AuditableTrait;
 
}


