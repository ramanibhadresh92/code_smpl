<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductTripMapping;
use App\Facades\LiveServices;
use Validator;
use DB;
use JWTAuth;
use Log;
class ProductTripMapping extends Model
{
    protected   $table          = 'product_trip_mapping';
    public      $timestamps     = false;
    protected   $primaryKey     = 'id'; 
    protected   $guarded        = ['id']; 

   

}
