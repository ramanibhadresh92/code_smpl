<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class BailOutwardLedger extends Model implements Auditable
{
    protected 	$table 		=	'bail_outward_ledger';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	/*
	Use 	: Add product bail outward data
	Author 	: Axay Shah
	Date 	: 08 Jan 2019
	*/

	public static function AddBailOutWard($productId,$bailQty,$bailType,$bailMasterId,$MRFID,$dispatchID,$OutwardDate){
		$id 						= 0;
		$self 						= new self();
		$self->product_id 			= $productId;
		$self->bail_qty 			= $bailQty;
		$self->bail_type 			= $bailType;
		$self->bail_master_id 		= $bailMasterId;
		$self->mrf_id 				= $MRFID;
		$self->dispatch_id 			= $dispatchID;
		$self->bail_outward_date 	= $OutwardDate;
		$self->company_id 			= Auth()->user()->company_id;
		$self->created_by			= Auth()->user()->adminuserid;
		if($self->save()){
			$id = $self->save();
		}
		return $id;
	}
}
