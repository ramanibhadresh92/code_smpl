<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\CustomerTripRateMaster;
use App\Facades\LiveServices;
use Validator;
use DB;
use JWTAuth;
use Log;
class CustomerTripRateMaster extends Model
{
    protected   $table          = 'customer_trip_rate_master';
    public      $timestamps     = false;
    protected   $primaryKey     = 'id'; 
    protected   $guarded        = ['id']; 

   

}
