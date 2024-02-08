<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\CustomerMaster;
class DifferenceMappingMaster extends Model implements Auditable
{
    protected 	$table 		=	'difference_mapping_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	/*
	Use 	: Get Vehicle Difference Mapping master list
	Auth 	: Axay Shah
	Date 	: 14 Oct,2019
	*/
	public static function GetDifferenceMappingList(){
		$Customer 	= new CustomerMaster();
		$self 		= (new static)->getTable();
		$data 		= self::select("$self.id","$self.customer_id","$self.company_id","$self.status","$self.trip",\DB::raw("CONCAT(CUS.first_name,' ',CUS.last_name) as customer_name"))
		->join($Customer->getTable()." AS CUS","$self.customer_id","=","CUS.customer_id")
		->where("$self.company_id",Auth()->user()->company_id)
		->where("$self.status",1)
		->get();
		return $data;
	}
}
