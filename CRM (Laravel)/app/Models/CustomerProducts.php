<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CustomerMaster;
class CustomerProducts extends Model implements Auditable
{
    protected 	$table 		=	'customer_products';
    protected 	$primaryKey =	'customer_id'; // or null
    protected 	$fillable =	['customer_id','category_id','product_id','product_quality_para_id']; // or null
    public      $timestamps =   false;
    use AuditableTrait;

    public function customerProductCategory(){
        return  $this->belongsTo(CompanyCategoryMaster::class,'category_id');
    }
    public function customerProduct(){
        return  $this->belongsTo(ViewCompanyProductMaster::class,'product_id');
    }
    /*
    Use     : get customer Product
    Author  : Axay Shah
    Date    : 13 Dec,2018
    */
    public static function  retrieveCustomerProducts($customerId=0){
        return self::with(['customerProductCategory'=>function($e) use ($customerId){
            $e->with(['productList'=>function($q) use($customerId){
                $q->join('customer_products','view_company_product_master.id','=','customer_products.product_id');
                $q->where('customer_products.customer_id',$customerId);
            }]);
        }])->where('customer_id',$customerId)
        ->groupBy('category_id')
        ->get();
        
    } 

    /*
    Use     : Save customer product
    Author  : Axay Shah
    Date    : 13 Dec,2018
    */
    public static function  saveCustomerProductMapping($request){
        if(isset($request->customer_id) && !empty($request->customer_id)){
            $delete = self::where('customer_id',$request->customer_id)->delete();
            if(is_array($request->customer_products) && !empty($request->customer_products)){
                foreach($request->customer_products as $map){
                   $map['customer_id']= $request->customer_id;
                   $insert = self::insert([$map]);
                }
                log_action('Customer_Product_Updated',$request->customer_id,"customer_products");
            }
            return true;
        }else{
            return false;
        }
    } 
}
