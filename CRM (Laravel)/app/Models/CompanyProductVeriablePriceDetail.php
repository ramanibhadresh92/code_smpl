<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProductVeriablePriceDetail extends Model
{
    //
    protected 	$table 		=	'company_product_veriable_price_details';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    public 		$timestamps = 	true;

    /*
    Use     : Delete product veriable detail by details id
    Author  : Axay Shah 
    Date    : 24 Sep,2018
    */
    public static function deleteByProductId($detailId){
    	return self::where('details_id',$detailId)->delete();
    }
    /*
    Use     : get veriable price group detail by details id 
    Author  : Axay Shah 
    Date    : 25 Sep,2018
    */
    public static function productVeriableDetail($detailId){
    	return self::where('details_id',$detailId)->get();
    }
}
