<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAvgCollection extends Model
{
    protected 	$table 		=	'customer_avg_collection';
    // protected 	$primaryKey =	'customer_id'; // or null
    // protected 	$guarded 	=	['customer_id'];
    // public      $timestamps =   true;

    public static function getCustomerAverageCollectionTime($customer_id){
        $collAvgTime = 0;
		$customerId	= intval($customer_id);
		if (!empty($customerId)) {
            $customer= self::where('customer_id',$customerId)->select('app_time as coll_avg_time')->first();
			if($customer) {
				$collAvgTime = (isset($customer->coll_avg_time)?$customer->coll_avg_time:0);
			}
		}
		return $collAvgTime;
    }

}
