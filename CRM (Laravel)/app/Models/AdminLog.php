<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminLog extends Model
{
    protected $table 		=	'adminlog';
    //protected 	$guarded 	=	['adminuserid'];
    protected $primaryKey =	'logid'; // or null
    //public $timestamps = false;
    protected $fillable = ['adminuserid', 'actionid', 'actionvalue', 'remark', 'ip' , 'created_dt','updated_dt'];
    var $actionLogin                    = 1001;
    var $actionLogout                   = 1002;
    var $actionAddAdminUser             = 1003;
    var $actionViewAdvancedUserRights   = 1004;
    var $actionWelcometoAdminSection   	= 1008;
    var $actionEditAdminUser            = 1005;
}

