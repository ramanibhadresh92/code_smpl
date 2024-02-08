<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\JobWorkMaster;

class JobWorkProductMappingMaster extends Model
{
	protected $table =	'jobwork_outward_product_mapping';
	protected $primaryKey =	'id';

	public function jobwork()
	{
		return $this->belongsTo('App\JobWorkMaster');
	}
	/*
	Use 	: Add product
	Author 	: Upasana	
	Date 	: 6/2/2020
	*/

	public static function Addproduct($productid = 0,$jobworkid=0,$quantity="",$actual_quantity="")
	{
		$id    = 0; 
		$data  = new self();
		$data->product_id	   = $productid;
		$data->jobwork_id	   = $jobworkid;
		$data->quantity        = $quantity;
		$data->actual_quantity =  $actual_quantity;
   
		if($data->save())
		{
			$id = $data->id;
			return $id;
		}
	}
}
