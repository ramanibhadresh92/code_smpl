<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerCollectionTags extends Model
{
    protected 	$table 		=	'customer_collection_tags';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public      $timestamps =   false;


        public function collectionImg()
        {
            return $this->belongsTo(CollectionTags::class,'tag_id');
        }



    public static function add($request){
        $collectionTag = '';
        if(isset($request->customer_id) && !empty($request->customer_id)){
            if(isset($request->customer_collection_tags) && !empty($request->customer_collection_tags))
            {
                if(is_array($request->customer_collection_tags)){
                    $collectionTag = $request->customer_collection_tags;
                }else{
                    $collectionTag = json_decode($request->customer_collection_tags);
                }
                self::deleteCustomerTags($request->customer_id);
                for($i=0;$i<count($collectionTag);$i++){
                    self::createCustomerCollectionTags($collectionTag[$i],$request->customer_id);
                }
            }
        }
    }
    public static function createCustomerCollectionTags($tagId,$customerId){
        $cityId = CustomerMaster::where("customer_id",$customerId)->value('city');
        return self::insert(['tag_id'=>$tagId,'customer_id'=>$customerId,"city_id"=>$cityId,"company_id"=>Auth()->user()->company_id]);
    }
    public static function deleteCustomerTags($customerId){
        return self::where('customer_id',$customerId)->delete();
    }



    

}
