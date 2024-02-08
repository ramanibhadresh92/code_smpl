<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class WmTransferProduct extends Model implements Auditable
{
    protected 	$table 		=	'wm_transfer_product';
	protected 	$primaryKey =	"id"; // or null
	public 		$timestamps = 	false;
	protected 	$guarded    = ["id"];
	use AuditableTrait;

	/*
	Use 	:   Insert record for transfer product
	Author 	: 	Axay Shah
	Date 	: 	08 Aug,2019
	*/
	public static function AddTransferProduct($transferId = 0,$productId = 0,$quantity =0,$received_Qty = 0){
		$Add = array(
			"transfer_id" 	=>  $transferId,
			"product_id" 	=>  $productId,
			"quantity" 		=>  $quantity,
			"received_qty" 	=>  $received_Qty
		);
		self::create($Add);

	}
}
