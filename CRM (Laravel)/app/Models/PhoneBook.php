<?php

namespace App\Models;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Model;

class PhoneBook extends Model
{
    protected 	$table 		=	'phonebook';
    protected 	$guarded 	=	['phonebookid'];

    public static function getPhonebook(){
        return self::where('status',1)->orderBy('name','ASC')->get();
    }


}
