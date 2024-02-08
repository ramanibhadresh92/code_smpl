<?php

namespace App\Models;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Model;

class InertDeductionImage extends Model
{
    protected 	$table 		=	'inert_deduction_images';
    protected 	$primaryKey =	'id'; // or null
    public      $timestamps =   true;

    public static function GetInertImages($deduction_id){
            $image = self::where('deduction_id',$deduction_id)->first();
            if(isset($image) && $image->filename !=""){
                return $image->filename;
            }
        return '';
    }
}
