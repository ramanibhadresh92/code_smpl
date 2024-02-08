<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use DB;
use App\Facades\LiveServices;
use App\Models\InwardPlantDetails;
use App\Models\InwardLadger;
use App\Models\ProductInwardLadger;
class InwardSegregationMaster extends Model implements Auditable
{
    protected 	$table 		=	'inward_segregation_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;

	/*
	Use 	:  Add Sagregation Master data
	Date 	:  19 Dec,2019
	Author 	:  Axay Shah 
	*/
	public static function AddSegregation($productId,$shift_id,$qty = 0,$mrf_id=0,$inward_date = '',$FromProductSorting=0){
		$id 						= 0;
		$add 						= new self();
		$add->inward_date 			= $inward_date;
		$add->product_id 			= $productId;
		$add->shift_id 				= $shift_id;
		$add->qty 					= $qty;
		$add->mrf_id 				= $mrf_id;
		$add->from_product_sorting 	= $FromProductSorting;
		$add->company_id 			= Auth()->user()->company_id;
		$add->created_by 			= Auth()->user()->adminuserid;
		if($add->save()){
			$id = $add->id;
			ProductInwardLadger::CreateInWard($productId,$qty,TYPE_INWARD,PRODUCT_PURCHASE,$mrf_id,$inward_date,0,$id);
		}
		
   		return $id;
	}



	/*
	Use 	:  Edit Sagregation Master data
	Date 	:  02 Jan,2019
	Author 	:  Axay Shah 
	*/
	public static function EditSegregation($ID = 0,$productId,$qty = 0,$mrf_id=0,$inward_date = '',$FromProductSorting=0){
		$add 				= self::find($ID);
		if($add){
			$add->inward_date 			= $inward_date;
			$add->product_id 			= $productId;
			$add->qty 					= $qty;
			$add->mrf_id 				= $mrf_id;
			$add->from_product_sorting 	= $FromProductSorting;
			$add->company_id 			= Auth()->user()->company_id;
			$add->updated_by 			= Auth()->user()->adminuserid;
			$add->save();
			ProductInwardLadger::CreateInWard($productId,$qty,TYPE_INWARD,PRODUCT_PURCHASE,$mrf_id,$inward_date,0,$ID);
		}
		return $ID;
	}

	/*
	Use 	:  Listing Sagregation 
	Date 	:  23 Dec,2019
	Author 	:  Axay Shah 
	*/
	public static function ListInwardSegregation($request,$groupBy = true){
		
		$product 		= new CompanyProductMaster();
		$MRF 			= new WmDepartment();
		$self 			= (new static)->getTable();
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "$self.id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size')   : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$cityId         = GetBaseLocationCity();	

		$data 			= 	self::select(
									DB::raw("SUM($self.qty) as total_qty"),
									"PRO.name as product_name",
									"$self.*",
									"MRF.department_name"
							)
		->join($product->getTable()." AS PRO","$self.product_id","=","PRO.id")
		->join($MRF->getTable()." AS MRF","$self.mrf_id","=","MRF.id");


		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$data->where("$self.mrf_id",$request->input('params.mrf_id'));
		}

		if($request->has('params.product_id') && !empty($request->input('params.product_id')))
		{
			$data->where("$self.product_id",$request->input('params.product_id'));
		}

		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
		{
			$data->whereBetween("$self.inward_date",array(date("Y-m-d", strtotime($request->input('params.startDate'))),date("Y-m-d", strtotime($request->input('params.endDate')))));
		}else if(!empty($request->input('params.startDate'))){
		   	$datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
		   	$data->whereBetween("$self.inward_date",array($datefrom,$datefrom));
		}else if(!empty($request->input('params.endDate'))){
			$dateEnd = date("Y-m-d", strtotime($request->input('params.endDate')));
		   	$data->whereBetween("$self.inward_date",array($dateEnd,$dateEnd));
		}

		if($groupBy){
			$data->groupBy("$self.product_id");
			$data->groupBy("$self.mrf_id");
			$data->groupBy("$self.inward_date");	
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage);
		}else{
			// LiveServices::toSqlWithBinding($data);
			$result =  $data->orderBy('id')->groupBy('id')->get();
		}
		
		// LiveServices::toSqlWithBinding($data);
		
		return $result;

	}

	/*
	Use 	:  Update Segeregation Inward
	Date 	:  17 Jan,2020
	Author 	:  Axay Shah 
	*/
	public static function updateSegeregationStock(){
		$date  			= 	(!empty($date)) ? date("Y-m-d",strtotime($date)) : date("Y-m-d");
		$PlantInwardQty =	0;
		$GetPlantDetails= 	InwardPlantDetails::select(\DB::raw("sum(inward_qty) as totalQty"),"mrf_id","id","inward_date","company_id")
							->Where("inward_date",$date)
							->groupBy("mrf_id")
							->get()
							->toArray();
		if(!empty($GetPlantDetails)){
			foreach($GetPlantDetails as $raw){
				$TotalInwardPlantSum 	= (isset($raw['totalQty'])) ? $raw['totalQty'] : 0;
				$ProductInward 			= self::select(\DB::raw("sum(qty) as totalProductQty"),"product_id","mrf_id")
				->where("mrf_id",$raw['mrf_id'])
				->where("inward_date",$date)
				->groupBy('product_id')
				->get()
				->toArray();
				$PlantInwardQty 		= $TotalInwardPlantSum; 
				if(!empty($ProductInward)){
					foreach($ProductInward as $res){
						$productQty 		= (!empty($res['totalProductQty'])) ? $res['totalProductQty'] : 0;
						$PlantInwardQty 	= $PlantInwardQty - $productQty;
						$array = array(
							"product_id" 	=> $res['product_id'],
							"quantity" 		=> $productQty,
							"type" 			=>"P" ,
							"product_type" 	=>"1" ,
							"inward_date" 	=>$date ,
							"mrf_id" 		=>$res['mrf_id'],
							"company_id" 	=>$raw['company_id'],
							"created_at" 	=>date("Y-m-d H:i:s")
						);
						DB::table('inward_ledger')->insert($array);
					}
				}

				if($PlantInwardQty > 0){
						$PlantArray = array(
							"product_id" 	=> FOC_PRODUCT,
							"quantity" 		=> $PlantInwardQty,
							"type" 			=>"P" ,
							"product_type" 	=>"1" ,
							"inward_date" 	=>$date ,
							"mrf_id" 		=>$raw['mrf_id'],
							"company_id"	=>$raw['company_id'],
							"created_at" 	=>date("Y-m-d H:i:s")
						);
						DB::table('inward_ledger')->insert($PlantArray);
					}
			}
		}
		return "Updated Successfully";
	}

	/*
	Use 	: Inward Total Number of trip report
	Author 	: Axay Shah
	Date 	: 26 March 2020
	*/
	public static function InwardInputOutputReport($request){
		$cityId         = GetBaseLocationCity();
		$product  		= new CompanyProductMaster();
		$MRF  			= new WmDepartment();
		$ProductQuality = new CompanyProductQualityParameter();
		$AdminUser 		= new AdminUser();
		$self 			= (new static)->getTable();
		$result 		= array();
		$Month     		= !empty($request->input('month')) 	? $request->input('month')  : date('m');
		$Year      		= !empty($request->input('year')) 	?  $request->input('year')  : date('Y');
		$startDate 		= $Year."-".$Month."-01";
		$endDate 		= date("Y-m-t", strtotime($startDate));
		$DAYS 			= date("t", strtotime($startDate));
		$TOTAL_PERCENT  = 0;
		$RESU 			= array();
		$list 			= self::select(
							\DB::raw("SUM($self.qty) as total_qty"),
							\DB::raw("$self.inward_date"),
							\DB::raw("$self.mrf_id"),
							\DB::raw("$self.product_id"),
							"MRF.department_name as mrf_name",
							\DB::raw("CONCAT(P.name,' ',QAL.parameter_name) AS product_name")
						)
		->join($MRF->getTable()." as MRF","$self.mrf_id","=","MRF.id")
		->join($product->getTable()." as P","$self.product_id","=","P.id")
		->join($ProductQuality->getTable()." as QAL","P.id","=","QAL.product_id");
		$list->whereIn("MRF.location_id",$cityId);
		if($request->has('mrf_id') && !empty($request->input('mrf_id')))
		{
			$list->where("$self.mrf_id",$request->input('mrf_id'));
		}
		if(!empty($startDate) && !empty($endDate))
		{
			$list->whereBetween("$self.inward_date",array($startDate,$endDate));
		}
		$result = $list->groupBy("$self.product_id")->get();
		
		if(!empty($result)){
			foreach($result as $value){
				$RESPONSE['product_id'] 	= $value['product_id'];
				$RESPONSE['mrf_id'] 		= $value['mrf_id'];
				$RESPONSE['mrf_name'] 		= $value['mrf_name'];
				$RESPONSE['product_name'] 	= $value['product_name'];
				$RESPONSE['total_qty'] 		= $value['total_qty'];
				$ROW =  self::select("product_id",\DB::raw("SUM(qty) as qty"),
							\DB::raw("DATE_FORMAT(inward_date,'%d') as day"))
							->whereBetween("inward_date",array($startDate,$endDate))
							->where("product_id",$value['product_id'])
							->groupBy("inward_date")
							->groupBy("product_id")
							->orderBy("inward_date")
							->get();
					if(!empty($ROW)){
						$ARRAY = array();
						foreach($ROW AS $RES){
							$DAY 						= $RES['day'];
							$QUNTITY 					= ($RES['qty'] > 0) ? _FormatNumberV2($RES['qty']) : 0;
							$PERCENT 					= _FormatNumberV2(($QUNTITY / $value['total_qty']) * 100);
							$ARRAY[$DAY]['day'] 		= $DAY;
							$ARRAY[$DAY]['quantity'] 	= $QUNTITY;
							$ARRAY[$DAY]['percentage']	= $PERCENT;
							$TOTAL_PERCENT 				+= $PERCENT;
						}
					}
					$RESPONSE['total_percent'] 	= $TOTAL_PERCENT;
					$RESPONSE['ROW'] 			= $ARRAY;
					$RESU[] = $RESPONSE;
				}
			}
		return $RESU;
	}

	/*
	Use 	:  Listing Sagregation 
	Date 	:  31 March 2020
	Author 	:  Axay Shah 
	*/
	public static function ListProductSortingSegregation($request,$groupBy = true){
		
		$product 		= new WmProductMaster();
		$MRF 			= new WmDepartment();
		$self 			= (new static)->getTable();
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "$self.id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size')   : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$cityId         = GetBaseLocationCity();	

		$data 			= 	self::select(
									DB::raw("SUM($self.qty) as total_qty"),
									"PRO.title as product_name",
									"$self.*",
									"MRF.department_name"
							)
		->join($product->getTable()." AS PRO","$self.product_id","=","PRO.id")
		->join($MRF->getTable()." AS MRF","$self.mrf_id","=","MRF.id")
		->where("$self.from_product_sorting",1);

		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$data->where("$self.mrf_id",$request->input('params.mrf_id'));
		}

		if($request->has('params.product_id') && !empty($request->input('params.product_id')))
		{
			$data->where("$self.product_id",$request->input('params.product_id'));
		}

		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
		{
			$data->whereBetween("$self.inward_date",array(date("Y-m-d", strtotime($request->input('params.startDate'))),date("Y-m-d", strtotime($request->input('params.endDate')))));
		}else if(!empty($request->input('params.startDate'))){
		   	$datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
		   	$data->whereBetween("$self.inward_date",array($datefrom,$datefrom));
		}else if(!empty($request->input('params.endDate'))){
			$dateEnd = date("Y-m-d", strtotime($request->input('params.endDate')));
		   	$data->whereBetween("$self.inward_date",array($dateEnd,$dateEnd));
		}

		if($groupBy){
			$data->groupBy("$self.product_id");
			$data->groupBy("$self.mrf_id");
			$data->groupBy("$self.inward_date");	
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage);
		}else{
			// LiveServices::toSqlWithBinding($data);
			$result =  $data->orderBy('id')->groupBy('id')->get();
		}
		
		// LiveServices::toSqlWithBinding($data);
		
		return $result;

	}

}
