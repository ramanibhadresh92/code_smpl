<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use Carbon\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class InwardLadger extends Model implements Auditable
{
    protected 	$table 		=	'inward_ladger';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;
	
    /*
	Use 	: Add Inward of product for stock
	Author 	: Axay Shah
	Date 	: 23 Aug,2019
    */
    public static function AddInward($request){
    	$Inward                =  new self();
    	$Inward->product_id    =  $request['product_id'];
    	$Inward->quntity 	   =  (isset($request['quntity']) && !empty($request['quntity'])) ? $request['quntity'] : 0 ;
    	$Inward->type 		   =  (isset($request['type']) && !empty($request['type'])) ? $request['type'] : NULL ;
    	$Inward->mrf_id 	   =  (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0 ;
        $Inward->company_id    =  Auth()->user()->company_id;
        $Inward->inward_date   =  date("Y-m-d");
        $Inward->created_by    =  Auth()->user()->adminuserid;
 		$Inward->save();
    }

    

}
