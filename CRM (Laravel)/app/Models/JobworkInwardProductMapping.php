<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\JobWorkMaster;

class JobworkInwardProductMapping extends Model
{
	protected $table 		=	'jobwork_inward_product_mapping';
	protected $primaryKey 	=	'id';
	protected $guarded 		=	['id'];
	public    $timestamps 	=   false;


}
