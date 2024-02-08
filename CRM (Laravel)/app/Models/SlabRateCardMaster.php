<?php

namespace App\Models;

use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\CompanyProductMaster;
class SlabRateCardMaster extends Model implements Auditable
{
    protected   $table      =   'slab_rate_card_master';
    protected   $primaryKey =   'id'; // or null
    protected   $guarded    =   ['id'];
    public      $timestamps =   false;
    use AuditableTrait;
    
    /*
    Use     : Get Data of slab rate by id
    Author  : Axay Shah
    Date    : 04-04-2023
    */
    public static function GetSlabRateByID($slab_id=0){
        $data = self::leftjoin("company_product_master","slab_rate_card_master.product_id","=","company_product_master.id")
        ->leftjoin("company_product_quality_parameter","company_product_master.id","=","company_product_quality_parameter.product_id")
        ->select(
            "slab_rate_card_master.product_id",
            "slab_rate_card_master.min_qty",
            "slab_rate_card_master.max_qty",
            \DB::raw("CONCAT(company_product_master.name,' ',company_product_quality_parameter.parameter_name) as product_name")
        )
        ->where("slab_rate_card_master.status",1)
        ->where("slab_rate_card_master.slab_id",$slab_id)
        ->where("company_product_master.para_status_id",PRODUCT_STATUS_ACTIVE)
        ->get()
        ->toArray();
        return $data;
    }

    /*
    Use     : Get FOC Product Data when no slab to user
    Author  : Axay Shah
    Date    : 04-04-2023
    */
    public static function GetFocProductDataByID($product_id=FOC_PRODUCT){
        $data = CompanyProductMaster::leftjoin("company_product_quality_parameter","company_product_master.id","=","company_product_quality_parameter.product_id")
        ->select(
            "company_product_master.id as product_id",
            \DB::raw("CONCAT(company_product_master.name,' ',company_product_quality_parameter.parameter_name) as product_name")
        )
        ->where("company_product_master.id",FOC_PRODUCT)
        ->where("company_product_master.para_status_id",PRODUCT_STATUS_ACTIVE)
        ->get()
        ->toArray();
        if(!empty($data)){
            foreach($data as $key => $value){
                $data[$key]['min_qty'] = 0;
                $data[$key]['max_qty'] = 0;
            }
        }
        return $data;
    }
}