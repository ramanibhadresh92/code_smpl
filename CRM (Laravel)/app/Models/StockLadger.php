<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\ProductInwardLadger;
use App\Models\OutWardLadger;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductQualityParameter;
use App\Models\WmDepartment;
use App\Models\WmProductMaster;
use App\Models\MonthlyStockAdjustment;
use App\Models\WmBatchMaster;
use App\Facades\LiveServices;
use DateTime;
use DateInterval;
use DatePeriod;
use view;
use PDF;
class StockLadger extends Model implements Auditable
{
	protected 	$table 		= 'stock_ladger';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public      $timestamps = true;
	public      $FOC_SQL_2D = "";
	public      $FOC_SQL_3D = "";
	use AuditableTrait;

	/*
	Use 	: Update Daily Stock of every product on daily basis
	Author 	: Axay Shah
	Date 	: 26 Aug,2019
	*/
	public static function UpdateStock($date = ""){
		$INWARD 				= 	new ProductInwardLadger();
		$OUTWARD 				= 	new OutwardLadger();
		$DEPARTMENT_TBL 		= 	new WmDepartment();
		$PURCHASE_TBL 			= 	new CompanyProductMaster();
		$PURCHASE_PRO 			= 	$PURCHASE_TBL->getTable();
		$SALES_TBL 				= 	new WmProductMaster();
		$SALES_PRO 				= 	$SALES_TBL->getTable();
		$DEPT 					=   $DEPARTMENT_TBL->getTable();
		$STOCK_DATE  			= 	(!empty($date)) ? date("Y-m-d",strtotime($date)) : date("Y-m-d",strtotime("-1 days"));
		$TotalInword 			=  	0;
		$TotalOutword 			=  	0;
		$OpeningStock 			= 	0;
		$ClosingStock 			= 	0;
		$TotalStockWithInword 	= 	0;
		$TodayInward 			= 	0;
		$TodayOutward 			= 	0;
		$arrCompany    			= 	CompanyMaster::select('company_id')
									->where('status','Active')
									->get();
		if (!empty($arrCompany))
		{
			foreach($arrCompany as $Company)
			{
				$COMPANY_ID = $Company->company_id;
				$Department = WmDepartment::select('id','location_id','base_location_id')
							->where('company_id',$COMPANY_ID)
							->where('is_virtual',0)
							->where('status','1')->get()->toArray();
				if (!empty($Department))
				{
					foreach($Department as $RAW)
					{
						$MRF_ID 			= $RAW['id'];

						echo "\r\n-- ".$MRF_ID." StartTime::".date("Y-m-d H:i:s")."--\r\n";

						$PURCHASE_PRODUCT 	= CompanyProductMaster::select("id as product_id",
												\DB::raw("CASE WHEN 1=1 THEN (
													SELECT SUM(inward_ledger.quantity)
													FROM inward_ledger
													WHERE inward_ledger.mrf_id = ".$MRF_ID."
													AND inward_ledger.product_type = ".PRODUCT_PURCHASE."
													AND inward_ledger.product_id = company_product_master.id
													AND inward_ledger.inward_date = '".$STOCK_DATE."'
												) END AS TOTAL_PURCHASE_INWARD"),
												\DB::raw("CASE WHEN 1=1 THEN (
													SELECT SUM(outward_ledger.quantity)
													FROM outward_ledger
													WHERE outward_ledger.mrf_id = ".$MRF_ID."
													AND outward_ledger.product_id = company_product_master.id
													AND outward_ledger.sales_product_id = 0
													AND outward_ledger.outward_date = '".$STOCK_DATE."'
												) END AS TOTAL_PURCHASE_OUTWARD"),

												\DB::raw("CASE WHEN 1=1 THEN (
													SELECT SUM(stock_ladger.opening_stock)
													FROM stock_ladger
													WHERE stock_ladger.mrf_id = ".$MRF_ID."
													AND stock_ladger.product_type = ".PRODUCT_PURCHASE."
													AND stock_ladger.product_id = company_product_master.id
													AND stock_ladger.stock_date = '".$STOCK_DATE."'
												) END AS TOTAL_PURCHASE_OPENING_STOCK"))
												->where("para_status_id",6001)
												->where("company_id",$COMPANY_ID)
												->get()
												->toArray();
						if(!empty($PURCHASE_PRODUCT))
						{
							foreach($PURCHASE_PRODUCT AS $PRO)
							{
								$PRODUCT_ID 						= 	$PRO['product_id'];
								$TOTAL_PURCHASE_INWARD 				=  (!empty($PRO['TOTAL_PURCHASE_INWARD'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_INWARD']) : 0;
								$TOTAL_PURCHASE_OUTWARD 			=  (!empty($PRO['TOTAL_PURCHASE_OUTWARD'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_OUTWARD']) : 0;
								$TOTAL_PURCHASE_OPENING_STOCK 		=  (!empty($PRO['TOTAL_PURCHASE_OPENING_STOCK'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_OPENING_STOCK']) : 0;
								$TOTAL_STOCK_WITH_PURCHASE_INWARD 	=  $TOTAL_PURCHASE_OPENING_STOCK + $TOTAL_PURCHASE_INWARD;
								$CLOSING_PURCHASE_STOCK 			=  $TOTAL_STOCK_WITH_PURCHASE_INWARD - $TOTAL_PURCHASE_OUTWARD;

								######## AVG PRICE CALCULATION FOR PRODUCT WISE 10 MARCH 2021 ############
								$PREV_DATE 			= date('Y-m-d', strtotime($STOCK_DATE .' -1 day'));
								$INWARD_AVG_PRICE 	= ProductInwardLadger::where("product_id",$PRODUCT_ID)
													->where("mrf_id",$MRF_ID)
													->where("product_type",PRODUCT_PURCHASE)
													->where("inward_date",$STOCK_DATE)
													->avg("avg_price");

								$STOCK_AVG_PRICE  	= self::where("mrf_id",$MRF_ID)
													->where("product_id",$PRODUCT_ID)
													->where("product_type",PRODUCT_PURCHASE)
													->where("stock_date",$PREV_DATE)
													->value("avg_price");
								$STOCK_AVG_PRICE 	= (!empty($STOCK_AVG_PRICE)) ? _FormatNumberV2($STOCK_AVG_PRICE) : 0;
								$INWARD_AVG_PRICE 	= (!empty($INWARD_AVG_PRICE)) ? _FormatNumberV2($INWARD_AVG_PRICE) : 0;
								$AVG_PRICE_AMT 		= _FormatNumberV2(($STOCK_AVG_PRICE + $INWARD_AVG_PRICE) / 2);

								######## AVG PRICE CALCULATION FOR PRODUCT WISE ############


								$AVG_PRICE_STOCK  	= self::where("mrf_id",$MRF_ID)
													->where("product_id",$PRODUCT_ID)
													->where("product_type",PRODUCT_PURCHASE)
													->where("stock_date",$STOCK_DATE)
													->value("avg_price");
								$PRIVIOUS_DATE = array(
									"product_id"	=> $PRODUCT_ID,
									"mrf_id"		=> $MRF_ID,
									"product_type"	=> PRODUCT_PURCHASE,
									"type"			=> "P",
									"opening_stock"	=> (!empty($TOTAL_PURCHASE_OPENING_STOCK)) ? _FormatNumberV2($TOTAL_PURCHASE_OPENING_STOCK) : 0,
									"inward"		=> _FormatNumberV2($TOTAL_PURCHASE_INWARD),
									"outward"		=> _FormatNumberV2($TOTAL_PURCHASE_OUTWARD),
									"closing_stock"	=> _FormatNumberV2($CLOSING_PURCHASE_STOCK),
									"company_id"	=> $COMPANY_ID,
									"stock_date"	=> $STOCK_DATE,
									"created_at"	=> date("Y-m-d H:i:s"),
								);
								$CURRENT_DATE = array(
									"product_id"	=> $PRODUCT_ID,
									"mrf_id"		=> $MRF_ID,
									"product_type"	=> PRODUCT_PURCHASE,
									"type"			=> "P",
									"opening_stock"	=> _FormatNumberV2($CLOSING_PURCHASE_STOCK),
									"inward"		=> 0,
									"outward"		=> 0,
									"closing_stock"	=> 0,
									"avg_price"		=> $AVG_PRICE_STOCK,
									"company_id"	=> $COMPANY_ID,
									"stock_date"	=> date("Y-m-d"),
									"created_at"	=> date("Y-m-d H:i:s"),
									// "avg_price"		=> $AVG_PRICE_AMT,
								);
								$PRIVIOUS_DATE_DATA = 	self::updateOrCreate(['product_id' 	=> $PRODUCT_ID,
															"mrf_id" 			=> $MRF_ID,
															"product_type" 		=> PRODUCT_PURCHASE,
															// "type"				=> "P",
															"stock_date" 		=> $STOCK_DATE
														], $PRIVIOUS_DATE);
								$CURRENT_DATE_DATA 	= 	self::updateOrCreate(['product_id' 	=> $PRODUCT_ID,
															"mrf_id" 			=> $MRF_ID,
															"product_type" 		=> PRODUCT_PURCHASE,
															// "type"				=> "P",
															"stock_date" 		=> date("Y-m-d")
														], $CURRENT_DATE);
							}
						}

						echo "\r\n-- ".$MRF_ID." EndTime::".date("Y-m-d H:i:s")."--\r\n";
					}
				}
			}
		}
	}

	/*
	Use 	: Update Sales Stock of every product on daily basis
	Author 	: Axay Shah
	Date 	: 26 Aug,2019
	*/

	public static function UpdateSalesStock($date = "")
	{
		$INWARD 				= 	new ProductInwardLadger();
		$OUTWARD 				= 	new OutwardLadger();
		$DEPARTMENT_TBL 		= 	new WmDepartment();
		$SALES_PROD_MASTER 		= 	new WmProductMaster();
		$PURCHASE_PRO 			= 	$SALES_PROD_MASTER->getTable();
		$SALES_TBL 				= 	new WmProductMaster();
		$SALES_PRO 				= 	$SALES_TBL->getTable();
		$DEPT 					=   $DEPARTMENT_TBL->getTable();
		$STOCK_DATE  			= 	(!empty($date)) ? date("Y-m-d",strtotime($date)) : date("Y-m-d",strtotime("-1 days"));
		$TotalInword 			=  	0;
		$TotalOutword 			=  	0;
		$OpeningStock 			= 	0;
		$ClosingStock 			= 	0;
		$TotalStockWithInword 	= 	0;
		$TodayInward 			= 	0;
		$TodayOutward 			= 	0;
		$arrCompany    			= 	CompanyMaster::select('company_id')
									->where('status','Active')
									->get();

		if (!empty($arrCompany))
		{
			foreach($arrCompany as $Company)
			{
				$COMPANY_ID 		= $Company->company_id;
				$Department = WmDepartment::select('id','location_id','base_location_id')
							->where('company_id',$COMPANY_ID)
							->where('is_virtual',0)
							->where('status','1')
							->get()
							->toArray();
				if (!empty($Department))
				{
					foreach($Department as $RAW)
					{
						$MRF_ID 			= $RAW['id'];

						echo "\r\n-- ".$MRF_ID." StartTime::".date("Y-m-d H:i:s")."--\r\n";

						$PURCHASE_PRODUCT 	= WmProductMaster::select("id as product_id",
												"inert_flag",
												\DB::raw("CASE WHEN 1=1 THEN (
													SELECT SUM(inward_ledger.quantity)
													FROM inward_ledger
													WHERE inward_ledger.mrf_id = ".$MRF_ID."
													AND inward_ledger.product_type = ".PRODUCT_SALES."
													AND inward_ledger.product_id = wm_product_master.id
													AND inward_ledger.inward_date = '".$STOCK_DATE."'
												) END AS TOTAL_PURCHASE_INWARD"),
												\DB::raw("CASE WHEN 1=1 THEN (
													SELECT SUM(outward_ledger.quantity)
													FROM outward_ledger
													WHERE outward_ledger.mrf_id = ".$MRF_ID."
													AND outward_ledger.sales_product_id = wm_product_master.id
													AND outward_ledger.outward_date = '".$STOCK_DATE."'
												) END AS TOTAL_PURCHASE_OUTWARD"),
												\DB::raw("CASE WHEN 1=1 THEN (
													SELECT SUM(stock_ladger.opening_stock)
													FROM stock_ladger
													WHERE stock_ladger.mrf_id = ".$MRF_ID."
													AND stock_ladger.product_type = ".PRODUCT_SALES."
													AND stock_ladger.product_id = wm_product_master.id
													AND stock_ladger.stock_date = '".$STOCK_DATE."'
												) END AS TOTAL_PURCHASE_OPENING_STOCK"))
												->where("status",1)
												->where("company_id",$COMPANY_ID)
												->get()
												->toArray();
												// LiveServices::toSqlWithBinding($PURCHASE_PRODUCT);
						if(!empty($PURCHASE_PRODUCT))
						{
							foreach($PURCHASE_PRODUCT AS $PRO)
							{
								$PRODUCT_ID 						= 	$PRO['product_id'];
								$TOTAL_PURCHASE_INWARD 				=  (!empty($PRO['TOTAL_PURCHASE_INWARD'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_INWARD']) : 0;
								$TOTAL_PURCHASE_OUTWARD 			=  (!empty($PRO['TOTAL_PURCHASE_OUTWARD'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_OUTWARD']) : 0;
								$TOTAL_PURCHASE_OPENING_STOCK 		=  (!empty($PRO['TOTAL_PURCHASE_OPENING_STOCK'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_OPENING_STOCK']) : 0;
								$TOTAL_STOCK_WITH_PURCHASE_INWARD 	=  $TOTAL_PURCHASE_OPENING_STOCK + $TOTAL_PURCHASE_INWARD;
								$CLOSING_PURCHASE_STOCK 			=  $TOTAL_STOCK_WITH_PURCHASE_INWARD - $TOTAL_PURCHASE_OUTWARD;

								######## AVG PRICE CALCULATION FOR PRODUCT WISE 10 MARCH 2021 ############
								$PREV_DATE 			= date('Y-m-d', strtotime($STOCK_DATE .' -1 day'));
								$INWARD_AVG_PRICE 	= ProductInwardLadger::where("product_id",$PRODUCT_ID)
													->where("mrf_id",$MRF_ID)
													->where("product_type",PRODUCT_SALES)
													->where("inward_date",$STOCK_DATE)
													->avg("avg_price");

								$STOCK_AVG_PRICE  	= self::where("mrf_id",$MRF_ID)
													->where("product_id",$PRODUCT_ID)
													->where("product_type",PRODUCT_SALES)
													->where("stock_date",$PREV_DATE)
													->value("avg_price");
								$STOCK_AVG_PRICE 	= (!empty($STOCK_AVG_PRICE)) ? _FormatNumberV2($STOCK_AVG_PRICE) : 0;
								$INWARD_AVG_PRICE 	= (!empty($INWARD_AVG_PRICE)) ? _FormatNumberV2($INWARD_AVG_PRICE) : 0;
								$AVG_PRICE_AMT 		= _FormatNumberV2(($STOCK_AVG_PRICE + $INWARD_AVG_PRICE) / 2);

								######## AVG PRICE CALCULATION FOR PRODUCT WISE ############
								$SALES_AVG_PRICE  	= self::where("mrf_id",$MRF_ID)
													->where("product_id",$PRODUCT_ID)
													->where("product_type",PRODUCT_SALES)
													->where("stock_date",$STOCK_DATE)
													->value("avg_price");
								$PRIVIOUS_DATE = array(
									"product_id"	=> $PRODUCT_ID,
									"mrf_id"		=> $MRF_ID,
									"product_type"	=> PRODUCT_SALES,
									"type"			=> "S",
									"opening_stock"	=> (!empty($TOTAL_PURCHASE_OPENING_STOCK)) ? _FormatNumberV2($TOTAL_PURCHASE_OPENING_STOCK) : 0,
									"inward"		=> _FormatNumberV2($TOTAL_PURCHASE_INWARD),
									"outward"		=> _FormatNumberV2($TOTAL_PURCHASE_OUTWARD),
									"closing_stock"	=> _FormatNumberV2($CLOSING_PURCHASE_STOCK),
									"company_id"	=> $COMPANY_ID,
									"stock_date"	=> $STOCK_DATE,
									"created_at"	=> date("Y-m-d H:i:s"),
								);
								###### INERT FLAG PRODUCT STOCK BECOME ZERO ON EVERY FIRST DATE OF MONTH #######
								$INERT_FLAG 			= $PRO['inert_flag'];
								$MONTH_FIRST_DATE 		= date("Y-m")."-01";
								$MONTH_FIRST_DATE_FLAG 	= (strtotime(date("Y-m-d")) == strtotime($MONTH_FIRST_DATE)) ? 1 : 0;
								$CLOSING_PURCHASE_STOCK = ($INERT_FLAG == 1 && $MONTH_FIRST_DATE_FLAG == 1) ? 0 : _FormatNumberV2($CLOSING_PURCHASE_STOCK);
								###### INERT FLAG PRODUCT STOCK BECOME ZERO ON EVERY FIRST DATE OF MONTH #######
								if(in_array($PRODUCT_ID,SALES_PRODUCT_INERT_CONTAMINATED)) {
									$SALES_AVG_PRICE = 0;
								}
								$CURRENT_DATE = array(
									"product_id"	=> $PRODUCT_ID,
									"mrf_id"		=> $MRF_ID,
									"product_type"	=> PRODUCT_SALES,
									"type"			=> "S",
									"opening_stock"	=> _FormatNumberV2($CLOSING_PURCHASE_STOCK),
									"inward"		=> 0,
									"outward"		=> 0,
									"closing_stock"	=> 0,
									"avg_price"		=> $SALES_AVG_PRICE,
									"company_id"	=> $COMPANY_ID,
									"stock_date"	=> date("Y-m-d"),
									"created_at"	=> date("Y-m-d H:i:s"),
									// "avg_price"		=> $AVG_PRICE_AMT,
								);
								$PRIVIOUS_DATE_DATA = 	self::updateOrCreate(['product_id' 	=> $PRODUCT_ID,
															"mrf_id" 			=> $MRF_ID,
															"product_type" 		=> PRODUCT_SALES,
															// "type"				=> "S",
															"stock_date" 		=> $STOCK_DATE
														], $PRIVIOUS_DATE);
								$CURRENT_DATE_DATA 	= 	self::updateOrCreate(['product_id' 	=> $PRODUCT_ID,
															"mrf_id" 			=> $MRF_ID,
															"product_type" 		=> PRODUCT_SALES,
															// "type"				=> "S",
															"stock_date" 		=> date("Y-m-d")
														], $CURRENT_DATE);
							}
						}

						echo "\r\n-- ".$MRF_ID." EndTime::".date("Y-m-d H:i:s")."--\r\n";
					}
				}
			}
		}
	}


	public static function UpdateSalesStockForDate($date = "")
	{
		// return false;
		$INWARD 				= 	new ProductInwardLadger();
		$OUTWARD 				= 	new OutwardLadger();
		$DEPARTMENT_TBL 		= 	new WmDepartment();
		$SALES_PROD_MASTER 		= 	new WmProductMaster();
		$PURCHASE_PRO 			= 	$SALES_PROD_MASTER->getTable();
		$SALES_TBL 				= 	new WmProductMaster();
		$SALES_PRO 				= 	$SALES_TBL->getTable();
		$DEPT 					=   $DEPARTMENT_TBL->getTable();
		$STOCK_DATE  			= 	date("Y-m-d",strtotime($date));
		$TotalInword 			=  	0;
		$TotalOutword 			=  	0;
		$OpeningStock 			= 	0;
		$ClosingStock 			= 	0;
		$TotalStockWithInword 	= 	0;
		$TodayInward 			= 	0;
		$TodayOutward 			= 	0;
		$arrCompany    			= 	CompanyMaster::select('company_id')->where('status','Active')->where("company_id",1)->get();
		$NEXT_DATE 				= date('Y-m-d', strtotime('+1 day', strtotime($STOCK_DATE)));

		if (!empty($arrCompany))
		{
			foreach($arrCompany as $Company)
			{
				$COMPANY_ID 		= $Company->company_id;
				$Department = WmDepartment::select('id','location_id','base_location_id')
							->where('company_id',$COMPANY_ID)
							->where('is_virtual',0)
							->where('status','1')
							->where('id','22')
							->get()
							->toArray();

				if (!empty($Department))
				{
					foreach($Department as $RAW)
					{
						$MRF_ID 			= $RAW['id'];

						echo "\r\n-- ".$MRF_ID." StartTime::".date("Y-m-d H:i:s")."--\r\n";

						$PURCHASE_PRODUCT 	= WmProductMaster::select("id as product_id",
												\DB::raw("CASE WHEN 1=1 THEN (
													SELECT SUM(inward_ledger.quantity)
													FROM inward_ledger
													WHERE inward_ledger.mrf_id = ".$MRF_ID."
													AND inward_ledger.product_type = ".PRODUCT_SALES."
													AND inward_ledger.product_id = wm_product_master.id
													AND inward_ledger.inward_date = '".$STOCK_DATE."'
												) END AS TOTAL_PURCHASE_INWARD"),
												\DB::raw("CASE WHEN 1=1 THEN (
													SELECT SUM(outward_ledger.quantity)
													FROM outward_ledger
													WHERE outward_ledger.mrf_id = ".$MRF_ID."
													AND outward_ledger.sales_product_id = wm_product_master.id
													AND outward_ledger.outward_date = '".$STOCK_DATE."'
												) END AS TOTAL_PURCHASE_OUTWARD"),
												\DB::raw("CASE WHEN 1=1 THEN (
													SELECT SUM(stock_ladger.opening_stock)
													FROM stock_ladger
													WHERE stock_ladger.mrf_id = ".$MRF_ID."
													AND stock_ladger.product_type = ".PRODUCT_SALES."
													AND stock_ladger.product_id = wm_product_master.id
													AND stock_ladger.stock_date = '".$STOCK_DATE."'
												) END AS TOTAL_PURCHASE_OPENING_STOCK"))
												->where("status",1)
												->whereIn("id",array(38,47,48,18,100,130,332,7,57,76,314,11,352,201,52,66,112,370,314,68))
												->where("company_id",$COMPANY_ID);
						// LiveServices::toSqlWithBinding($PURCHASE_PRODUCT);die;
						$SELECTROWS = $PURCHASE_PRODUCT->get()->toArray();
						if(!empty($SELECTROWS))
						{
							foreach($SELECTROWS AS $PRO)
							{
								// PRD($PRO);
								$PRODUCT_ID 						= 	$PRO['product_id'];
								$TOTAL_PURCHASE_INWARD 				=  (!empty($PRO['TOTAL_PURCHASE_INWARD'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_INWARD']) : 0;
								$TOTAL_PURCHASE_OUTWARD 			=  (!empty($PRO['TOTAL_PURCHASE_OUTWARD'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_OUTWARD']) : 0;
								$TOTAL_PURCHASE_OPENING_STOCK 		=  (!empty($PRO['TOTAL_PURCHASE_OPENING_STOCK'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_OPENING_STOCK']) : 0;
								$TOTAL_STOCK_WITH_PURCHASE_INWARD 	=  $TOTAL_PURCHASE_OPENING_STOCK + $TOTAL_PURCHASE_INWARD;
								$CLOSING_PURCHASE_STOCK 			=  $TOTAL_STOCK_WITH_PURCHASE_INWARD - $TOTAL_PURCHASE_OUTWARD;
								$PRIVIOUS_DATE = array(
									"product_id"	=> $PRODUCT_ID,
									"mrf_id"		=> $MRF_ID,
									"product_type"	=> PRODUCT_SALES,
									"type"			=> "S",
									"opening_stock"	=> (!empty($TOTAL_PURCHASE_OPENING_STOCK)) ? _FormatNumberV2($TOTAL_PURCHASE_OPENING_STOCK) : 0,
									"inward"		=> _FormatNumberV2($TOTAL_PURCHASE_INWARD),
									"outward"		=> _FormatNumberV2($TOTAL_PURCHASE_OUTWARD),
									"closing_stock"	=> _FormatNumberV2($CLOSING_PURCHASE_STOCK),
									"company_id"	=> $COMPANY_ID,
									"stock_date"	=> $STOCK_DATE,
									"created_at"	=> date("Y-m-d H:i:s"),
								);
								$PRIVIOUS_DATE_DATA = 	self::updateOrCreate(['product_id' 	=> $PRODUCT_ID,
															"mrf_id" 			=> $MRF_ID,
															"product_type" 		=> PRODUCT_SALES,
															"stock_date" 		=> $STOCK_DATE
														], $PRIVIOUS_DATE);

								$NEXT_DATE_DATA = 	self::updateOrCreate(['product_id' 	=> $PRODUCT_ID,
															"mrf_id" 			=> $MRF_ID,
															"product_type" 		=> PRODUCT_SALES,
															"stock_date" 		=> $NEXT_DATE
														], array("opening_stock"=> $CLOSING_PURCHASE_STOCK));
							}
						}

						echo "\r\n-- ".$MRF_ID." EndTime::".date("Y-m-d H:i:s")."--\r\n";
					}
				}
			}
		}
	}


	/*
	Use 	: List Stock Ladger
	Author 	: Axay Shah
	Date 	: 31 Aug,2019
	*/
	public static function ListStockOld($request)
	{

		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "product_id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size') : DEFAULT_SIZE;
		IF($recordPerPage == 500){
			$recordPerPage = 50000;
		}
		$pageNumber     = !empty($request->input('pageNumber')) ? $request->input('pageNumber') : '';
		$self 			= (new static)->getTable();
		$Department 	= new WmDepartment();
		$Product 		= new CompanyProductMaster();
		$where 			= "";
		if($request->has('params.product_id') && !empty($request->input('params.product_id')))
		{
			$where .= " AND stock_ladger.product_id = ".$request->input('params.product_id');
		}
		// $mrf_id = "3,11,22,23,26,27,48,59";
		// $where .= " AND stock_ladger.mrf_id IN (".$mrf_id.")";
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$mrf_id = $request->input('params.mrf_id');
			if(is_array($mrf_id)){
				$mrf_id = implode(",", $mrf_id);
			}
			$where .= " AND stock_ladger.mrf_id IN (".$mrf_id.")";
		}

		if($request->has('params.base_location_id') && !empty($request->input('params.base_location_id')))
		{
			$base_location_mrf 	= "";
			$base_location_id 	= $request->input('params.base_location_id');
			$mrf_data 			= WmDepartment::select(\DB::raw("GROUP_CONCAT(id) as mrf_ids"))->where("is_virtual","0")->whereIn("base_location_id",$base_location_id)->get()->toArray();

			if(!empty($mrf_data)){
				$base_location_mrf = $mrf_data[0]['mrf_ids'];
			}
			if(!empty($base_location_mrf)){
				$where .= " AND stock_ladger.mrf_id IN (".$base_location_mrf.")";
			}
		}

		if($request->has('params.product_type') && !empty($request->input('params.product_type')))
		{
			$where .= " AND stock_ladger.product_type = ".$request->input('params.product_type');
		}
		if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to')))
		{
			$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_from')));
			$ENDDATE   = date("Y-m-d", strtotime($request->input('params.created_to')));
			$where .= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$ENDDATE."')";
		} else if(!empty($request->input('params.created_from'))) {
			$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_from')));
			$where .= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$Today."')";

		} else if(!empty($request->input('params.created_to'))) {
			$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_to')));
			$where .= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$Today."')";
		}
		if($request->has('params.exclude_zero_stock') && !empty($request->input('params.exclude_zero_stock')))
		{
			$exclude_zero_stock = $request->input("params.exclude_zero_stock");
			if($exclude_zero_stock == "1") {
				// $where .= " having closing_stock > 0";
			}
		}
		$query 			= "	SELECT * FROM
							(
									SELECT
									stock_ladger.id,
									stock_ladger.product_id,
									stock_ladger.product_type,
									stock_ladger.opening_stock,
									stock_ladger.inward,
									stock_ladger.outward,
									IF(stock_ladger.stock_date = CURDATE(),GetProductInventoryStock(stock_ladger.mrf_id,stock_ladger.stock_date,stock_ladger.product_id,stock_ladger.product_type,'CLOSING',stock_ladger.opening_stock),stock_ladger.closing_stock) as closing_stock,
									stock_ladger.company_id,
									stock_ladger.mrf_id,
									stock_ladger.stock_date,
									stock_ladger.stock_date as created_at,
									stock_ladger.avg_price,
									stock_ladger.new_cogs_price,
									company_product_master.net_suit_code,
									CONCAT(company_product_master.name,' ',company_product_quality_parameter.parameter_name) AS name,
									'Purchase product' AS product_type_name,
									D.department_name
									FROM stock_ladger
									JOIN company_product_master ON stock_ladger.product_id = company_product_master.id
									JOIN company_product_quality_parameter ON company_product_master.id = company_product_quality_parameter.product_id
									JOIN wm_department as D on stock_ladger.mrf_id = D.id
									WHERE stock_ladger.product_type = ".PRODUCT_PURCHASE." AND D.is_virtual = 0
									AND stock_ladger.company_id = ".Auth()->user()->company_id."
									$where
								UNION
									SELECT
									stock_ladger.id,
									stock_ladger.product_id,
									stock_ladger.product_type,
									stock_ladger.opening_stock,
									stock_ladger.inward,
									stock_ladger.outward,
									IF(stock_ladger.stock_date = CURDATE(),GetProductInventoryStock(stock_ladger.mrf_id,stock_ladger.stock_date,stock_ladger.product_id,stock_ladger.product_type,'CLOSING',stock_ladger.opening_stock),stock_ladger.closing_stock) as closing_stock,
									stock_ladger.company_id,
									stock_ladger.mrf_id,
									stock_ladger.stock_date,
									stock_ladger.stock_date as created_at,
									stock_ladger.avg_price,
									stock_ladger.new_cogs_price,
									wm_product_master.net_suit_code,
									wm_product_master.title as name,
									'Sales product' AS product_type_name,
									D.department_name
									FROM stock_ladger
									JOIN wm_product_master on stock_ladger.product_id = wm_product_master.id
									JOIN wm_department as D on stock_ladger.mrf_id = D.id
									WHERE stock_ladger.product_type = ".PRODUCT_SALES." AND D.is_virtual = 0
									AND stock_ladger.company_id = ".Auth()->user()->company_id."
									$where
							) as q ";
		$result 	= $query." ORDER BY ".$sortBy." $sortOrder";

		$GetCount  	= \DB::select($result);
		if($request->has('ex') && !empty($request->input('ex')) && $request->input('ex') == EXPORT_ALL)
		{
			$recordPerPage = count($GetCount);
		}
		if(empty($pageNumber)) {
			$pageNumber = 1;
		}
		$start_from 				= ($pageNumber-1) * $recordPerPage;
		$RawQuery 					= $result." LIMIT $start_from, $recordPerPage";
		$raw  						= \DB::select($RawQuery);
		$closingStockQty 			= 0;
		if(!empty($raw)){
			foreach($raw as $key => $value){
				if(strtotime($value->stock_date) == strtotime($Today) && $value->product_type == PRODUCT_PURCHASE){
					$PurchaseOutWard 	= 	OutWardLadger::where("outward_date",$Today)
											->where("product_id",$value->product_id)
											->where("sales_product_id",0)
											->where("mrf_id",$value->mrf_id)
											->sum("quantity");
					$PurchaseInWard	 	= 	ProductInwardLadger::where("inward_date",$Today)
											->where("product_id",$value->product_id)
											->where("product_type",PRODUCT_PURCHASE)
											->where("mrf_id",$value->mrf_id)
											->sum("quantity");
					$raw[$key]->outward = (!empty($PurchaseOutWard)) ? _FormatNumberV2($PurchaseOutWard) : "0.00";
					$raw[$key]->inward 	= (!empty($PurchaseInWard)) ? _FormatNumberV2($PurchaseInWard) : "0.00";
					$outwardQty 		= $raw[$key]->outward;
				}elseif(strtotime($value->stock_date) == strtotime($Today) && $value->product_type == PRODUCT_SALES){
					$SalesOutWard 	= 	OutWardLadger::where("outward_date",$Today)
										->where("sales_product_id",$value->product_id)
										->where("mrf_id",$value->mrf_id)
										->sum("quantity");
					$SalesInWard 	= 	ProductInwardLadger::where("inward_date",$Today)
										->where("product_id",$value->product_id)
										->where("product_type",PRODUCT_SALES)
										->where("mrf_id",$value->mrf_id)
										->sum("quantity");
					$raw[$key]->outward = (!empty($SalesOutWard)) ? _FormatNumberV2($SalesOutWard) : "0.00";
					$raw[$key]->inward 	= (!empty($SalesInWard)) ? _FormatNumberV2($SalesInWard) : "0.00";
					$outwardQty 		= $raw[$key]->outward;
				} else {
					$outwardQty 		= $raw[$key]->outward;
				}
				if (strtotime($value->stock_date) == strtotime($Today)) {
					$closingStockQty 			= _FormatNumberV2(($raw[$key]->opening_stock + $raw[$key]->inward) - $raw[$key]->outward);
					$raw[$key]->closing_stock 	= (empty($closingStockQty)?"0.00":$closingStockQty);
				}
				$raw[$key]->outward 		= $outwardQty;
				$raw[$key]->stock_value 	= (!empty($value->closing_stock)) ? _FormatNumberV2($value->closing_stock * $value->avg_price) : 0;
			}
		}
		$output['result'] 			= $raw;
		$output['pageNumber'] 		= $pageNumber;
		$output['totalElements'] 	= count($GetCount);
		$output['size'] 			= $recordPerPage;
		$output['query'] 			= $result;
		$output['totalPages'] 		= ceil(count($GetCount)/$recordPerPage);
		return $output;
	}

	/*
	Use 	: List Stock Ladger
	Author 	: Axay Shah
	Date 	: 31 Aug,2019
	*/
	public static function ListStock($request)
	{
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "product_id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size') : DEFAULT_SIZE;
		IF($recordPerPage == 500) {
			$recordPerPage = 50000;
		}
		$pageNumber     = !empty($request->input('pageNumber')) ? $request->input('pageNumber') : '';
		$self 			= (new static)->getTable();
		$Department 	= new WmDepartment();
		$Product 		= new CompanyProductMaster();
		$WHERE 			= "";
		if($request->has('params.product_id') && !empty($request->input('params.product_id')))
		{
			$WHERE .= " AND stock_ladger.product_id = ".$request->input('params.product_id');
		}
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$mrf_id = $request->input('params.mrf_id');
			if(is_array($mrf_id)) {
				$mrf_id = implode(",", $mrf_id);
			}
			$WHERE .= " AND stock_ladger.mrf_id IN (".$mrf_id.")";
		}

		if($request->has('params.base_location_id') && !empty($request->input('params.base_location_id')))
		{
			$base_location_mrf 	= "";
			$base_location_id 	= $request->input('params.base_location_id');
			$mrf_data 			= WmDepartment::select(\DB::raw("GROUP_CONCAT(id) as mrf_ids"))->where("is_virtual","0")->whereIn("base_location_id",$base_location_id)->get()->toArray();

			if(!empty($mrf_data)) {
				$base_location_mrf = $mrf_data[0]['mrf_ids'];
			}
			if(!empty($base_location_mrf)) {
				$WHERE .= " AND stock_ladger.mrf_id IN (".$base_location_mrf.")";
			}
		}

		if($request->has('params.product_type') && !empty($request->input('params.product_type'))) {
			$WHERE .= " AND stock_ladger.product_type = ".$request->input('params.product_type');
		}
		if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to'))) {
			$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_from')));
			$ENDDATE   = date("Y-m-d", strtotime($request->input('params.created_to')));
			$WHERE .= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$ENDDATE."')";
		} else if(!empty($request->input('params.created_from'))) {
			$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_from')));
			$WHERE .= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$Today."')";

		} else if(!empty($request->input('params.created_to'))) {
			$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_to')));
			$WHERE .= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$Today."')";
		}
		$HAVING_COND = "";
		if($request->has('params.exclude_zero_stock') && !empty($request->input('params.exclude_zero_stock'))) {
			$exclude_zero_stock = $request->input("params.exclude_zero_stock");
			if($exclude_zero_stock == "1") {
				$HAVING_COND .= " having closing_stock > 0 ";
			}
		}
		if($request->has('params.product_tag') && !empty($request->input('params.product_tag'))) {
			$PRODUCT_FLAG = "'".implode("','",$request->input('params.product_tag'))."'";
			if (empty($HAVING_COND)) {
				$HAVING_COND .= " HAVING PRODUCT_FLAG IN (".$PRODUCT_FLAG.")";
			} else {
				$HAVING_COND .= " AND PRODUCT_FLAG IN (".$PRODUCT_FLAG.")";
			}
		}
		$query 		= "	SELECT
						stock_ladger.id,
						stock_ladger.product_id,
						stock_ladger.product_type,
						IF(stock_ladger.product_type = 1,'PURCHASE','SALES') as product_type_name,
						stock_ladger.opening_stock,
						IF(stock_ladger.stock_date = CURDATE(),GetProductInventoryStock(stock_ladger.mrf_id,stock_ladger.stock_date,stock_ladger.product_id,stock_ladger.product_type,'INWARD',stock_ladger.opening_stock),stock_ladger.inward) as inward,
						IF(stock_ladger.stock_date = CURDATE(),GetProductInventoryStock(stock_ladger.mrf_id,stock_ladger.stock_date,stock_ladger.product_id,stock_ladger.product_type,'OUTWARD',stock_ladger.opening_stock),stock_ladger.outward) as outward,
						IF(stock_ladger.stock_date = CURDATE(),GetProductInventoryStock(stock_ladger.mrf_id,stock_ladger.stock_date,stock_ladger.product_id,stock_ladger.product_type,'CLOSING',stock_ladger.opening_stock),stock_ladger.closing_stock) as closing_stock,
						stock_ladger.company_id,
						stock_ladger.mrf_id,
						stock_ladger.stock_date,
						stock_ladger.stock_date as created_at,
						stock_ladger.avg_price,
						stock_ladger.new_cogs_price,
						IF (stock_ladger.product_type = 1, CONCAT(company_product_master.name,'-',company_product_quality_parameter.parameter_name),wm_product_master.title) as name,
						IF (stock_ladger.product_type = 2,(IF (wm_product_master.is_afr,'AFR',IF(wm_product_master.is_rdf,'RDF',(IF(wm_product_master.is_inert,'INERT','RECYCLABLE'))))),IF(company_product_master.foc_product,'FOC','-')) AS PRODUCT_FLAG,
						IF (stock_ladger.product_type = 1,company_product_master.net_suit_code,wm_product_master.net_suit_code) AS net_suit_code,
						(IF(stock_ladger.stock_date = CURDATE(),GetProductInventoryStock(stock_ladger.mrf_id,stock_ladger.stock_date,stock_ladger.product_id,stock_ladger.product_type,'CLOSING',stock_ladger.opening_stock),stock_ladger.closing_stock) * stock_ladger.avg_price) as stock_value,
						D.department_name
						FROM stock_ladger
						INNER JOIN wm_department as D on stock_ladger.mrf_id = D.id
						LEFT JOIN wm_product_master on stock_ladger.product_id = wm_product_master.id AND stock_ladger.product_type = 2
						LEFT JOIN company_product_master on stock_ladger.product_id = company_product_master.id AND stock_ladger.product_type = 1
						LEFT JOIN company_product_quality_parameter on company_product_master.id = company_product_quality_parameter.product_id
						WHERE stock_ladger.company_id = ".Auth()->user()->company_id."
						$WHERE
						$HAVING_COND";
		$result 	= $query." ORDER BY ".$sortBy." $sortOrder";
		$GetCount  	= \DB::select($result);
		if($request->has('ex') && !empty($request->input('ex')) && $request->input('ex') == EXPORT_ALL) {
			$recordPerPage = count($GetCount);
		}
		if(empty($pageNumber)) {
			$pageNumber = 1;
		}
		$start_from 				= ($pageNumber-1) * $recordPerPage;
		$RawQuery 					= $result." LIMIT $start_from, $recordPerPage";
		$raw  						= \DB::select($RawQuery);
		$output['result'] 			= $raw;
		$output['pageNumber'] 		= $pageNumber;
		$output['totalElements'] 	= count($GetCount);
		$output['size'] 			= $recordPerPage;
		$output['totalPages'] 		= ceil(count($GetCount)/$recordPerPage);
		return $output;
	}

	public static function ListStockNew($request)
	{
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "product_id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size') : DEFAULT_SIZE;
		IF($recordPerPage == 500){
			$recordPerPage = 50000;
		}
		$pageNumber     = !empty($request->input('pageNumber')) ? $request->input('pageNumber') : '';
		$self 			= (new static)->getTable();
		$Department 	= new WmDepartment();
		$Product 		= new CompanyProductMaster();
		$where 			= "";
		if($request->has('params.product_id') && !empty($request->input('params.product_id')))
		{
			$where .= " AND stock_ladger.product_id = ".$request->input('params.product_id');
		}
		// $mrf_id = "3,11,22,23,26,27,48,59";
		// $where .= " AND stock_ladger.mrf_id IN (".$mrf_id.")";
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$mrf_id = $request->input('params.mrf_id');
			if(is_array($mrf_id)){
				$mrf_id = implode(",", $mrf_id);
			}
			$where .= " AND stock_ladger.mrf_id IN (".$mrf_id.")";
		}

		if($request->has('params.base_location_id') && !empty($request->input('params.base_location_id')))
		{
			$base_location_mrf 	= "";
			$base_location_id 	= $request->input('params.base_location_id');
			$mrf_data 			= WmDepartment::select(\DB::raw("GROUP_CONCAT(id) as mrf_ids"))->where("is_virtual","0")->whereIn("base_location_id",$base_location_id)->get()->toArray();

			if(!empty($mrf_data)){
				$base_location_mrf = $mrf_data[0]['mrf_ids'];
			}
			if(!empty($base_location_mrf)){
				$where .= " AND stock_ladger.mrf_id IN (".$base_location_mrf.")";
			}
		}

		if($request->has('params.product_type') && !empty($request->input('params.product_type')))
		{
			$where .= " AND stock_ladger.product_type = ".$request->input('params.product_type');
		}

		if($request->has('params.exclude_zero_stock') && !empty($request->input('params.exclude_zero_stock')))
		{
			$exclude_zero_stock = $request->input("params.exclude_zero_stock");
			if($exclude_zero_stock == "1") {
				$where .= " AND stock_ladger.closing_stock > 0";
			}
		}
		if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to')))
		{
			$STARTDATE 	= date("Y-m-d", strtotime($request->input('params.created_from')));
			$ENDDATE   	= date("Y-m-d", strtotime($request->input('params.created_to')));
			$where 		.= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$ENDDATE."')";
		} else if(!empty($request->input('params.created_from'))) {
			$STARTDATE 	= date("Y-m-d", strtotime($request->input('params.created_from')));
			$where 		.= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$Today."')";

		} else if(!empty($request->input('params.created_to'))) {
			$STARTDATE 	= date("Y-m-d", strtotime($request->input('params.created_to')));
			$where 		.= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$Today."')";
		}

		$totalElementQuery 			=  'CALL SP_STOCK_LEDGER_REPORT(" '.$where.'","")';
		$GetCount  					= \DB::select($totalElementQuery);
		if(($request->has('ex') && !empty($request->input('ex')) && $request->input('ex') == EXPORT_ALL) || ($recordPerPage > 500))
		{
			$recordPerPage = count($GetCount);
		}
		if(empty($pageNumber)) {
			$pageNumber = 1;
		}
		$start_from 				= ($pageNumber-1) * $recordPerPage;
		$LimitCon 					= " LIMIT $start_from, $recordPerPage";
		$result 					=  'CALL SP_STOCK_LEDGER_REPORT(" '.$where.'"," '.$LimitCon.'")';
		$closingStockQty 			= 0;
		$raw  						= \DB::select($result);
		$closingStockQty 			= 0;
		if(!empty($raw)){
			foreach($raw as $key => $value){
				$PurchaseOutWard = 0;
				if(strtotime($value->stock_date) == strtotime($Today) && $value->product_type == PRODUCT_PURCHASE){

					$PurchaseOutWard 	= 	OutWardLadger::where("outward_date",$Today)
											->where("product_id",$value->product_id)
											->where("sales_product_id",0)
											->where("mrf_id",$value->mrf_id)
											->sum("quantity");
					$PurchaseInWard	 	= 	ProductInwardLadger::where("inward_date",$Today)
											->where("product_id",$value->product_id)
											->where("product_type",PRODUCT_PURCHASE)
											->where("mrf_id",$value->mrf_id)
											->sum("quantity");
					$raw[$key]->outward = (!empty($PurchaseOutWard)) ? _FormatNumberV2($PurchaseOutWard) : "0.00";
					$raw[$key]->inward 	= (!empty($PurchaseInWard)) ? _FormatNumberV2($PurchaseInWard) : "0.00";
					$outwardQty 		= $raw[$key]->outward;
				}elseif(strtotime($value->stock_date) == strtotime($Today) && $value->product_type == PRODUCT_SALES){
					$SalesOutWard 	= 	OutWardLadger::where("outward_date",$Today)
										->where("sales_product_id",$value->product_id)
										->where("mrf_id",$value->mrf_id)
										->sum("quantity");
					$SalesInWard 	= 	ProductInwardLadger::where("inward_date",$Today)
										->where("product_id",$value->product_id)
										->where("product_type",PRODUCT_SALES)
										->where("mrf_id",$value->mrf_id)
										->sum("quantity");
					$raw[$key]->outward = (!empty($SalesOutWard)) ? _FormatNumberV2($SalesOutWard) : "0.00";
					$raw[$key]->inward 	= (!empty($SalesInWard)) ? _FormatNumberV2($SalesInWard) : "0.00";
					$outwardQty 		= $raw[$key]->outward;
				} else {
					$outwardQty 		= $raw[$key]->outward;
				}
				if (strtotime($value->stock_date) == strtotime($Today)) {
					$closingStockQty 			= _FormatNumberV2(($raw[$key]->opening_stock + $raw[$key]->inward) - $raw[$key]->outward);
					$raw[$key]->closing_stock 	= (empty($closingStockQty)?"0.00":$closingStockQty);
				}
				$raw[$key]->outward 		= $outwardQty;
				$raw[$key]->stock_value 	= (!empty($value->closing_stock)) ? _FormatNumberV2($value->closing_stock * $value->avg_price) : 0;
			}
		}
		$output['result'] 			= $raw;
		$output['pageNumber'] 		= $pageNumber;
		$output['totalElements'] 	= count($GetCount);
		$output['size'] 			= $recordPerPage;
		$output['query'] 			= $result;
		$output['totalPages'] 		= ceil(count($GetCount)/$recordPerPage);
		return $output;
	}
	/*
	Use 	: List Stock Ladger
	Author 	: Axay Shah
	Date 	: 31 Aug,2019
	*/
	public static function ListStockNewOne($request)
	{
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "product_id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size') : DEFAULT_SIZE;
		IF($recordPerPage == 500){
			$recordPerPage = 50000;
		}
		$pageNumber     = !empty($request->input('pageNumber')) ? $request->input('pageNumber') : '';
		$self 			= (new static)->getTable();
		$Department 	= new WmDepartment();
		$Product 		= new CompanyProductMaster();
		$where 			= " WHERE stock_ladger.company_id = 1";
		if($request->has('params.product_id') && !empty($request->input('params.product_id')))
		{
			$where .= " AND stock_ladger.product_id = ".$request->input('params.product_id');
		}
		// $mrf_id = "3,11,22,23,26,27,48,59";
		// $where .= " AND stock_ladger.mrf_id IN (".$mrf_id.")";
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$mrf_id = $request->input('params.mrf_id');
			if(is_array($mrf_id)){
				$mrf_id = implode(",", $mrf_id);
			}
			$where .= " AND stock_ladger.mrf_id IN (".$mrf_id.")";
		}
		if($request->has('params.base_location_id') && !empty($request->input('params.base_location_id')))
		{
			$base_location_mrf 	= "";
			$base_location_id 	= $request->input('params.base_location_id');
			$mrf_data 			= WmDepartment::select(\DB::raw("GROUP_CONCAT(id) as mrf_ids"))->where("is_virtual","0")->whereIn("base_location_id",$base_location_id)->get()->toArray();

			if(!empty($mrf_data)){
				$base_location_mrf = $mrf_data[0]['mrf_ids'];
			}
			if(!empty($base_location_mrf)){
				$where .= " AND stock_ladger.mrf_id IN (".$base_location_mrf.")";
			}
		}

		if($request->has('params.product_type') && !empty($request->input('params.product_type')))
		{
			$where .= " AND stock_ladger.product_type = ".$request->input('params.product_type');
		}


		if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to')))
		{
			$STARTDATE 	= date("Y-m-d", strtotime($request->input('params.created_from')));
			$ENDDATE   	= date("Y-m-d", strtotime($request->input('params.created_to')));
			$where 		.= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$ENDDATE."')";
		} else if(!empty($request->input('params.created_from'))) {
			$STARTDATE 	= date("Y-m-d", strtotime($request->input('params.created_from')));
			$where 		.= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$Today."')";

		} else if(!empty($request->input('params.created_to'))) {
			$STARTDATE 	= date("Y-m-d", strtotime($request->input('params.created_to')));
			$where 		.= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$Today."')";
		}
		if(Auth()->user()->adminuserid == 1){
			// echo $result;
			// exit;
		}
		if($request->has('params.exclude_zero_stock') && !empty($request->input('params.exclude_zero_stock')))
		{
			$exclude_zero_stock = $request->input("params.exclude_zero_stock");
			if($exclude_zero_stock == "1") {
				$LimitCon .= " having closing_stock > 0";
			}
		}
		$totalElementQuery 			=  'CALL SP_STOCK_LEDGER_REPORT("'.$where.'","","1")';
		$GetCount  					= \DB::select($totalElementQuery);
		if(($request->has('ex') && !empty($request->input('ex')) && $request->input('ex') == EXPORT_ALL) || ($recordPerPage > 500))
		{
			$recordPerPage = count($GetCount);
		}
		if(empty($pageNumber)) {
			$pageNumber = 1;
		}
		$start_from 				= ($pageNumber-1) * $recordPerPage;
		$LimitCon 					= " LIMIT $start_from, $recordPerPage";
		$result 					=  'CALL SP_STOCK_LEDGER_REPORT("'.$where.'","'.$LimitCon.'","0")';
		echo $result;
		exit;
		$closingStockQty 			= 0;
		$raw  						= \DB::select($result);
		$closingStockQty 			= 0;
		if(!empty($raw)){
			foreach($raw as $key => $value){
				$PurchaseOutWard 			= 0;
				$raw[$key]->stock_value 	= (!empty($value->closing_stock)) ? _FormatNumberV2($value->closing_stock * $value->avg_price) : 0;
			}
		}
		$output['result'] 			= $raw;
		$output['pageNumber'] 		= $pageNumber;
		$output['totalElements'] 	= count($GetCount);
		$output['size'] 			= $recordPerPage;
		$output['query'] 			= $result;
		$output['totalPages'] 		= ceil(count($GetCount)/$recordPerPage);
		return $output;
	}
	/*
	Use 	: List Stock Ladger
	Author 	: Axay Shah
	Date 	: 31 Aug,2019
	*/
	public static function ListStockV2($request)
	{

		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "product_id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size') : DEFAULT_SIZE;
		IF($recordPerPage == 500){
			$recordPerPage = 50000;
		}
		$pageNumber     = !empty($request->input('pageNumber')) ? $request->input('pageNumber') : '';
		$self 			= (new static)->getTable();
		$Department 	= new WmDepartment();
		$Product 		= new CompanyProductMaster();
		$where 			= "";
		if($request->has('params.product_id') && !empty($request->input('params.product_id')))
		{
			$where .= " AND stock_ladger.product_id = ".$request->input('params.product_id');
		}
		// $mrf_id = "3,11,22,23,26,27,48,59";
		// $where .= " AND stock_ladger.mrf_id IN (".$mrf_id.")";
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$mrf_id = $request->input('params.mrf_id');
			if(is_array($mrf_id)){
				$mrf_id = implode(",", $mrf_id);
			}
			$where .= " AND stock_ladger.mrf_id IN (".$mrf_id.")";
		}

		if($request->has('params.base_location_id') && !empty($request->input('params.base_location_id')))
		{
			$base_location_mrf 	= "";
			$base_location_id 	= $request->input('params.base_location_id');
			$mrf_data 			= WmDepartment::select(\DB::raw("GROUP_CONCAT(id) as mrf_ids"))->where("is_virtual","0")->whereIn("base_location_id",$base_location_id)->get()->toArray();

			if(!empty($mrf_data)){
				$base_location_mrf = $mrf_data[0]['mrf_ids'];
			}
			if(!empty($base_location_mrf)){
				$where .= " AND stock_ladger.mrf_id IN (".$base_location_mrf.")";
			}
		}
		if($request->has('params.product_type') && !empty($request->input('params.product_type')))
		{
			$where .= " AND stock_ladger.product_type = ".$request->input('params.product_type');
		}
		if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to')))
		{
			$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_from')));
			$ENDDATE   = date("Y-m-d", strtotime($request->input('params.created_to')));
			$where .= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$ENDDATE."')";
		} else if(!empty($request->input('params.created_from'))) {
			$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_from')));
			$where .= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$Today."')";

		} else if(!empty($request->input('params.created_to'))) {
			$STARTDATE = date("Y-m-d", strtotime($request->input('params.created_to')));
			$where .= " AND (stock_ladger.stock_date BETWEEN '".$STARTDATE."' AND '".$Today."')";
		}
		if(empty($pageNumber)) {
			$pageNumber = 1;
		}
		$start_from 				= ($pageNumber-1) * $recordPerPage;
		$LimitCon 					= " LIMIT $start_from, $recordPerPage";
		// $totalElement 				= \DB::select('CALL SP_STOCK_LEDGER_LISTING("'.$where.'")';
		$totalElementQuery 	=  'CALL SP_STOCK_LEDGER_LISTING("'.$where.'","")';

		$result 			=  'CALL SP_STOCK_LEDGER_LISTING("'.$where.'","'.$LimitCon.'")';

		$GetTotalElement 			= \DB::select($totalElementQuery);

		$totalElement 			= (isset($GetTotalElement[0]->cnt)) ? $GetTotalElement[0]->cnt : 0;


		$GetCount  					= \DB::select($result);

		$closingStockQty 			= 0;
		if(!empty($GetCount)){
			foreach($GetCount as $key => $value){
				$inward = 0;
				if(strtotime($value->stock_date) == strtotime($Today)){

					$inward 			= 	ProductInwardLadger::where("inward_date",$Today)
											->where("product_id",$value->product_id)
											->where("product_type",$value->product_type)
											->where("mrf_id",$value->mrf_id)
											->sum("quantity");
					if($value->product_type == PRODUCT_PURCHASE){
						$PurchaseOutWard 	= 	OutWardLadger::where("outward_date",$Today)
							->where("product_id",$value->product_id)
							->where("sales_product_id",0)
							->where("mrf_id",$value->mrf_id)
							->sum("quantity");
							$GetCount[$key]->outward 	= (!empty($PurchaseOutWard)) ? _FormatNumberV2($PurchaseOutWard) : "0.00";
					}else{
						$SalesOutWard 	= 	OutWardLadger::where("outward_date",$Today)
							->where("sales_product_id",$value->product_id)
							->where("mrf_id",$value->mrf_id)
							->sum("quantity");
						$GetCount[$key]->outward 	= (!empty($SalesOutWard)) ? _FormatNumberV2($SalesOutWard) : "0.00";
					}


					$outwardQty 		= $GetCount[$key]->outward;
				} else {
					$outwardQty 		= $GetCount[$key]->outward;
				}
				$GetCount[$key]->inward 	= (!empty($inward)) ? _FormatNumberV2($inward) : "0.00";
				if (strtotime($value->stock_date) == strtotime($Today)) {
					$closingStockQty 			= _FormatNumberV2(($GetCount[$key]->opening_stock + $GetCount[$key]->inward) - $GetCount[$key]->outward);
					$GetCount[$key]->closing_stock 	= (empty($closingStockQty)?"0.00":$closingStockQty);
				}
				$GetCount[$key]->outward 		= $outwardQty;
				$GetCount[$key]->stock_value 	= (!empty($value->closing_stock)) ? _FormatNumberV2($value->closing_stock * $value->avg_price) : 0;
			}
		}
		$output['result'] 			= $GetCount;
		$output['pageNumber'] 		= $pageNumber;
		$output['totalElements'] 	= $totalElement;
		$output['size'] 			= $recordPerPage;
		$output['query'] 			= $result;
		$output['totalPages'] 		= ceil(count($GetCount)/$recordPerPage);
		return $output;
	}



	/*
	Use 	: Get Ladger Chart
	Author 	: Axay Shah
	Date 	: 10 Sep,2019
	*/
	public static function GetChartData($request)
	{
		$data      		= array();
		$Month  		= intval((isset($request->month) && !empty($request->input('month')))? $request->input('month') : date("m"));
		$Year  			= intval((isset($request->year) && !empty($request->input('year')))? $request->input('year') : date("Y"));
		$MRF_ID     	= (isset($request->mrf_id)     && !empty($request->mrf_id)) ? $request->mrf_id 		: "" ;
		$START_DATE		= $Year."-".$Month."-01";
		$END_DATE		= date("Y-m-t",strtotime($START_DATE));
		$Result			= array();
		$totalAvg 		= 0;
		$totalQty 		= 0;
		$totalQtyOut 	= 0;
		$totalAvgOut 	= 0;
		$table 			= (new static)->getTable();
		$data			= self::select(	\DB::raw("CAST((SUM($table.inward)/1000) AS DECIMAL(12,1)) AS inward"),
										\DB::raw("CAST((SUM($table.outward)/1000) AS DECIMAL(12,1)) AS outward"),
										"$table.stock_date",
										\DB::raw("DATE_FORMAT($table.stock_date,'%d') as stock_day"),
										"$table.mrf_id")
							->where("$table.company_id",Auth()->user()->company_id);
		if(!empty($MRF_ID)) {
			$data->where("$table.mrf_id",$MRF_ID);
		}
		if(!empty($PRODUCT_ID)) {
			$data->where("$table.product_id",$PRODUCT_ID);
		}
		if(!empty($START_DATE) && !empty($END_DATE)) {
			$data->whereBetween("$table.stock_date",array($START_DATE,$END_DATE));
		} else if(!empty($START_DATE)) {
			$data->where("$table.stock_date",$START_DATE);
		} else if(!empty($END_DATE)) {
			$data->where("$table.stock_date",$END_DATE);
		}
		$SELECTSQL 	= LiveServices::toSqlWithBinding($data,true);
		$list 		= $data->orderBy("$table.stock_date")->groupBy(["$table.stock_date"])->get()->toArray();
		if(count($list) > 0) {
			foreach($list as $raw) {
				$totalQty = $totalQty + _FormatNumberV2($raw['inward']);
				$totalQtyOut = $totalQtyOut + _FormatNumberV2($raw['outward']);
			}
			$totalAvg 		= $totalQty / count($list);
			$totalAvgOut 	= $totalQtyOut / count($list);
		}
		// $Result['SELECTSQL'] 	= $SELECTSQL;
		$Result['chart_data'] 	= $list;
		$Result['avg_qty'] 		= _FormatNumberV2($totalAvg);
		$Result['avg_qty_out'] 	= _FormatNumberV2($totalAvgOut);
		return $Result;
	}

	/*
	Use 	: Get OutWard Department Chart
	Author 	: Axay Shah
	Date 	: 18 Sep,2019
	*/
	public static function GetOutwardDepartmentWise($request)
	{
		$data   	= array();
		$Month  	= intval((isset($request->month) && !empty($request->input('month')))? $request->input('month') : date("m"));
		$Year  		= intval((isset($request->year) && !empty($request->input('year')))? $request->input('year') : date("Y"));
		$MRF_ID     = (isset($request->mrf_id)     && !empty($request->mrf_id)) ? $request->mrf_id 		: "" ;
		$PRODUCT_ID = (isset($request->product_id) && !empty($request->product_id))? $request->product_id  : "" ;
		$START_DATE	= $Year."-".$Month."-01";
		$END_DATE	= date("Y-m-t",strtotime($START_DATE));
		$Result		= array();
		$totalQty 	= 0;
		$totalAvg 	= 0;
		$SalesMaster= new WmProductMaster();
		/* FOR OUTWARD CHART DEPARTMENT WISE */
		 $data 	= 	OutwardLadger::select(	\DB::raw("CAST((SUM(outward_ledger.quantity)/1000) AS DECIMAL(12,1)) AS quantity"),
											\DB::raw("outward_ledger.outward_date as date"),
											\DB::raw("DATE_FORMAT(outward_ledger.outward_date,'%d') as stock_day"),
											\DB::raw("AVG(outward_ledger.quantity) as avg_quantity"),
											\DB::raw("outward_ledger.sales_product_id"),
											\DB::raw("outward_ledger.product_id"),
											\DB::raw("P.title as sales_product_name"),
											"outward_ledger.mrf_id")
		->join($SalesMaster->getTable()." AS P","outward_ledger.sales_product_id","=","P.id")
		->where("outward_ledger.company_id",Auth()->user()->company_id);
		if(!empty($MRF_ID)) {
			$data->where("outward_ledger.mrf_id",$MRF_ID);
		}
		if(!empty($PRODUCT_ID)) {
			$data->where("outward_ledger.sales_product_id",$PRODUCT_ID);
		}
		if(!empty($START_DATE) && !empty($END_DATE)) {
			$data->whereBetween("outward_ledger.outward_date",array($START_DATE,$END_DATE));
		} else if(!empty($START_DATE)) {
			$data->where("outward_ledger.outward_date",$START_DATE);
		} else if(!empty($END_DATE)) {
			$data->where("outward_ledger.outward_date",$END_DATE);
		}
		$list = $data->orderBy("outward_ledger.outward_date")->groupBy(["outward_ledger.outward_date"])->get()->toArray();
		if(count($list) > 0) {
			foreach($list as $raw) {
				$totalQty =  $totalQty + $raw['quantity'];
			}
			$totalAvg =  $totalQty / count($list);
		}
		$Result['chart_data'] 	= $list;
		$Result['avg_qty'] 		= $totalAvg;
		return $Result;
	}


	/*
	Use 	: Get Inward product list
	Author 	: Axay Shah
	Date 	: 18 Sep,2019
	*/
	public static function GetInwardProductList($request)
	{
		$data   		= array();
		$Month  		= intval((isset($request->month) && !empty($request->input('month')))? $request->input('month') : "");
		$Year  			= intval((isset($request->year) && !empty($request->input('year')))? $request->input('year') : "");
		$product_type  	= (isset($request->product_type) && !empty($request->input('product_type')))? $request->input('product_type') : "P";
		$START_DATE		= $Year."-".$Month."-01";
		$END_DATE		= date("Y-m-t",strtotime($START_DATE));
		$Result			= array();
		$ProductMaster 	= new CompanyProductMaster();
		$ProductQuality = new CompanyProductQualityParameter();
		$WmProductMaster= new WmProductMaster();
		/* FOR INWARD CHART DEPARTMENT WISE */
		if ($product_type == "P") {
			$data 	= 	ProductInwardLadger::select(\DB::raw("inward_ledger.product_id"),
													\DB::raw("CONCAT(P.name,' ',PQ.parameter_name) AS product_name"))
							->join($ProductMaster->getTable()." AS P","inward_ledger.product_id","=","P.id")
							->join($ProductQuality->getTable()." as PQ","P.id","=","PQ.product_id")
							->where("inward_ledger.company_id",Auth()->user()->company_id)
							->where("inward_ledger.product_type",PRODUCT_PURCHASE)
							->whereBetween("inward_ledger.inward_date",array($START_DATE,$END_DATE))
							->orderBy("P.name")
							->groupBy(["inward_ledger.product_id"])
							->get();
		} else {
			$data 	= 	ProductInwardLadger::select(\DB::raw("inward_ledger.product_id"),
													\DB::raw("P.title AS product_name"))
							->join($WmProductMaster->getTable()." AS P","inward_ledger.product_id","=","P.id")
							->where("inward_ledger.company_id",Auth()->user()->company_id)
							->where("inward_ledger.product_type",PRODUCT_SALES)
							->whereBetween("inward_ledger.inward_date",array($START_DATE,$END_DATE))
							->orderBy("P.title")
							->groupBy(["inward_ledger.product_id"])
							->get();
		}
		return $data;
	}

	/*
	Use 	: Get OutWard Product List
	Author 	: Axay Shah
	Date 	: 18 Sep,2019
	*/
	public static function GetOutwardProductList($request){
		$data   	=  	array();
		$Month  	= intval((isset($request->month) && !empty($request->input('month')))? $request->input('month') : "");
		$Year  		= intval((isset($request->year) && !empty($request->input('year')))? $request->input('year') : "");
		$START_DATE	= 	$Year."-".$Month."-01";
		$END_DATE	= 	date("Y-m-t",strtotime($START_DATE));
		$Result		=  	array();
		$SalesMaster= 	new WmProductMaster();
		/* FOR OUTWARD CHART DEPARTMENT WISE */

			 $data 	= 	OutwardLadger::select(
							\DB::raw("outward_ledger.sales_product_id"),
							\DB::raw("P.title as sales_product_name")
						)
		->join($SalesMaster->getTable()." AS P","outward_ledger.sales_product_id","=","P.id")
		->where("outward_ledger.company_id",Auth()->user()->company_id)
		->whereBetween("outward_ledger.outward_date",array($START_DATE,$END_DATE))
		->orderBy("P.title")
		->groupBy(["outward_ledger.sales_product_id"])
		->get();
		return $data;
	}
	/*
	Use 	: Get InWard Department Chart
	Author 	: Axay Shah
	Date 	: 18 Sep,2019
	*/
	public static function GetInwardDepartmentWise($request)
	{
		$data   		= array();
		$Month  		= intval((isset($request->month) && !empty($request->input('month')))? $request->input('month') : date("m"));
		$Year  			= intval((isset($request->year) && !empty($request->input('year')))? $request->input('year') : date("Y"));
		$MRF_ID     	= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id 	: 0;
		$PRODUCT_ID 	= (isset($request->product_id) && !empty($request->product_id))? $request->product_id  : 0;
		$REPORT_TYPE 	= (isset($request->report_type) && !empty($request->report_type))? $request->report_type  : "P";
		$START_DATE		= $Year."-".$Month."-01";
		$END_DATE		= date("Y-m-t",strtotime($START_DATE));
		$Result			= array();
		$totalQty 		= 0;
		$totalAvg 		= 0;
		/* FOR INWARD CHART DEPARTMENT WISE */

		if ($REPORT_TYPE == "P") {
			$ProductMaster 	= new CompanyProductMaster();
			$WmBatchMaster 	= new WmBatchMaster();
			$data 			= ProductInwardLadger::select(\DB::raw("CAST((SUM(inward_ledger.quantity)/1000) AS DECIMAL(12,1)) as quantity"),
												\DB::raw("AVG(inward_ledger.quantity) as avg_quantity"),
												\DB::raw("inward_ledger.inward_date as date"),
												\DB::raw("DATE_FORMAT(inward_ledger.inward_date,'%d') as stock_day"),
												\DB::raw("inward_ledger.product_id"),
												\DB::raw("P.name as product_name"),
												"inward_ledger.mrf_id")
								->join($ProductMaster->getTable()." AS P","inward_ledger.product_id","=","P.id")
								->where("inward_ledger.company_id",Auth()->user()->company_id);
			if (!empty($MRF_ID)) $data->where("inward_ledger.mrf_id",$MRF_ID);
			$data->where("inward_ledger.product_id",$PRODUCT_ID);
			$data->where("inward_ledger.product_type",PRODUCT_PURCHASE);
			$data->whereBetween("inward_ledger.inward_date",array($START_DATE,$END_DATE));
			$list = $data->orderBy("inward_ledger.inward_date")->groupBy(["inward_ledger.inward_date"])->get()->toArray();
			if(count($list) > 0) {
				foreach($list as $raw) {
					$totalQty = $totalQty + $raw['quantity'];
				}
				$totalAvg = $totalQty / count($list);
			}
			$Result['chart_data'] 	= $list;
			$Result['avg_qty'] 		= $totalAvg;

			$DetailsData 	= ProductInwardLadger::select(	\DB::raw("inward_ledger.inward_date"),
															\DB::raw("inward_ledger.quantity as quantity"),
															\DB::raw("IF(BM.code IS NULL,'-',BM.code) as Batch_Code"),
															\DB::raw("	(CASE
																			WHEN inward_ledger.type = 'D' THEN 'Dispatch'
																			WHEN inward_ledger.type = 'I' THEN 'Inward'
																			WHEN inward_ledger.type = 'J' THEN 'Jobwork'
																			WHEN inward_ledger.type = 'P' THEN 'Inward'
																			WHEN inward_ledger.type = 'S' THEN 'Sales'
																			WHEN inward_ledger.type = 'T' THEN 'Transfer'
																			ELSE '-'
																		END) AS Inward_Type"),
															\DB::raw("inward_ledger.avg_price"))
							->join($ProductMaster->getTable()." AS P","inward_ledger.product_id","=","P.id")
							->leftjoin($WmBatchMaster->getTable()." AS BM","inward_ledger.batch_id","=","BM.batch_id")
							->where("inward_ledger.company_id",Auth()->user()->company_id);
			if (!empty($MRF_ID)) $DetailsData->where("inward_ledger.mrf_id",$MRF_ID);
			$DetailsData->where("inward_ledger.product_id",$PRODUCT_ID);
			$DetailsData->where("inward_ledger.product_type",PRODUCT_PURCHASE);
			$DetailsData->whereBetween("inward_ledger.inward_date",array($START_DATE,$END_DATE));
			$DetailsViewData 			= $DetailsData->orderBy("inward_ledger.inward_date")->get()->toArray();
			$Result['DetailsViewData'] 	= $DetailsViewData;
		} else {
			$ProductMaster 	= new CompanyProductMaster();
			$ProductQuality = new CompanyProductQualityParameter();
			$WmProductMaster= new WmProductMaster();
			$data 			= ProductInwardLadger::select(\DB::raw("CAST((SUM(inward_ledger.quantity)/1000) AS DECIMAL(12,1)) as quantity"),
												\DB::raw("AVG(inward_ledger.quantity) as avg_quantity"),
												\DB::raw("inward_ledger.inward_date as date"),
												\DB::raw("DATE_FORMAT(inward_ledger.inward_date,'%d') as stock_day"),
												\DB::raw("inward_ledger.product_id"),
												\DB::raw("P.title as product_name"),
												"inward_ledger.mrf_id")
								->join($WmProductMaster->getTable()." AS P","inward_ledger.product_id","=","P.id")
								->where("inward_ledger.company_id",Auth()->user()->company_id);
			if (!empty($MRF_ID)) $data->where("inward_ledger.mrf_id",$MRF_ID);
			$data->where("inward_ledger.product_id",$PRODUCT_ID);
			$data->where("inward_ledger.product_type",PRODUCT_SALES);
			$data->whereBetween("inward_ledger.inward_date",array($START_DATE,$END_DATE));
			$list = $data->orderBy("inward_ledger.inward_date")->groupBy(["inward_ledger.inward_date"])->get()->toArray();
			if(count($list) > 0) {
				foreach($list as $raw) {
					$totalQty = $totalQty + $raw['quantity'];
				}
				$totalAvg = $totalQty / count($list);
			}
			$Result['chart_data'] 	= $list;
			$Result['avg_qty'] 		= $totalAvg;
			$DetailsData 			= ProductInwardLadger::select(	\DB::raw("inward_ledger.inward_date"),
																	\DB::raw("inward_ledger.quantity as quantity"),
																	\DB::raw("IF(inward_ledger.production_report_id IS NULL,inward_ledger.ref_id,inward_ledger.production_report_id) as Batch_Code"),
																	\DB::raw("	(CASE
																						WHEN inward_ledger.type = 'D' THEN 'Direct Dispatch'
																						WHEN inward_ledger.type = 'I' THEN 'Inward'
																						WHEN inward_ledger.type = 'J' THEN 'Jobwork'
																						WHEN inward_ledger.type = 'P' THEN 'Production'
																						WHEN inward_ledger.type = 'S' THEN 'Production'
																						WHEN inward_ledger.type = 'T' THEN 'Transfer'
																						ELSE '-'
																					END) AS Inward_Type"),
																	\DB::raw("inward_ledger.avg_price"))
							->join($WmProductMaster->getTable()." AS P","inward_ledger.product_id","=","P.id")
							->leftjoin($ProductMaster->getTable()." AS PM","inward_ledger.purchase_product_id","=","PM.id")
							->leftjoin($ProductQuality->getTable()." as PQ","PM.id","=","PQ.product_id")
							->where("inward_ledger.company_id",Auth()->user()->company_id);
			if (!empty($MRF_ID)) $DetailsData->where("inward_ledger.mrf_id",$MRF_ID);
			$DetailsData->where("inward_ledger.product_id",$PRODUCT_ID);
			$DetailsData->where("inward_ledger.product_type",PRODUCT_SALES);
			$DetailsData->whereBetween("inward_ledger.inward_date",array($START_DATE,$END_DATE));
			$DetailsViewData 			= $DetailsData->orderBy("inward_ledger.inward_date")->get()->toArray();
			$Result['DetailsViewData'] 	= $DetailsViewData;
		}
		return $Result;
	}

	/*
	Use 	: Dispatch Chart With Transfer & Sales & Purchase
	Author 	: Axay Shah
	Date 	: 19 Sep,2019
	*/
	public static function DispatchSalesTransferChart()
	{
		$data   	= array();
		$Month  	= intval((isset($request->month) && !empty($request->input('month')))? $request->input('month') : date("m"));
		$Year  		= intval((isset($request->year) && !empty($request->input('year')))? $request->input('year') : date("Y"));
		$MRF_ID     = (isset($request->mrf_id)     && !empty($request->mrf_id)) ? $request->mrf_id 		: "" ;
		$PRODUCT_ID = (isset($request->product_id) && !empty($request->product_id))? $request->product_id  : "" ;
		$START_DATE	= $Year."-".$Month."-01";
		$END_DATE	= date("Y-m-t",strtotime($START_DATE));
		$Result		= array();
	}

	/*
	Use 	: Get Opening or closing Stock of product
	Author 	: Axay Shah
	Date 	: 08,November,2021
	*/
	public static function GetOpeningOrClosingStock($product_id,$product_type,$stock_date,$mrf_id,$opening=1){
		$STOCK 	= 0;
		$data 	= StockLadger::where(array(
			"product_type" 	=> $product_type,
			"product_id" 	=> $product_id,
			"stock_date" 	=> $stock_date,
			"company_id" 	=> Auth()->user()->company_id
		));
		if(!empty($mrf_id)){
			if(!is_array($mrf_id)){
				$mrf_id = explode(",",$mrf_id);
			}
			$data->whereIn("mrf_id",$mrf_id);
		}
		if($opening == 1){
			$STOCK 		= $data->sum("opening_stock");
		}elseif($opening == 0){
			$STOCK 		= $data->sum("closing_stock");
		}
		$STOCK_QTY 	= ($STOCK > 0) ? _FormatNumberV2($STOCK) : 0;
		return $STOCK_QTY;
	}

	/*
	Use 	: List Product Stock
	Author 	: Axay Shah
	Date 	: 31 Aug,2019
	*/
	public static function ListPurchaseProductStock($request)
	{
		$InwardLadger 	=  new ProductInwardLadger();
		$Department 	=  new WmDepartment();
		$Product 		=  new CompanyProductMaster();
		$PRO 			=  $Product->getTable();
		$QUALITY 		=  new CompanyProductQualityParameter();
		$self 			=  $InwardLadger->getTable();
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "$self.id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size') : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ? $request->input('pageNumber') : '';
		$MRF_ID     	= !empty($request->input('params.mrf_id')) ? $request->input('params.mrf_id') : array();
		$YEAR     		= !empty($request->input('params.year')) ? $request->input('params.year') : '';
		$PERIOD     	= !empty($request->input('params.period')) ? $request->input('params.period') : '';
		$SUB_PERIOD 	= !empty($request->input('params.sub_period')) ? $request->input('params.sub_period') : '';
		$PRODUCT_ID 	= !empty($request->input('params.product_id')) ? $request->input('params.product_id') : array();
		$NET_SUIT_CODE 	= !empty($request->input('params.net_suit_code')) ? $request->input('params.net_suit_code') : "";
		######## FINALCIAL YEAR FILTER ##########
		$START_YEAR 	= date("Y");
		$END_YEAR 		= date("Y",strtotime("+1 year"));
		if(!empty($YEAR)){
			$YEAR 		= explode("-",$YEAR);
			$START_YEAR = $YEAR[0];
			$END_YEAR 	= $YEAR[1];
		}
		if($PERIOD == 1){
			$month 		= (isset($SUB_PERIOD) && !empty($SUB_PERIOD)) ? $SUB_PERIOD : date('m');
			$month 		= (strlen($month) == 1) ? "0".$month : $month;
			$startDate 	= ($month <= 3) ? $END_YEAR."-".$month."-01" : $START_YEAR."-".$month."-01";
			$endDate 	= date('Y-m-t',strtotime($startDate));

		}elseif($PERIOD == 2){
				if($SUB_PERIOD == "Q1"){
					$startDate 	= $START_YEAR."-04-01";
					$endDate 	= $START_YEAR."-06-30";
				}elseif($SUB_PERIOD == "Q2"){
					$startDate 	= $START_YEAR."-07-01";
					$endDate 	= $START_YEAR."-09-30";
				}elseif($SUB_PERIOD == "Q3"){
				$startDate 	= $START_YEAR."-10-01";
				$endDate 	= $START_YEAR."-12-31";
			}elseif($SUB_PERIOD == "Q4"){
				$startDate 	= $END_YEAR."-01-01";
				$endDate 	= $END_YEAR."-03-31";
			}
		}elseif($PERIOD == 3){
			if($SUB_PERIOD == "HY1"){
				$startDate 	= $START_YEAR."-04-01";
				$endDate 	= $START_YEAR."-09-30";
			}elseif($SUB_PERIOD == "HY2"){
				$startDate 	= $START_YEAR."-10-01";
				$endDate 	= $END_YEAR."-03-31";
			}
		}else{
			$startDate 	= $START_YEAR."-04-01";
			$endDate 	= $END_YEAR."-03-31";
		}
		######## FINALCIAL YEAR FILTER ##########3
		$data 	= 	CompanyProductMaster::select(\DB::raw("$PRO.id as product_id"),
												\DB::raw("CONCAT($PRO.name,' ',QUALITY.parameter_name) AS name"),
												\DB::raw("$PRO.net_suit_code"))
					->join($QUALITY->getTable()." as QUALITY","$PRO.id","=","QUALITY.product_id")
					->where("$PRO.para_status_id",PRODUCT_STATUS_ACTIVE)
					->where("$PRO.company_id",Auth()->user()->company_id);
		if(!empty($PRODUCT_ID)) {
			$data->whereIn("$PRO.id",$PRODUCT_ID);
		}
		if(!empty($NET_SUIT_CODE)) {
			$data->where("$PRO.net_suit_code","like","%".$NET_SUIT_CODE."%");
		}
		if($request->has('ex') && !empty($request->input('ex')) && $request->input('ex') == EXPORT_ALL)
		{
			$recordPerPage 	= $data->get();
			$recordPerPage 	= count($recordPerPage);
			$result      	= $data->paginate($recordPerPage);
		} else {
			$result  = $data->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		}

		if(!empty($result))
		{
			$toArray = $result->toArray();
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0)
			{
				foreach($toArray['result'] as $key => $value)
				{
					$OPENING_QTY = self::GetOpeningOrClosingStock($value['product_id'],PRODUCT_PURCHASE,$startDate,$MRF_ID);
					/** TOTAL INWARD/OUTWARD SQL */
					$INWARD_SQL 	= "	SELECT SUM(quantity) AS INWARD_QTY
										FROM `inward_ledger`
										WHERE `product_id` = ".$value['product_id']."
										AND `product_type` = ".PRODUCT_PURCHASE."
										AND `mrf_id` IN (".implode(",",$MRF_ID).")
										AND `inward_date` BETWEEN '$startDate' AND '$endDate'";
					$INWARD_RES 	= \DB::select($INWARD_SQL);
					$INWARD_QTY 	= isset($INWARD_RES[0]->INWARD_QTY)?$INWARD_RES[0]->INWARD_QTY:0;
					$OUTWARD_SQL 	= "	SELECT SUM(quantity) AS OUTWARD_QTY
										FROM `outward_ledger`
										WHERE `product_id` = ".$value['product_id']."
										AND `mrf_id` IN (".implode(",",$MRF_ID).")
										AND sales_product_id = 0
										AND `outward_date` BETWEEN '$startDate' AND '$endDate'";
					$OUTWARD_RES 	= \DB::select($OUTWARD_SQL);
					$OUTWARD_QTY 	= isset($OUTWARD_RES[0]->OUTWARD_QTY)?$OUTWARD_RES[0]->OUTWARD_QTY:0;
					/** TOTAL INWARD/OUTWARD SQL */

					// if(date("Y-m",strtotime($endDate)) == date("Y-m")){
					// 	$endDate = date("Y-m-d");
					// }
					$CURRENT_QTY 									= _FormatNumberV2(($OPENING_QTY + $INWARD_QTY) - $OUTWARD_QTY);
					$toArray['result'][$key]['total_opening_stock'] = _FormatNumberV2($OPENING_QTY);
					$toArray['result'][$key]['total_inward_stock'] 	= _FormatNumberV2($INWARD_QTY);
					$toArray['result'][$key]['total_inward'] 		= _FormatNumberV2($INWARD_QTY);
					$toArray['result'][$key]['total_outward'] 		= _FormatNumberV2($OUTWARD_QTY);
					$toArray['result'][$key]['total_current_stock'] = _FormatNumberV2($CURRENT_QTY);
					$toArray['result'][$key]['start_date'] 			= $startDate;
					$toArray['result'][$key]['end_date'] 			= $endDate;
				}
			}
			$result = $toArray;
		}
		return $result;
	}
	/*
	Use 	: List Product Stock
	Author 	: Axay Shah
	Date 	: 31 Aug,2019
	*/
	public static function ListSalesProductStock($request){

		$Department 	=  new WmDepartment();
		$Product 		=  new WmProductMaster();
		$Stock 			=  new StockLadger();
		$Inward 		=  new ProductInwardLadger();
		$Outward 		=  new OutWardLadger();
		$Today          = date('Y-m-d');
		$CLOSING_DATE 	= $Today;
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size') : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ? $request->input('pageNumber') : '';
		$MRF_ID     	= !empty($request->input('params.mrf_id')) ? $request->input('params.mrf_id') : '';
		$PRODUCT_ID     = ($request->has('params.sales_product_id') && !empty($request->input('params.sales_product_id')) ? $request->input('params.sales_product_id') : array());
		$MRF_ID     	= !empty($request->input('params.mrf_id')) ? $request->input('params.mrf_id') : '';
		$YEAR     		= !empty($request->input('params.year')) ? $request->input('params.year') : '';
		$PERIOD     	= !empty($request->input('params.period')) ? $request->input('params.period') : '';
		$SUB_PERIOD 	= !empty($request->input('params.sub_period')) ? $request->input('params.sub_period') : '';
		$NET_SUIT_CODE 	= !empty($request->input('params.net_suit_code')) ? $request->input('params.net_suit_code') : "";
		######## FINALCIAL YEAR FILTER ##########3
		$START_YEAR 	= date("Y");
		$END_YEAR 		= date("Y",strtotime("+1 year"));
		if(!empty($YEAR)){
			$YEAR = explode("-",$YEAR);
			$START_YEAR = $YEAR[0];
			$END_YEAR 	= $YEAR[1];
		}
		if($PERIOD == 1){
			$month 		= (isset($SUB_PERIOD) && !empty($SUB_PERIOD)) ? $SUB_PERIOD : date('m');
			$month 		= (strlen($month) == 1) ? "0".$month : $month;
			$startDate 	= ($month <= 3) ? $END_YEAR."-".$month."-01" : $START_YEAR."-".$month."-01";
			$endDate 	= date('Y-m-t',strtotime($startDate));
			// $endDate 	= '2021-12-26';
		}elseif($PERIOD == 2){
				if($SUB_PERIOD == "Q1"){
					$startDate 	= $START_YEAR."-04-01";
					$endDate 	= $START_YEAR."-06-30";
				}elseif($SUB_PERIOD == "Q2"){
					$startDate 	= $START_YEAR."-07-01";
					$endDate 	= $START_YEAR."-09-30";
				}elseif($SUB_PERIOD == "Q3"){
				$startDate 	= $START_YEAR."-10-01";
				$endDate 	= $START_YEAR."-12-31";
			}elseif($SUB_PERIOD == "Q4"){
				$startDate 	= $END_YEAR."-01-01";
				$endDate 	= $END_YEAR."-03-31";
			}
		}elseif($PERIOD == 3){
			if($SUB_PERIOD == "HY1"){
				$startDate 	= $START_YEAR."-04-01";
				$endDate 	= $START_YEAR."-09-30";
			}elseif($SUB_PERIOD == "HY2"){
				$startDate 	= $START_YEAR."-10-01";
				$endDate 	= $END_YEAR."-03-31";
			}
		}else{
			$startDate 	= $START_YEAR."-04-01";
			$endDate 	= $END_YEAR."-03-31";
		}
		######## FINALCIAL YEAR FILTER ##########3
		$data 	= 	WmProductMaster::select(
						\DB::raw("title"),
						\DB::raw("id as sales_product_id"),
						\DB::raw("id"),
						\DB::raw("net_suit_code"))
		->where("status",1)

		->where("company_id",Auth()->user()->company_id);
		if($PRODUCT_ID)
		{
			$data->whereIn("id",$PRODUCT_ID);
		}
		if($NET_SUIT_CODE)
		{
			$data->where("net_suit_code","like","%".$NET_SUIT_CODE."%");
		}
		if($request->has('ex') && !empty($request->input('ex')) && $request->input('ex') == EXPORT_ALL)
		{
			$recordPerPage 	= $data->get();
			$recordPerPage 	= count($recordPerPage);
			$result      	= $data->paginate($recordPerPage);
		}else{
			$result     	= $data->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		}
		if(!empty($result)){
			$toArray = $result->toArray();
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0){
				foreach($toArray['result'] as $key => $value){
					$OPENING_QTY = self::GetOpeningOrClosingStock($value['sales_product_id'],PRODUCT_SALES,$startDate,$MRF_ID);
					$CLOSING_QTY = self::GetOpeningOrClosingStock($value['sales_product_id'],PRODUCT_SALES,$endDate,$MRF_ID,0);
					$INWARD_QTY  = ProductInwardLadger::whereBetween("inward_date",array($startDate,$endDate))->whereIn("mrf_id",$MRF_ID)->where("product_id",$value['sales_product_id'])->where("product_type",PRODUCT_SALES)
					->groupBy("product_id")->sum('quantity');
					$OUTWARD_QTY  = OutWardLadger::whereBetween("outward_date",array($startDate,$endDate))
					->whereIn("mrf_id",$MRF_ID)
					->where("sales_product_id",$value['sales_product_id'])
					->sum('quantity');
					// if(date("Y-m",strtotime($endDate)) == date("Y-m")){
					// 	$endDate = date("Y-m-d");
					// }
					$CURRENT_QTY = _FormatNumberV2(($OPENING_QTY + $INWARD_QTY) - $OUTWARD_QTY);
					$toArray['result'][$key]['total_opening_stock'] = _FormatNumberV2($OPENING_QTY);
					$toArray['result'][$key]['total_inward_stock'] 	= _FormatNumberV2($INWARD_QTY);
					$toArray['result'][$key]['total_inward'] 		= _FormatNumberV2($INWARD_QTY);
					$toArray['result'][$key]['total_outward'] 		= _FormatNumberV2($OUTWARD_QTY);
					$toArray['result'][$key]['total_current_stock'] = _FormatNumberV2($CURRENT_QTY);
					$toArray['result'][$key]['start_date'] 			= $startDate;
					$toArray['result'][$key]['end_date'] 			= $endDate;
				}
			}
			$result = $toArray;
		}
		return $result;
	}
	/*
	Use 	: Generate SynopsisReport
	Author 	: Axay Shah
	Date 	: 13 May 2020
	*/
	public static function SynopsisReport($request)
	{
		$result 		= array();
		$MONTH 			= (isset($request->month) 	&& !empty($request->month)) ? $request->month : "05";
		$YEAR 			= (isset($request->year) 	&& !empty($request->year)) ? $request->year : "2020";
		$MRF_ID 		= (isset($request->mrf_id) 	&& !empty($request->mrf_id)) ? $request->mrf_id : 0;
		$START_DATE 	= date("Y-m-d",strtotime($YEAR."-".$MONTH."-01"));
		$END_DATE 		= date("Y-m-t",strtotime($START_DATE));
		$TOTAL_DAYS  	= (date("Y-m") == date("Y-m",strtotime($START_DATE))) ? date("d"): date("d",strtotime($END_DATE));
		$TODAY 			= date("Y-m-d");
		$TWO_ARRAY 		= array();
		$TWO_OUT_ARRAY 	= array();
		$THREE_ARRAY 	= array();
		$i 				= 0;
		$j				= 0;
		$k				= 0;
		$TOTAL_FTD 		= 0;
		$TOTAL_MTD 		= 0;
		$TOTAL_AVG 		= 0;
		$TOTAL_OUT_FTD 	= 0;
		$TOTAL_OUT_MTD 	= 0;
		$TOTAL_OUT_AVG 	= 0;
		$TOTAL_3D_FTD 	= 0;
		$TOTAL_3D_MTD 	= 0;
		$TOTAL_3D_AVG 	= 0;
		$TWO_INWARD 	= SYNOPSIS_INWARD;
		$TWO_OUTWARD 	= SYNOPSIS_OUTWARD;
		$SYNOPSIS_3D 	= SYNOPSIS_3D;
		$SQL_QUERY_2D	= "";
		$SQL_QUERY_3D	= "";
		foreach($TWO_INWARD AS $RAW)
		{
			$FTD 		= 0;
			$MTD 		= 0;
			$AVG 		= 0;
			$FETCH 		= "";

			$MTD_DATA 	= self::Inward2dSynopsisQuery($START_DATE,$END_DATE,$TODAY,$RAW,$MRF_ID);
			$FTD_DATA 	= self::Inward2dSynopsisQuery($TODAY,$TODAY,$TODAY,$RAW,$MRF_ID);

			if(!empty($MTD_DATA)){
				foreach($MTD_DATA as $value){
					$MTD += $value->QTY;
				}
			}

			$FTD 		= (isset($FTD_DATA[0]->QTY) && !empty($FTD_DATA[0]->QTY) ? _FormatNumberV2($FTD_DATA[0]->QTY) : 0);
			if(!empty($MTD_DATA) && $MTD > 0){
				$AVG 	=  _FormatNumberV2($MTD / $TOTAL_DAYS);
			}
			if(SYNOPSIS_OTHERS == $RAW){
				$FTD = 0;
				$MTD = 0;
				$AVG = 0;
			}
			$TOTAL_FTD 	+= _FormatNumberV2($FTD);
			$TOTAL_MTD 	+= _FormatNumberV2($MTD);
			$TOTAL_AVG 	+= _FormatNumberV2($AVG);
			$TWO_ARRAY[$i]["title"] 	= $RAW;
			$TWO_ARRAY[$i]["FTD"] 		= $FTD;
			$TWO_ARRAY[$i]["MTD"] 		= _FormatNumberV2($MTD);
			$TWO_ARRAY[$i]["DAILY_AVG"] = $AVG;
			$i++;
		}
		/* INWARD 2D FTD MTD AND AVG*/
		/* OUTWARD 2D FTD MTD AND AVG*/
		foreach($TWO_OUTWARD AS $RAW1)
		{
			$OUT_FTD 	= 0;
			$OUT_MTD 	= 0;
			$OUT_AVG 	= 0;
			$OUTWARD 	= "";

			$OUT_MTD_DATA 	= self::OutWard2dSynopsisQuery($START_DATE,$END_DATE,$TODAY,$RAW1,$MRF_ID);
			$OUT_FTD_DATA 	= self::OutWard2dSynopsisQuery($TODAY,$TODAY,$TODAY,$RAW1,$MRF_ID);

			if(!empty($OUT_MTD_DATA)){
				foreach($OUT_MTD_DATA as $value){
					$OUT_MTD += $value->QTY;
				}
			}

			$OUT_FTD 		= (isset($OUT_FTD_DATA[0]->QTY) && !empty($OUT_FTD_DATA[0]->QTY) ? _FormatNumberV2($OUT_FTD_DATA[0]->QTY) : 0);
			if(!empty($OUT_MTD_DATA) && $OUT_MTD > 0){
				$OUT_AVG 	=  _FormatNumberV2($OUT_MTD / $TOTAL_DAYS);
			}




			// $OUTWARD 	= self::OutWard2dSynopsisQuery($START_DATE,$END_DATE,$TODAY,$RAW1,$MRF_ID);
			// $OUT_FTD 	= (isset($OUTWARD[1]->QTY) && !empty($OUTWARD[1]->QTY) ? _FormatNumberV2($OUTWARD[1]->QTY) : 0);
			// $OUT_MTD 	= (isset($OUTWARD[0]->QTY) && !empty($OUTWARD[0]->QTY) ? _FormatNumberV2($OUTWARD[0]->QTY) : 0);
			// $OUT_AVG 	= (isset($OUTWARD[0]->AVG_QTY) && !empty($OUTWARD[0]->AVG_QTY) ? _FormatNumberV2($OUTWARD[0]->AVG_QTY) : 0) ;
			$TOTAL_OUT_FTD 	+= _FormatNumberV2($OUT_FTD);
			$TOTAL_OUT_MTD 	+= _FormatNumberV2($OUT_MTD);
			$TOTAL_OUT_AVG 	+= _FormatNumberV2($OUT_AVG);
			$TWO_OUT_ARRAY[$j]["title"] 	= $RAW1;
			$TWO_OUT_ARRAY[$j]["FTD"] 		= _FormatNumberV2($OUT_FTD);
			$TWO_OUT_ARRAY[$j]["MTD"] 		= _FormatNumberV2($OUT_MTD);
			$TWO_OUT_ARRAY[$j]["DAILY_AVG"] = _FormatNumberV2($OUT_AVG);
			$j++;
		}

		foreach($SYNOPSIS_3D AS $RAW2)
		{
			$FTD_3D 		= 0;
			$MTD_3D 		= 0;
			$AVG_3D 		= 0;
			$FETCH_3D 		= "";

			$FETCH_MTD_DATA = self::Synopsis3DQuery($START_DATE,$END_DATE,$TODAY,$RAW2,$MRF_ID);
			$FETCH_3D 		= self::Synopsis3DQuery($TODAY,$TODAY,$TODAY,$RAW2,$MRF_ID);
			if(SYNOPSIS_3D_INWARD == $RAW2 || SYNOPSIS_3D_SORTING == $RAW2 || SYNOPSIS_3D_GRINDING == $RAW2 ||SYNOPSIS_3D_WASHING == $RAW2){
				$FTD_3D 	= (isset($FETCH_3D[0]->actual_quantity) && !empty($FETCH_3D[0]->actual_quantity	) ? _FormatNumberV2($FETCH_3D[0]->actual_quantity) : 0);
				if(!empty($FETCH_MTD_DATA)){
					foreach($FETCH_MTD_DATA as $value){
						$MTD_3D += $value->actual_quantity;
					}
				}
				if(!empty($FETCH_MTD_DATA) && $MTD_3D > 0){
					$AVG_3D 	=  _FormatNumberV2($MTD_3D / $TOTAL_DAYS);
				}
			}elseif(SYNOPSIS_3D_TRANSFER == $RAW2){
				$FTD_3D 	= (isset($FETCH_3D[0]->quantity) && !empty($FETCH_3D[0]->quantity) ? _FormatNumberV2($FETCH_3D[0]->quantity) : 0);
				if(!empty($FETCH_MTD_DATA)){
					foreach($FETCH_MTD_DATA as $value){
						$MTD_3D += $value->quantity;
					}
				}
				if(!empty($FETCH_MTD_DATA) && $MTD_3D > 0){
					$AVG_3D 	=  _FormatNumberV2($MTD_3D / $TOTAL_DAYS);
				}
			}elseif(SYNOPSIS_3D_SALES == $RAW2){
				$FTD_3D 	= (isset($FETCH_3D[0]->quantity) && !empty($FETCH_3D[0]->quantity) ? _FormatNumberV2($FETCH_3D[0]->quantity) : 0);
				if(!empty($FETCH_MTD_DATA)){
					foreach($FETCH_MTD_DATA as $value){
						$MTD_3D += $value->quantity;
					}
				}
				if(!empty($FETCH_MTD_DATA) && $MTD_3D > 0){
					$AVG_3D 	=  _FormatNumberV2($MTD_3D / $TOTAL_DAYS);
				}
			}
			$TOTAL_3D_FTD 	+= _FormatNumberV2($FTD_3D);
			$TOTAL_3D_MTD 	+= _FormatNumberV2($MTD_3D);
			$TOTAL_3D_AVG 	+= _FormatNumberV2($AVG_3D);
			$THREE_ARRAY[$k]["title"] 		= $RAW2;
			$THREE_ARRAY[$k]["FTD"] 		= _FormatNumberV2($FTD_3D);
			$THREE_ARRAY[$k]["MTD"] 		= _FormatNumberV2($MTD_3D);
			$THREE_ARRAY[$k]["DAILY_AVG"] 	= _FormatNumberV2($AVG_3D);
			$k++;
		}
		$result['2D']['synopsis_type'] 				= "2D synopsis";
		$result['2D']['start_date'] 				= $START_DATE;
		$result['2D']['synopsis_id'] 				= 1041001;
		$result['2D']['total_FTD_inward'] 			= _FormatNumberV2($TOTAL_FTD);
		$result['2D']['total_MTD_inward'] 			= _FormatNumberV2($TOTAL_MTD);
		$result['2D']['total_daily_avg_inward']		= _FormatNumberV2($TOTAL_AVG);
		$result['2D']['total_FTD_outward'] 			= _FormatNumberV2($TOTAL_OUT_FTD);
		$result['2D']['total_MTD_outward'] 			= _FormatNumberV2($TOTAL_OUT_MTD);
		$result['2D']['total_daily_avg_outward'] 	= _FormatNumberV2($TOTAL_OUT_AVG);
		$result['2D']['inward'] 					= $TWO_ARRAY;
		$result['3D']['synopsis_type'] 				= "3D synopsis";
		$result['3D']['synopsis_id'] 				= 1041002;
		$result['2D']['outward'] 					= $TWO_OUT_ARRAY;
		$result['3D']['data'] 						= $THREE_ARRAY;
		return $result;
	}



	public static function Inward2dSynopsisQuery($startDate,$endDate,$today,$type,$mrf_id=0)
	{
		$WHERE = "";
		if(SYNOPSIS_FOC == $type){
			$WHERE = " AND IL.product_id IN (".FOC_PRODUCT.") ";
		}elseif(SYNOPSIS_PAID == $type){
			$WHERE = " AND IL.product_id NOT IN (".FOC_PRODUCT.",".RDF_PRODUCT.") ";
		}elseif(SYNOPSIS_OTHERS == $type){
			$WHERE = "";
		}elseif(SYNOPSIS_RDF == $type){
			$WHERE = " AND IL.product_id IN (".RDF_PRODUCT.") ";
		}

		if(!empty($mrf_id)){
			$WHERE .= " AND IL.mrf_id = $mrf_id";
		}
			$FOC_SQL = "SELECT SUM(IL.QUANTITY) AS QTY,AVG(IL.QUANTITY) AS AVG_QTY,COM.id as product_id
						FROM inward_ledger as IL
						INNER JOIN company_product_master COM ON IL.product_id = COM.id
						WHERE COM.product_tagging_id = ".PARA_2D_TAGGING."
						AND IL.product_type = ".PRODUCT_PURCHASE."
						AND IL.inward_date BETWEEN '$startDate' and '$endDate' $WHERE
						GROUP BY IL.inward_date";

		// echo $FOC_SQL;
		// exit;
	$FOC = \DB::SELECT($FOC_SQL);
		return $FOC;
	}



	public static function OutWard2dSynopsisQuery($startDate,$endDate,$today,$type,$mrf_id=0)
	{
		$OUTWARD 	= array();
		$WHERE 		= "";
		if(SYNOPSIS_SALES == $type){
			$WHERE = " AND IL.type ='".TYPE_DISPATCH."'";
		} elseif(SYNOPSIS_TRANSFER == $type) {
			$WHERE = " AND IL.type ='".TYPE_TRANSFER."'";
		} elseif(SYNOPSIS_UNSHAREDDED == $type) {
			$WHERE = "";
		} elseif(SYNOPSIS_SHAREDDED == $type) {
			$WHERE = "";
		} elseif(SYNOPSIS_PET_TRANSFER == $type) {
			$WHERE = " AND IL.sales_product_id IN (76,74,308)";
		}
		$QUERY_ONLY_FOR = array(SYNOPSIS_SALES,SYNOPSIS_TRANSFER,SYNOPSIS_PET_TRANSFER);


		if(!empty($mrf_id)){
			$WHERE .= " AND IL.mrf_id = $mrf_id";
		}
		if(in_array($type,$QUERY_ONLY_FOR)){
			$OUTWARD_SQL = "SELECT SUM(IL.QUANTITY) AS QTY,AVG(IL.QUANTITY) AS AVG_QTY,COM.id as product_id
				FROM outward_ledger as IL
				INNER JOIN wm_product_master COM ON IL.sales_product_id = COM.id
				WHERE  COM.product_tagging_id = ".PARA_2D_TAGGING." $WHERE
				AND IL.outward_date BETWEEN '$startDate' and '$endDate'
				GROUP BY IL.outward_date";
				// echo $OUTWARD_SQL;
				// exit;
			$OUTWARD = \DB::SELECT($OUTWARD_SQL);
		}

		return $OUTWARD;
	}
	/*
	Use 	: For outward 2d synopsis product
	Author 	: Axay Shah
	Date 	: 15 May 2020
	*/
	public static function Synopsis3DQuery($startDate,$endDate,$today,$type,$MRF_ID=0)
	{
		$company_id = Auth()->user()->company_id;
		$WHERE  	= "";
		$WHERE1 	= "";
		$WHERE2 	= "";
		if(SYNOPSIS_3D_INWARD == $type) {
			$WHERE1 = 	" AND JOPM.inward_date BETWEEN '".$startDate."' AND '".$endDate."'";
			$WHERE2 = 	" AND JOPM.inward_date = '".$today."'";
		} elseif(SYNOPSIS_3D_SORTING == $type) {
			$WHERE1 = 	" AND JTM.jobwork_type_id = ".PARA_JOBWORK_SORTING." AND JOPM.inward_date BETWEEN '".$startDate."' AND '".$endDate."'";
			$WHERE2 = 	" AND JTM.jobwork_type_id = ".PARA_JOBWORK_SORTING." AND JOPM.inward_date = '".$today."'";
		} elseif(SYNOPSIS_3D_GRINDING == $type) {
			$WHERE1 = 	" AND JTM.jobwork_type_id = ".PARA_JOBWORK_GRINDING." AND JOPM.inward_date BETWEEN '".$startDate."' AND '".$endDate."'";
			$WHERE2 = 	" AND JTM.jobwork_type_id = ".PARA_JOBWORK_GRINDING." AND JOPM.inward_date = '".$today."'";
		} elseif(SYNOPSIS_3D_WASHING == $type) {
			$WHERE1 = 	" AND JTM.jobwork_type_id = ".PARA_JOBWORK_WASHING." AND JOPM.inward_date BETWEEN '".$startDate."' AND '".$endDate."'";
			$WHERE2 = 	" AND JTM.jobwork_type_id = ".PARA_JOBWORK_WASHING." AND JOPM.inward_date = '".$today."'";
		} elseif(SYNOPSIS_3D_SALES == $type) {
			$WHERE1 = 	" AND JM.jobwork_date BETWEEN '".$startDate."' AND '".$endDate."'";
			$WHERE2 = 	" AND JM.jobwork_date = '".$today."'";
		}
		if($MRF_ID > 0){
			$WHERE =(SYNOPSIS_3D_TRANSFER == $type) ? " AND WTM.origin_mrf = ".$MRF_ID : " AND JM.mrf_id = ".$MRF_ID;
		}
		if(SYNOPSIS_3D_TRANSFER == $type) {
			$SQL = "SELECT SUM(WTP.quantity) AS quantity,AVG(WTP.quantity) AS avg_quantity
					FROM wm_transfer_master WTM
					INNER JOIN wm_transfer_product as WTP on WTM.id = WTP.transfer_id
					INNER JOIN wm_product_master COM ON WTP.product_id = COM.id
					WHERE COM.company_id = ".$company_id."
					AND COM.product_tagging_id = ".PARA_3D_TAGGING." AND
					WTM.transfer_date BETWEEN '".$startDate."' AND '".$endDate."' $WHERE";
		} else {
			$SQL = "SELECT AVG(JOPM.quantity) AS avg_quantity,AVG(JOPM.actual_quantity) AS avg_actual_quantity,
					SUM(JOPM.actual_quantity) as actual_quantity,sum(JOPM.quantity) as quantity
					FROM jobwork_master as JM
					INNER JOIN jobwork_outward_product_mapping as JOPM ON JM.id =  JOPM.jobwork_id
					left JOIN wm_product_master COM ON JOPM.product_id = COM.id
					left JOIN jobwork_tagging_master JTM ON JM.id= JTM.jobwork_id
					WHERE COM.company_id = ".$company_id."
					AND COM.product_tagging_id = ".PARA_3D_TAGGING."
					$WHERE1 $WHERE";
		}
		$SQL_RESULT = \DB::SELECT($SQL);
		return $SQL_RESULT;
	}

	/*
	Use 	: List Product Stock (Sales) Revised by KP as AVG Price should be calculated MRF Wise
	Author 	: Kalpak Prajapati
	Date 	: 18 Oct,2021
	*/
	public static function ListSalesProductTodayStock($request)
	{
		$Today          = date('Y-m-d');
		$MRF_ID     	= !empty($request->input('params.mrf_id')) ? $request->input('params.mrf_id') : '';
		$toArray 		= array();
		$MRF_ID 		= (($request->bill_from_id) && !empty($request->bill_from_id)) ? $request->bill_from_id : array();
		$PRODUCT_ID 	= (($request->product_id) && !empty($request->product_id)) ? $request->product_id : array();
		$BASELOCATIONID = Auth()->user()->base_location;
		$ADMINUSERID 	= Auth()->user()->adminuserid;
		$COMPANY_ID 	= Auth()->user()->company_id;
		$MRF_IDS		= WmDepartment::where("base_location_id",$BASELOCATIONID)->where("status",1)->pluck("id")->toArray();
		if(!empty($PRODUCT_ID)) {
			if(!is_array($PRODUCT_ID)) {
				$PRODUCT_ID = explode(",",$PRODUCT_ID);
			}
		}
		if(!empty($MRF_ID)) {
			if(!is_array($MRF_ID)) {
				$MRF_ID = explode(",",$MRF_ID);
			}
		}
		########### DAYS ARRAY ###############
		$DaysArray 		= array();
		$arrDaysLoop	= 4;
		$DCounter 		= 0;
		while($DCounter<=$arrDaysLoop) {
			$NewDate = date("Y-m-d",strtotime("-$DCounter Day"));
			array_push($DaysArray,$NewDate);
			$DCounter++;
		}
		########### DAYS ARRAY ###############
		$arrResult		= array();
		$arrDaysLable	= array();
		$WHERE_COND 	= "";
		$MRFID 			= array();
		if(!empty($MRF_ID)) {
			$MRFID 	= $MRF_ID;
		} else {
			$MRFID 	= $MRF_IDS;
		}
		$WHERE_COND = " AND mrf_id IN (".implode(",",$MRFID).")";
		$EXTRA_COLS = "";
		$SEPARATOR	= ",";
		foreach($DaysArray as $DayID=>$Date)
		{
			$IsToday 	= ($Date == $Today)?1:0;
			$EXTRA_COLS .= $SEPARATOR." getProductCurrentStock(wm_product_master.id,'".$Date."','".implode(",",$MRFID)."',".$IsToday.",".PRODUCT_SALES.") AS STOCK_".str_replace("-","_",$Date);
		}

		$SELECT_SQL = "	SELECT wm_product_master.id as sales_product_id,wm_product_master.title
						$EXTRA_COLS
						FROM wm_product_master
						WHERE wm_product_master.status = 1
						AND wm_product_master.company_id = $COMPANY_ID";
		if(!empty($PRODUCT_ID)) {
			$SELECT_SQL .= " AND wm_product_master.id IN (".implode(",",$PRODUCT_ID).")";
		}
		$SQL_RESULT = \DB::SELECT($SELECT_SQL);
		if(!empty($SQL_RESULT)) {
			foreach($SQL_RESULT as $key => $SQLROW)
			{
				$TODAY_STOCK_COL 			= "STOCK_".str_replace("-","_",$Today);
				$TOTAL_STOCK 				= floatval((isset($SQLROW->$TODAY_STOCK_COL)?$SQLROW->$TODAY_STOCK_COL:0));
				if (empty($TOTAL_STOCK)) continue; //Exclude Empty Stock Items
				$AVG_PRICE 					= 0;
				/** GET AVG PRICE FOR PRODUCT */
				$T_S = 0;
				$T_P = 0;
				foreach($MRFID AS $ID)
				{
					$AVG_PRICE_SQL 	= "	SELECT stock_ladger.avg_price,
										getSalesProductCurrentStock(".$SQLROW->sales_product_id.",'".$Today."',".$ID.",0) AS Current_Stock
										FROM stock_ladger
										WHERE stock_ladger.product_id = ".$SQLROW->sales_product_id."
										AND stock_ladger.stock_date = '".$Today."'
										AND stock_ladger.product_type = ".PRODUCT_SALES."
										AND stock_ladger.mrf_id = ".$ID;
					$AVG_PRICE_RES 	= \DB::SELECT($AVG_PRICE_SQL);
					foreach($AVG_PRICE_RES AS $AVG_PRICE_ROW) {
						$T_S += $AVG_PRICE_ROW->Current_Stock;
						$T_P += ($AVG_PRICE_ROW->Current_Stock * $AVG_PRICE_ROW->avg_price);
					}
				}
				$AVG_PRICE = round((($T_S > 0)?($T_P / $T_S):0),2);
				/** GET AVG PRICE FOR PRODUCT */

				$arrRow 									= array();
				$TOTAL_STOCK_AMT 							= ($TOTAL_STOCK > 0 && $AVG_PRICE > 0) ? $TOTAL_STOCK * $AVG_PRICE : 0;
				$arrRow['sales_product_id'] 				= $SQLROW->sales_product_id;
				$arrRow['title'] 							= $SQLROW->title;
				$arrRow['total_current_stock'] 				= ($TOTAL_STOCK > 0) ? _FormatNumberV2($TOTAL_STOCK) : 0;
				$arrRow['total_stock_amount'] 				= ($TOTAL_STOCK_AMT > 0) ? _FormatNumberV2($TOTAL_STOCK_AMT) : 0;
				$arrRow['avg_price'] 						= ($AVG_PRICE > 0) ? _FormatNumberV2($AVG_PRICE) : 0;
				$DayWiseData 								= array();
				$DayID 										= 0;
				foreach($DaysArray as $Date)
				{
					if ($Date == $Today) continue;
					$COL_NAME 						= "STOCK_".str_replace("-","_",$Date);
					$DayWiseData[$DayID]["stock"]	= (isset($SQLROW->$COL_NAME)?$SQLROW->$COL_NAME:0);
					if ($Date == $Today) {
						$DayWiseData[$DayID]["date"]	= "Today";
						$arrDaysLable[$DayID] 			= "Today";
					} else {
						$DayWiseData[$DayID]["date"]	= $Date;
						$arrDaysLable[$DayID] 			= $Date;
					}
					$DayID++;
				}
				$arrRow['date_wise_data'] = $DayWiseData;
				array_push($arrResult,$arrRow);
			}
		}
		$toArray['result'] 	= $arrResult;
		$toArray['lable'] 	= $arrDaysLable;
		return $toArray;
	}

	/*
	Use 	: List Purchase product today stock
	Author 	: Axay Shah
	Date 	: 12 June,2020
	*/
	public static function ListPurchaseProductTodayStock($request)
	{
		$Today          = date('Y-m-d');
		$MRF_ID 		= (($request->bill_from_id) && !empty($request->bill_from_id)) ? $request->bill_from_id : array();
		$PRODUCT_ID 	= (($request->product_id) && !empty($request->product_id)) ? $request->product_id : array();
		$BASELOCATIONID = Auth()->user()->base_location;
		$ADMINUSERID 	= Auth()->user()->adminuserid;
		$MRF_IDS		= WmDepartment::where("base_location_id",$BASELOCATIONID)->where("status",1)->pluck("id")->toArray();
		if(!empty($PRODUCT_ID)) {
			if(!is_array($PRODUCT_ID)) {
				$PRODUCT_ID = explode(",",$PRODUCT_ID);
			}
		}
		if(!empty($MRF_ID)) {
			if(!is_array($MRF_ID)) {
				$MRF_ID = explode(",",$MRF_ID);
			}
		}
		########### DAYS ARRAY ###############
		$DaysArray 		= array();
		$arrDaysLoop	= 5;
		$DCounter 		= 0;
		while($DCounter<=$arrDaysLoop) {
			$NewDate = date("Y-m-d",strtotime("-$DCounter Day"));
			array_push($DaysArray,$NewDate);
			$DCounter++;
		}
		########### DAYS ARRAY ###############
		$arrResult		= array();
		$arrDaysLable	= array();
		$WHERE_COND 	= "";
		$MRFID 			= array();
		if(!empty($MRF_ID)) {
			$MRFID 	= $MRF_ID;
		} else {
			$MRFID 	= $MRF_IDS;
		}
		$WHERE_COND = " AND mrf_id IN (".implode(",",$MRFID).")";
		$EXTRA_COLS = "";
		$SEPARATOR	= ",";
		foreach($DaysArray as $DayID=>$Date)
		{
			$IsToday 	= ($Date == $Today)?1:0;
			$EXTRA_COLS .= $SEPARATOR." getProductCurrentStock(company_product_master.id,'".$Date."','".implode(",",$MRFID)."',".$IsToday.",".PRODUCT_PURCHASE.") AS STOCK_".str_replace("-","_",$Date);
			$arrDaysLable[$DayID] = $Date;
		}
		$SELECT_SQL = "	SELECT company_product_master.id as product_id,
						CONCAT(company_product_master.name,' - ',CPQ.parameter_name) AS PRODUCT_NAME
						$EXTRA_COLS
						FROM company_product_master
						LEFT JOIN company_product_quality_parameter AS CPQ ON CPQ.product_id = company_product_master.id
						WHERE company_product_master.para_status_id = ".PRODUCT_STATUS_ACTIVE."
						AND company_product_master.company_id = ".Auth()->user()->company_id;
		if(!empty($PRODUCT_ID)) {
			$SELECT_SQL .= " AND company_product_master.id IN (".implode(",",$PRODUCT_ID).")";
		}
		$SQL_RESULT 	= \DB::SELECT($SELECT_SQL);
		$arrResult		= array();
		if (!empty($SQL_RESULT))
		{
			foreach($SQL_RESULT AS $SQLROW)
			{
				$StockFound = false;
				foreach($DaysArray as $key=>$Date)
				{
					$COL_NAME = "STOCK_".str_replace("-","_",$Date);
					if (isset($SQLROW->$COL_NAME) && !empty(floatval($SQLROW->$COL_NAME))) {
						$StockFound = true;
					}
					$date_wise_data[$key]["stock"]	= (isset($SQLROW->$COL_NAME)?$SQLROW->$COL_NAME:0);
					$date_wise_data[$key]["date"]	= $Date;
				}
				if ($StockFound) {
					$arrResult[]	= array("product_id"	=> $SQLROW->product_id,
											"name" 			=> $SQLROW->PRODUCT_NAME,
											"date_wise_data"=> $date_wise_data);
				}
			}
		}
		$toArray['result'] 	= $arrResult;
		$toArray['lable'] 	= $arrDaysLable;
		return $toArray;
	}

	/*
	Use 	: Get Current Stock of Purchase Product
	Author 	: Axay Shah
	Date 	: 07 July,2020
	*/
	public static function GetPurchaseProductCurrentStock($date,$productID = 0,$MRF_ID = 0)
	{
		$TOTAL_CURRENT_STOCK 	= 0;
		$TOTAL_INWARD_STOCK 	= 0;
		$TOTAL_OUTWARD_STOCK 	= 0;
		$TOTAL_OPENING_STOCK 	= 0;
		$date 					= (!empty($date)) ? date("Y-m-d",strtotime($date)) : date("Y-m-d");
		$OPENING_STOCK_QTY 		= self::where("stock_date",$date)->where("product_type",PRODUCT_PURCHASE);
		$INWARD_STOCK 			= ProductInwardLadger::where("inward_date",$date)->where("product_type",PRODUCT_PURCHASE);
		$OUTWARD_STOCK 			= OutWardLadger::where("outward_date",$date)->where("sales_product_id","0");
		if($MRF_ID > 0) {
			$OPENING_STOCK_QTY->where("mrf_id",$MRF_ID);
			$INWARD_STOCK->where("mrf_id",$MRF_ID);
			$OUTWARD_STOCK->where("mrf_id",$MRF_ID);
		}
		if($productID > 0) {
			$OPENING_STOCK_QTY->where("product_id",$productID);
			$INWARD_STOCK->where("product_id",$productID);
			$OUTWARD_STOCK->where("product_id",$productID);
		}
		$TOTAL_OPENING_STOCK 	= $OPENING_STOCK_QTY->sum("opening_stock");
		$TOTAL_INWARD_STOCK 	= $INWARD_STOCK->sum("quantity");
		$TOTAL_OUTWARD_STOCK 	= $OUTWARD_STOCK->sum("quantity");
		$TOTAL_CURRENT_STOCK 	= (floatval($TOTAL_OPENING_STOCK) + floatval($TOTAL_INWARD_STOCK)) - $TOTAL_OUTWARD_STOCK ;
		return $TOTAL_CURRENT_STOCK;
	}

	/*
	Use 	: Stock Adjustment at the end of month Api
	Author 	: Axay Shah
	Date 	: 07 August,2020
	*/

	public static function GetProuctStockById($PRODUCT_ID=0,$PRODUCT_TYPE=PRODUCT_PURCHASE,$MRF_ID=0,$DATE=""){
		$Inward 	= 	ProductInwardLadger::where("product_type",$PRODUCT_TYPE);
		if(!empty($DATE)){
			$Inward->where("inward_date",$DATE);
		}
		if(!empty($PRODUCT_ID)){
			$Inward->where("product_id",$PRODUCT_ID);
		}
		if(!empty($MRF_ID)){
			$Inward->where("mrf_id",$MRF_ID);
		}
		$Inward->sum("quantity");
	}


	/*
	Use 	: Stock Adjustment at the end of month Api
	Author 	: Axay Shah
	Date 	: 07 August,2020
	*/
	public static function StockAdjustment($request)
	{
		$QTY 			= (isset($request->qty) 		&& !empty($request->qty)) 	? $request->qty 	: 0;
		$MRF_ID 		= (isset($request->mrf_id) 		&& !empty($request->mrf_id))? $request->mrf_id 	: Auth()->user()->mrf_user_id;
		$DATE 			= (isset($request->date) 		&& !empty($request->date)) 	? date("Y-m-d",strtotime($request->date)) : "";
		$PRODUCT_ID 	= (isset($request->product_id) 	&& !empty($request->product_id)) ? $request->product_id : 0;
		$REMARK 		= "From stock adjustment";
		$USERID 		= Auth()->user()->adminuserid;
		$COMPANY_ID 	= Auth()->user()->company_id;
		$QUANTITY 		= 0;
		$CURRENT_STOCK 	= self::GetPurchaseProductCurrentStock($DATE,$PRODUCT_ID,$MRF_ID);
		$INERT_INWARD 	= array();
		$INWARD 		= array();
		$TODAY 			= date('Y-m-d');
		/**
			LOGIC : IF PHYSICAL STOCK IS GRETER THEN CURRENT STOCK THEN ADD INWARD IN THAT PURCHASE PRODUCT AND
			IF PHYSICAL STOCK IS LESS THEN CURRENT STOCK THEN DO OUTWARD FROM THAT PURCHASE PRODUCT STOCK AND
			ADD INWARD IN SALES INERT PRODUCT - 13 AUGUST 2020
		 */
		$ENDDATE 						= date('Y-m-d',strtotime("+1 days"));
		$BEGIN 							= new DateTime($DATE);
		$END 							= new DateTime($ENDDATE);
		$PRIVIOUS_DATE_CLOSING_STOCK 	=  0;
		$DATE_RANGE 					= new DatePeriod($BEGIN, new DateInterval('P1D'), $END);

		/** ADD ADDITIONAL RECORD IN REPORT TABLE FOR SENDING EMAIL TO HOD */
		MonthlyStockAdjustment::SaveMonthlyStockAdjustmentForProduct($PRODUCT_ID,$QTY,$CURRENT_STOCK,$TODAY,$MRF_ID,$USERID);
		/** ADD ADDITIONAL RECORD IN REPORT TABLE FOR SENDING EMAIL TO HOD */

		foreach($DATE_RANGE as $DATE_VAL) {
			$INWARD_STOCK_QTY 				=  0;
			$OUTWARD_STOCK_QTY 				=  0;
			$OPENING_STOCK_QTY 				=  0;
			$CLOSING_STOCK_QTY 				=  0;
			$PRIVIOUS_CLOSING_STOCK_QTY 	=  0;
			$CURRENT_PROCESS_DATE 			=  $DATE_VAL->format("Y-m-d");
			if($QTY > $CURRENT_STOCK)
			{
				$INWARD_QTY 					=  0;
				$INWARD_QTY 					= _FormatNumberV2($QTY - $CURRENT_STOCK);
				$INWARD 						= array();
				$INWARD['quantity']				= $INWARD_QTY;
				$INWARD['product_id'] 			= $PRODUCT_ID;
				$INWARD['type']					= TYPE_PURCHASE;
				$INWARD['product_type']			= PRODUCT_PURCHASE;
				$INWARD['mrf_id']				= $MRF_ID;
				$INWARD['company_id']			= $COMPANY_ID;
				$INWARD['inward_date']			= $CURRENT_PROCESS_DATE;
				$INWARD['remarks']				= $REMARK;
				$INWARD['created_by']			= $USERID;
				$INWARD['updated_by']			= $USERID;
				$GET_DATA = self::GetProductStockData($PRODUCT_ID,PRODUCT_PURCHASE,$MRF_ID,$CURRENT_PROCESS_DATE,$COMPANY_ID);
				if($CURRENT_PROCESS_DATE == $DATE) {
					ProductInwardLadger::AutoAddInward($INWARD);
					$OPENING_STOCK_QTY 				= $GET_DATA['opening_stock'];
					$OUTWARD_STOCK_QTY 				= $GET_DATA['outward'];
					$INWARD_STOCK_QTY 				= $GET_DATA['inward'] + $INWARD_QTY;
					// $CLOSING_STOCK_QTY 				= ($INWARD_STOCK_QTY + $OPENING_STOCK_QTY) - $OUTWARD_STOCK_QTY;
					$CLOSING_STOCK_QTY 				= $QTY;
					$PRIVIOUS_DATE_CLOSING_STOCK 	= $CLOSING_STOCK_QTY;
				} else {
					$OPENING_STOCK_QTY 				= $PRIVIOUS_DATE_CLOSING_STOCK;
					$OUTWARD_STOCK_QTY 				= $GET_DATA['outward'];
					$INWARD_STOCK_QTY 				= $GET_DATA['inward'];
					$CLOSING_STOCK_QTY 				= ($INWARD_STOCK_QTY + $OPENING_STOCK_QTY) - $OUTWARD_STOCK_QTY;
					$PRIVIOUS_DATE_CLOSING_STOCK 	= $CLOSING_STOCK_QTY;
				}
				self::createOrUpdate($PRODUCT_ID,PRODUCT_PURCHASE,$MRF_ID,$CURRENT_PROCESS_DATE,$COMPANY_ID,$OPENING_STOCK_QTY,$CLOSING_STOCK_QTY,$INWARD_STOCK_QTY,$OUTWARD_STOCK_QTY,TYPE_PURCHASE);
			} elseif($QTY < $CURRENT_STOCK) {
				$INWARD 						= array();
				$QUANTITY 						= _FormatNumberV2($CURRENT_STOCK - $QTY);
				$INWARD 						= array();
				$INWARD['product_id'] 			= $PRODUCT_ID;
				$INWARD['type']					= TYPE_PURCHASE;
				$INWARD['product_type']			= PRODUCT_PURCHASE;
				$INWARD['mrf_id']				= $MRF_ID;
				$INWARD['company_id']			= $COMPANY_ID;
				$INWARD['outward_date']			= $DATE;
				$INWARD['remarks']				= $REMARK;
				$INWARD['created_by']			= $USERID;
				$INWARD['updated_by']			= $USERID;
				$INWARD['quantity']				= $QUANTITY;
				$GET_DATA = self::GetProductStockData($PRODUCT_ID,PRODUCT_PURCHASE,$MRF_ID,$CURRENT_PROCESS_DATE,$COMPANY_ID);
				if($CURRENT_PROCESS_DATE == $DATE) {
					OutwardLadger::AutoAddOutward($INWARD);
					$OPENING_STOCK_QTY 				= $GET_DATA['opening_stock'];
					$OUTWARD_STOCK_QTY 				= $GET_DATA['outward'] + $QUANTITY;
					$INWARD_STOCK_QTY 				= $GET_DATA['inward'];
					// $CLOSING_STOCK_QTY 				= ($INWARD_STOCK_QTY + $OPENING_STOCK_QTY) - $OUTWARD_STOCK_QTY;
					$CLOSING_STOCK_QTY 				= $QTY;
					$PRIVIOUS_DATE_CLOSING_STOCK 	= $CLOSING_STOCK_QTY;
				} else {
					$PRIVIOUS_DATE 					= date('Y-m-d', strtotime($CURRENT_PROCESS_DATE .' -1 day'));
					$PRIVIOUS_DATE_STOCK 			= self::GetProductStockData($PRODUCT_ID,PRODUCT_PURCHASE,$MRF_ID,$PRIVIOUS_DATE,$COMPANY_ID);
					$PRIVIOUS_CLOSING_STOCK_QTY 	= (isset($PRIVIOUS_DATE_STOCK['closing_stock']) && !empty($PRIVIOUS_DATE_STOCK['closing_stock']) ? $PRIVIOUS_DATE_STOCK['closing_stock'] : 0);
					$OPENING_STOCK_QTY 				= $PRIVIOUS_CLOSING_STOCK_QTY;
					$OUTWARD_STOCK_QTY 				= $GET_DATA['outward'];
					$INWARD_STOCK_QTY 				= $GET_DATA['inward'];
					$CLOSING_STOCK_QTY 				= ($INWARD_STOCK_QTY + $OPENING_STOCK_QTY) - $OUTWARD_STOCK_QTY;
					$PRIVIOUS_DATE_CLOSING_STOCK 	= $CLOSING_STOCK_QTY;
				}
				self::createOrUpdate($PRODUCT_ID,PRODUCT_PURCHASE,$MRF_ID,$CURRENT_PROCESS_DATE,$COMPANY_ID,$OPENING_STOCK_QTY,$CLOSING_STOCK_QTY,$INWARD_STOCK_QTY,$OUTWARD_STOCK_QTY,TYPE_PURCHASE);
				$INERT_INWARD['product_id'] 			= PRODUCT_INERT;
				$INERT_INWARD['quantity']				= $QUANTITY;
				$INERT_INWARD['type']					= TYPE_SALES;
				$INERT_INWARD['product_type']			= PRODUCT_SALES;
				$INERT_INWARD['mrf_id']					= $MRF_ID;
				$INERT_INWARD['company_id']				= $COMPANY_ID;
				$INERT_INWARD['inward_date']			= $DATE;
				$INERT_INWARD['remarks']				= $REMARK;
				$INERT_INWARD['created_by']				= $USERID;
				$INERT_INWARD['updated_by']				= $USERID;
				$GET_DATA = self::GetProductStockData(PRODUCT_INERT,PRODUCT_SALES,$MRF_ID,$CURRENT_PROCESS_DATE,$COMPANY_ID);
				if($CURRENT_PROCESS_DATE == $DATE) {
					ProductInwardLadger::AutoAddInward($INERT_INWARD);
					$OPENING_STOCK_QTY 					= $GET_DATA['opening_stock'];
					$OUTWARD_STOCK_QTY 					= $GET_DATA['outward'];
					$INWARD_STOCK_QTY 					= $GET_DATA['inward'] + $QUANTITY;
					$CLOSING_STOCK_QTY 					= ($INWARD_STOCK_QTY + $OPENING_STOCK_QTY) - $OUTWARD_STOCK_QTY;
					$PRIVIOUS_DATE_CLOSING_STOCK 		= $CLOSING_STOCK_QTY;
				} else {
					$PRIVIOUS_DATE_SALES 				= date('Y-m-d', strtotime($CURRENT_PROCESS_DATE .' -1 day'));
					$PRIVIOUS_DATE_INERT_STOCK 			= self::GetProductStockData(PRODUCT_INERT,PRODUCT_SALES,$MRF_ID,$PRIVIOUS_DATE,$COMPANY_ID);
					$PRIVIOUS_INERT_CLOSING_STOCK_QTY 	= (isset($PRIVIOUS_DATE_INERT_STOCK['closing_stock']) && !empty($PRIVIOUS_DATE_INERT_STOCK['closing_stock']) ? $PRIVIOUS_DATE_INERT_STOCK['closing_stock'] : 0);
					$OPENING_STOCK_QTY 					= $PRIVIOUS_INERT_CLOSING_STOCK_QTY;
					$OUTWARD_STOCK_QTY 					= $GET_DATA['outward'];
					$INWARD_STOCK_QTY 					= $GET_DATA['inward'];
					$CLOSING_STOCK_QTY 					= ($INWARD_STOCK_QTY + $OPENING_STOCK_QTY) - $OUTWARD_STOCK_QTY;
					$PRIVIOUS_DATE_CLOSING_STOCK 		= $CLOSING_STOCK_QTY;
				}
				###### INERT FLAG PRODUCT STOCK BECOME ZERO ON EVERY FIRST DATE OF MONTH #######
				$INERT_FLAG 			= WmProductMaster::where("id",PRODUCT_INERT)->value("inert_flag");
				$MONTH_FIRST_DATE 		= date("Y-m")."-01";
				$MONTH_FIRST_DATE_FLAG 	= (strtotime($MONTH_FIRST_DATE) == strtotime($CURRENT_PROCESS_DATE)) ? 1 : 0;
				$OPENING_STOCK_QTY 		= ($INERT_FLAG == 1 && $MONTH_FIRST_DATE_FLAG == 1) ? 0 : _FormatNumberV2($OPENING_STOCK_QTY);
				###### INERT FLAG PRODUCT STOCK BECOME ZERO ON EVERY FIRST DATE OF MONTH #######
				self::createOrUpdate(PRODUCT_INERT,PRODUCT_SALES,$MRF_ID,$CURRENT_PROCESS_DATE,$COMPANY_ID,$OPENING_STOCK_QTY,$CLOSING_STOCK_QTY,$INWARD_STOCK_QTY,$OUTWARD_STOCK_QTY,TYPE_SALES);
			}
		}
	}

	/*
	Use 	: get product stock data
	Author 	: Axay Shah
	Date 	: 17 August,2020
	*/
	public static function GetProductStockData($PRODUCT_ID=0,$PRODUCT_TYPE,$MRF_ID=0,$STOCK_DATE,$COMPANY_ID=0){
		$OPENING_STOCK_QTY 	= 0;
		$CLOSING_STOCK 		= 0;
		$INWARD 			= 0;
		$OUTWARD 			= 0;

		$EXITS = self::where("product_id",$PRODUCT_ID)
				->where("product_type",$PRODUCT_TYPE)
				->where("company_id",$COMPANY_ID)
				->where("mrf_id",$MRF_ID)
				->where("stock_date",$STOCK_DATE)->first();
		if($EXITS){
			$OPENING_STOCK_QTY 	= $EXITS->opening_stock;
			$CLOSING_STOCK 		= $EXITS->closing_stock;
			$INWARD 			= $EXITS->inward;
			$OUTWARD 			= $EXITS->outward;
		}
		$array['opening_stock'] = $OPENING_STOCK_QTY;
		$array['closing_stock'] = $CLOSING_STOCK;
		$array['inward'] 		= $INWARD;
		$array['outward'] 		= $OUTWARD;
		return $array;
	}
	/*
	Use 	: create or update stock product record
	Author 	: Axay Shah
	Date 	: 17 August,2020
	*/
	public static function createOrUpdate($PRODUCT_ID=0,$PRODUCT_TYPE,$MRF_ID=0,$STOCK_DATE,$COMPANY_ID=0,$OPENING_STOCK_QTY,$CLOSING_STOCK_QTY,$INWARD_STOCK_QTY,$OUTWARD_STOCK_QTY,$TYPE){

		$EXITS = self::where("product_id",$PRODUCT_ID)
				->where("product_type",$PRODUCT_TYPE)
				->where("company_id",$COMPANY_ID)
				->where("mrf_id",$MRF_ID)
				->where("stock_date",$STOCK_DATE)
				->first();
		if(!$EXITS){
			$EXITS = new self();
		}
		$EXITS->product_id 		= $PRODUCT_ID;
		$EXITS->product_type 	= $PRODUCT_TYPE;
		$EXITS->opening_stock 	= $OPENING_STOCK_QTY;
		$EXITS->inward 			= $INWARD_STOCK_QTY;
		$EXITS->outward 		= $OUTWARD_STOCK_QTY;
		$EXITS->closing_stock 	= $CLOSING_STOCK_QTY;
		$EXITS->company_id 		= $COMPANY_ID;
		$EXITS->type 			= $TYPE;
		$EXITS->mrf_id 			= $MRF_ID;
		$EXITS->stock_date 		= $STOCK_DATE;
		$EXITS->created_at 		= date("Y-m-d H:i:s");
		$EXITS->updated_at 		= date("Y-m-d H:i:s");
		$EXITS->save();
	}

	/*
	Use 	: Stock Adjustment for sales Product
	Author 	: Axay Shah
	Date 	: 03 June 2021
	*/
	public static function StockAdjustmentSalesProduct($request){
		$QTY 			= (isset($request->new_stock) 	&& !empty($request->new_stock)) 	? $request->new_stock 	: 0;
		$MRF_ID 		= (isset($request->mrf_id) 		&& !empty($request->mrf_id))? $request->mrf_id 	: Auth()->user()->mrf_user_id;
		$DATE 			= (isset($request->date) 		&& !empty($request->date)) 	? date("Y-m-d",strtotime($request->date)) : "";
		$PRODUCT_ID 	= (isset($request->product_id) 	&& !empty($request->product_id)) ? $request->product_id : 0;
		$REMARK 		= "From sales product stock adjustment";
		$USERID 		= Auth()->user()->adminuserid;
		$COMPANY_ID 	= Auth()->user()->company_id;
		$QUANTITY 		= 0;
		$CURRENT_STOCK 	= self::GetSalesProductStock($MRF_ID,$PRODUCT_ID,$DATE);
		$INERT_INWARD 	= array();
		$INWARD 		= array();
		$TODAY 			= date('Y-m-d');
		$ENDDATE 						= date('Y-m-d',strtotime("+1 days"));
		$BEGIN 							= new DateTime($DATE);
		$END 							= new DateTime($ENDDATE);
		$PRIVIOUS_DATE_CLOSING_STOCK 	=  0;
		$DATE_RANGE 					= new DatePeriod($BEGIN, new DateInterval('P1D'), $END);

		foreach($DATE_RANGE as $DATE_VAL){
			$INWARD_STOCK_QTY 				=  0;
			$OUTWARD_STOCK_QTY 				=  0;
			$OPENING_STOCK_QTY 				=  0;
			$CLOSING_STOCK_QTY 				=  0;
			$PRIVIOUS_CLOSING_STOCK_QTY 	=  0;
			$CURRENT_PROCESS_DATE 			=  $DATE_VAL->format("Y-m-d");
			if($QTY < $CURRENT_STOCK){
				$INWARD 						= array();
				$QUANTITY 						= _FormatNumberV2($CURRENT_STOCK - $QTY);
				$INWARD 						= array();
				$INWARD['sales_product_id'] 	= $PRODUCT_ID;
				$INWARD['type']					= TYPE_SALES;
				$INWARD['product_type']			= PRODUCT_SALES;
				$INWARD['mrf_id']				= $MRF_ID;
				$INWARD['company_id']			= $COMPANY_ID;
				$INWARD['outward_date']			= $DATE;
				$INWARD['remarks']				= $REMARK;
				$INWARD['created_by']			= $USERID;
				$INWARD['updated_by']			= $USERID;
				$INWARD['quantity']				= $QUANTITY;

				$GET_DATA = self::GetProductStockData($PRODUCT_ID,PRODUCT_SALES,$MRF_ID,$CURRENT_PROCESS_DATE,$COMPANY_ID);

				if($CURRENT_PROCESS_DATE == $DATE){
					OutwardLadger::AutoAddOutward($INWARD);
					$OPENING_STOCK_QTY 				= $GET_DATA['opening_stock'];
					$OUTWARD_STOCK_QTY 				= $GET_DATA['outward'] + $QUANTITY;
					$INWARD_STOCK_QTY 				= $GET_DATA['inward'];
					// $CLOSING_STOCK_QTY 				= ($INWARD_STOCK_QTY + $OPENING_STOCK_QTY) - $OUTWARD_STOCK_QTY;
					$CLOSING_STOCK_QTY 				= $QTY;
					$PRIVIOUS_DATE_CLOSING_STOCK 	= $CLOSING_STOCK_QTY;

				}else{
					$PRIVIOUS_DATE 					= date('Y-m-d', strtotime($CURRENT_PROCESS_DATE .' -1 day'));

					$PRIVIOUS_DATE_STOCK 			= self::GetProductStockData($PRODUCT_ID,PRODUCT_SALES,$MRF_ID,$PRIVIOUS_DATE,$COMPANY_ID);

					$PRIVIOUS_CLOSING_STOCK_QTY 	= (isset($PRIVIOUS_DATE_STOCK['closing_stock']) && !empty($PRIVIOUS_DATE_STOCK['closing_stock']) ? $PRIVIOUS_DATE_STOCK['closing_stock'] : 0);
					$OPENING_STOCK_QTY 				= $PRIVIOUS_CLOSING_STOCK_QTY;
					$OUTWARD_STOCK_QTY 				= $GET_DATA['outward'];
					$INWARD_STOCK_QTY 				= $GET_DATA['inward'];
					$CLOSING_STOCK_QTY 				= ($INWARD_STOCK_QTY + $OPENING_STOCK_QTY) - $OUTWARD_STOCK_QTY;
					$PRIVIOUS_DATE_CLOSING_STOCK 	= $CLOSING_STOCK_QTY;

				}
				self::createOrUpdate($PRODUCT_ID,PRODUCT_SALES,$MRF_ID,$CURRENT_PROCESS_DATE,$COMPANY_ID,$OPENING_STOCK_QTY,$CLOSING_STOCK_QTY,$INWARD_STOCK_QTY,$OUTWARD_STOCK_QTY,TYPE_SALES);

				$INERT_INWARD['sales_product_id'] 		= PRODUCT_INERT;
				$INERT_INWARD['quantity']				= $QUANTITY;
				$INERT_INWARD['type']					= TYPE_SALES;
				$INERT_INWARD['product_type']			= PRODUCT_SALES;
				$INERT_INWARD['mrf_id']					= $MRF_ID;
				$INERT_INWARD['company_id']				= $COMPANY_ID;
				$INERT_INWARD['inward_date']			= $DATE;
				$INERT_INWARD['remarks']				= $REMARK;
				$INERT_INWARD['created_by']				= $USERID;
				$INERT_INWARD['updated_by']				= $USERID;

				$GET_DATA = self::GetProductStockData(PRODUCT_INERT,PRODUCT_SALES,$MRF_ID,$CURRENT_PROCESS_DATE,$COMPANY_ID);
				if($CURRENT_PROCESS_DATE == $DATE){

					ProductInwardLadger::AutoAddInward($INERT_INWARD);
					$OPENING_STOCK_QTY 					= $GET_DATA['opening_stock'];
					$OUTWARD_STOCK_QTY 					= $GET_DATA['outward'];
					$INWARD_STOCK_QTY 					= $GET_DATA['inward'] + $QUANTITY;
					$CLOSING_STOCK_QTY 					= ($INWARD_STOCK_QTY + $OPENING_STOCK_QTY) - $OUTWARD_STOCK_QTY;
					$PRIVIOUS_DATE_CLOSING_STOCK 		= $CLOSING_STOCK_QTY;

				}else{

					$PRIVIOUS_DATE_SALES 				= date('Y-m-d', strtotime($CURRENT_PROCESS_DATE .' -1 day'));

					$PRIVIOUS_DATE_INERT_STOCK 			= self::GetProductStockData(PRODUCT_INERT,PRODUCT_SALES,$MRF_ID,$PRIVIOUS_DATE,$COMPANY_ID);
					$PRIVIOUS_INERT_CLOSING_STOCK_QTY 	= (isset($PRIVIOUS_DATE_INERT_STOCK['closing_stock']) && !empty($PRIVIOUS_DATE_INERT_STOCK['closing_stock']) ? $PRIVIOUS_DATE_INERT_STOCK['closing_stock'] : 0);

					$OPENING_STOCK_QTY 					= $PRIVIOUS_INERT_CLOSING_STOCK_QTY;
					$OUTWARD_STOCK_QTY 					= $GET_DATA['outward'];
					$INWARD_STOCK_QTY 					= $GET_DATA['inward'];
					$CLOSING_STOCK_QTY 					= ($INWARD_STOCK_QTY + $OPENING_STOCK_QTY) - $OUTWARD_STOCK_QTY;
					$PRIVIOUS_DATE_CLOSING_STOCK 		= $CLOSING_STOCK_QTY;
				}

				###### INERT FLAG PRODUCT STOCK BECOME ZERO ON EVERY FIRST DATE OF MONTH #######
				$INERT_FLAG 			= WmProductMaster::where("id",PRODUCT_INERT)->value("inert_flag");
				$MONTH_FIRST_DATE 		= date("Y-m")."-01";
				$MONTH_FIRST_DATE_FLAG 	= (strtotime($MONTH_FIRST_DATE) == strtotime($CURRENT_PROCESS_DATE)) ? 1 : 0;
				$OPENING_STOCK_QTY 		= ($INERT_FLAG == 1 && $MONTH_FIRST_DATE_FLAG == 1) ? 0 : _FormatNumberV2($OPENING_STOCK_QTY);
				###### INERT FLAG PRODUCT STOCK BECOME ZERO ON EVERY FIRST DATE OF MONTH #######
				self::createOrUpdate(PRODUCT_INERT,PRODUCT_SALES,$MRF_ID,$CURRENT_PROCESS_DATE,$COMPANY_ID,$OPENING_STOCK_QTY,$CLOSING_STOCK_QTY,$INWARD_STOCK_QTY,$OUTWARD_STOCK_QTY,TYPE_SALES);
			}
		}
	}
	/*
	Use 	: GET CURRENT STOCK OF SALES PORDUCT
	Author 	: Axay Shah
	Date 	: 03 June 2021
	*/
	public static function GetSalesProductStock($MRF_ID,$PRODUCT_ID,$DATE){
		$OPENING_STOCK 	= self::where("product_type",PRODUCT_SALES)
						->where("stock_date",$DATE)
						->where("product_id",$PRODUCT_ID)
						->where("mrf_id",$MRF_ID)
						->value("opening_stock");
		$INWARD_STOCK 	= ProductInwardLadger::where("product_type",PRODUCT_SALES)
						->where("inward_date",$DATE)
						->where("product_id",$PRODUCT_ID)
						->where("mrf_id",$MRF_ID)
						->sum("quantity");
		$OUTWARD_STOCK 	= OutwardLadger::where("outward_date",$DATE)
						->where("sales_product_id",$PRODUCT_ID)
						->where("mrf_id",$MRF_ID)
						->sum("quantity");
		$CURRENT_STOCK  = _FormatNumberV2(($OPENING_STOCK + $INWARD_STOCK) - $OUTWARD_STOCK);
		return $CURRENT_STOCK;
	}

	/*
	Use 	: GET CURRENT STOCK OF PURCHASE PORDUCT
	Author 	: Axay Shah
	Date 	: 17 Dec 2021
	*/
	public static function GetPurchaseProductStock($MRF_ID,$PRODUCT_ID,$DATE){
		$OPENING_STOCK 	= self::where("product_type",PRODUCT_PURCHASE)
						->where("stock_date",$DATE)
						->where("product_id",$PRODUCT_ID)
						->where("mrf_id",$MRF_ID)
						->value("opening_stock");
		$INWARD_STOCK 	= ProductInwardLadger::where("product_type",PRODUCT_PURCHASE)
						->where("inward_date",$DATE)
						->where("product_id",$PRODUCT_ID)
						->where("mrf_id",$MRF_ID)
						->sum("quantity");
		$OUTWARD_STOCK 	= OutwardLadger::where("outward_date",$DATE)
						->where("product_id",$PRODUCT_ID)
						->where("sales_product_id",0)
						->where("mrf_id",$MRF_ID)
						->sum("quantity");
		$CURRENT_STOCK  = _FormatNumberV2(($OPENING_STOCK + $INWARD_STOCK) - $OUTWARD_STOCK);
		return $CURRENT_STOCK;
	}
	/*
	Use 	: Update Daily Stock of every product on daily basis
	Author 	: Axay Shah
	Date 	: 26 Aug,2019
	*/
	public static function UpdateMRFStock($date,$MRF_ID){
		$INWARD 				= 	new ProductInwardLadger();
		$OUTWARD 				= 	new OutwardLadger();
		$DEPARTMENT_TBL 		= 	new WmDepartment();
		$PURCHASE_TBL 			= 	new CompanyProductMaster();
		$PURCHASE_PRO 			= 	$PURCHASE_TBL->getTable();
		$SALES_TBL 				= 	new WmProductMaster();
		$SALES_PRO 				= 	$SALES_TBL->getTable();
		$DEPT 					=   $DEPARTMENT_TBL->getTable();
		$STOCK_DATE  			= 	(!empty($date)) ? date("Y-m-d",strtotime($date)) : date("Y-m-d",strtotime("-1 days"));
		$TotalInword 			=  	0;
		$TotalOutword 			=  	0;
		$OpeningStock 			= 	0;
		$ClosingStock 			= 	0;
		$TotalStockWithInword 	= 	0;
		$TodayInward 			= 	0;
		$TodayOutward 			= 	0;
		$arrCompany    			= 	CompanyMaster::select('company_id')
									->where('status','Active')
									->where('company_id','1')
									->get();
		if (!empty($arrCompany))
		{
			foreach($arrCompany as $Company)
			{
				$COMPANY_ID = $Company->company_id;
				$Department = WmDepartment::select('id','location_id','base_location_id')
							->where('company_id',$COMPANY_ID)
							->where('id',$MRF_ID)
							->where('status','1')->get()->toArray();
				// prd($Department);
				if (!empty($Department))
				{
					foreach($Department as $RAW)
					{
						$MRF_ID 			= $RAW['id'];

						echo "\r\n-- ".$MRF_ID." StartTime::".date("Y-m-d H:i:s")."--\r\n";

						$PURCHASE_PRODUCT 	= CompanyProductMaster::select("id as product_id",
												\DB::raw("CASE WHEN 1=1 THEN (
													SELECT SUM(inward_ledger.quantity)
													FROM inward_ledger
													WHERE inward_ledger.mrf_id = ".$MRF_ID."
													AND inward_ledger.product_type = ".PRODUCT_PURCHASE."
													AND inward_ledger.product_id = company_product_master.id
													AND inward_ledger.inward_date = '".$STOCK_DATE."'
												) END AS TOTAL_PURCHASE_INWARD"),
												\DB::raw("CASE WHEN 1=1 THEN (
													SELECT SUM(outward_ledger.quantity)
													FROM outward_ledger
													WHERE outward_ledger.mrf_id = ".$MRF_ID."
													AND outward_ledger.product_id = company_product_master.id
													AND outward_ledger.sales_product_id = 0
													AND outward_ledger.outward_date = '".$STOCK_DATE."'
												) END AS TOTAL_PURCHASE_OUTWARD"),

												\DB::raw("CASE WHEN 1=1 THEN (
													SELECT SUM(stock_ladger.opening_stock)
													FROM stock_ladger
													WHERE stock_ladger.mrf_id = ".$MRF_ID."
													AND stock_ladger.product_type = ".PRODUCT_PURCHASE."
													AND stock_ladger.product_id = company_product_master.id
													AND stock_ladger.stock_date = '".$STOCK_DATE."'
												) END AS TOTAL_PURCHASE_OPENING_STOCK"))
												->where("para_status_id",6001)
												->where("company_id",$COMPANY_ID)
												->get()
												->toArray();
						if(!empty($PURCHASE_PRODUCT))
						{
							foreach($PURCHASE_PRODUCT AS $PRO)
							{
								$PRODUCT_ID 						= 	$PRO['product_id'];
								$TOTAL_PURCHASE_INWARD 				=  (!empty($PRO['TOTAL_PURCHASE_INWARD'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_INWARD']) : 0;
								$TOTAL_PURCHASE_OUTWARD 			=  (!empty($PRO['TOTAL_PURCHASE_OUTWARD'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_OUTWARD']) : 0;
								$TOTAL_PURCHASE_OPENING_STOCK 		=  (!empty($PRO['TOTAL_PURCHASE_OPENING_STOCK'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_OPENING_STOCK']) : 0;
								$TOTAL_STOCK_WITH_PURCHASE_INWARD 	=  $TOTAL_PURCHASE_OPENING_STOCK + $TOTAL_PURCHASE_INWARD;
								$CLOSING_PURCHASE_STOCK 			=  $TOTAL_STOCK_WITH_PURCHASE_INWARD - $TOTAL_PURCHASE_OUTWARD;

								######## AVG PRICE CALCULATION FOR PRODUCT WISE 10 MARCH 2021 ############
								$PREV_DATE 			= date('Y-m-d', strtotime($STOCK_DATE .' -1 day'));
								$INWARD_AVG_PRICE 	= ProductInwardLadger::where("product_id",$PRODUCT_ID)
													->where("mrf_id",$MRF_ID)
													->where("product_type",PRODUCT_PURCHASE)
													->where("inward_date",$STOCK_DATE)
													->avg("avg_price");

								$STOCK_AVG_PRICE  	= self::where("mrf_id",$MRF_ID)
													->where("product_id",$PRODUCT_ID)
													->where("product_type",PRODUCT_PURCHASE)
													->where("stock_date",$PREV_DATE)
													->value("avg_price");
								$STOCK_AVG_PRICE 	= (!empty($STOCK_AVG_PRICE)) ? _FormatNumberV2($STOCK_AVG_PRICE) : 0;
								$INWARD_AVG_PRICE 	= (!empty($INWARD_AVG_PRICE)) ? _FormatNumberV2($INWARD_AVG_PRICE) : 0;
								$AVG_PRICE_AMT 		= _FormatNumberV2(($STOCK_AVG_PRICE + $INWARD_AVG_PRICE) / 2);

								######## AVG PRICE CALCULATION FOR PRODUCT WISE ############



								$PRIVIOUS_DATE = array(
									"product_id"	=> $PRODUCT_ID,
									"mrf_id"		=> $MRF_ID,
									"product_type"	=> PRODUCT_PURCHASE,
									"type"			=> "P",
									"opening_stock"	=> (!empty($TOTAL_PURCHASE_OPENING_STOCK)) ? _FormatNumberV2($TOTAL_PURCHASE_OPENING_STOCK) : 0,
									"inward"		=> _FormatNumberV2($TOTAL_PURCHASE_INWARD),
									"outward"		=> _FormatNumberV2($TOTAL_PURCHASE_OUTWARD),
									"closing_stock"	=> _FormatNumberV2($CLOSING_PURCHASE_STOCK),
									"company_id"	=> $COMPANY_ID,
									"stock_date"	=> $STOCK_DATE,
									"created_at"	=> date("Y-m-d H:i:s"),
								);
								$NEXT_DATE 			= date('Y-m-d', strtotime($STOCK_DATE .' +1 day'));
								$CURRENT_DATE = array(
									"product_id"	=> $PRODUCT_ID,
									"mrf_id"		=> $MRF_ID,
									"product_type"	=> PRODUCT_PURCHASE,
									"type"			=> "P",
									"opening_stock"	=> _FormatNumberV2($CLOSING_PURCHASE_STOCK),
									"inward"		=> 0,
									"outward"		=> 0,
									"closing_stock"	=> 0,
									"company_id"	=> $COMPANY_ID,
									"stock_date"	=> $NEXT_DATE,
									"created_at"	=> date("Y-m-d H:i:s"),
									// "avg_price"		=> $AVG_PRICE_AMT,
								);
								$PRIVIOUS_DATE_DATA = 	self::updateOrCreate(['product_id' 	=> $PRODUCT_ID,
															"mrf_id" 			=> $MRF_ID,
															"product_type" 		=> PRODUCT_PURCHASE,
															// "type"				=> "P",
															"stock_date" 		=> $STOCK_DATE
														], $PRIVIOUS_DATE);
								$CURRENT_DATE_DATA 	= 	self::updateOrCreate(['product_id' 	=> $PRODUCT_ID,
															"mrf_id" 			=> $MRF_ID,
															"product_type" 		=> PRODUCT_PURCHASE,
															// "type"				=> "P",
															"stock_date" 		=> $NEXT_DATE
														], $CURRENT_DATE);
							}
						}

						echo "\r\n-- ".$MRF_ID." EndTime::".date("Y-m-d H:i:s")."--\r\n";
					}
				}
			}
		}
	}

	public static function UpdateMRFSalesStock($date,$MRF_ID)
	{
		$INWARD 				= new ProductInwardLadger();
		$OUTWARD 				= new OutwardLadger();
		$DEPARTMENT_TBL 		= new WmDepartment();
		$SALES_PROD_MASTER 		= new WmProductMaster();
		$PURCHASE_PRO 			= $SALES_PROD_MASTER->getTable();
		$SALES_TBL 				= new WmProductMaster();
		$SALES_PRO 				= $SALES_TBL->getTable();
		$DEPT 					= $DEPARTMENT_TBL->getTable();
		$STOCK_DATE  			= (!empty($date)) ? date("Y-m-d",strtotime($date)) : date("Y-m-d",strtotime("-1 days"));
		$TotalInword 			= 0;
		$TotalOutword 			= 0;
		$OpeningStock 			= 0;
		$ClosingStock 			= 0;
		$TotalStockWithInword 	= 0;
		$TodayInward 			= 0;
		$TodayOutward 			= 0;
		$COMPANY_ID 			= 1;

		echo "\r\n-- ".$MRF_ID." StartTime::".date("Y-m-d H:i:s")."--\r\n";

		$PURCHASE_PRODUCT 		= WmProductMaster::select("id as product_id",
														"inert_flag",
														\DB::raw("CASE WHEN 1=1 THEN (
															SELECT SUM(inward_ledger.quantity)
															FROM inward_ledger
															WHERE inward_ledger.mrf_id = ".$MRF_ID."
															AND inward_ledger.product_type = ".PRODUCT_SALES."
															AND inward_ledger.product_id = wm_product_master.id
															AND inward_ledger.inward_date = '".$STOCK_DATE."'
														) END AS TOTAL_PURCHASE_INWARD"),
														\DB::raw("CASE WHEN 1=1 THEN (
															SELECT SUM(outward_ledger.quantity)
															FROM outward_ledger
															WHERE outward_ledger.mrf_id = ".$MRF_ID."
															AND outward_ledger.sales_product_id = wm_product_master.id
															AND outward_ledger.outward_date = '".$STOCK_DATE."'
														) END AS TOTAL_PURCHASE_OUTWARD"),
														\DB::raw("CASE WHEN 1=1 THEN (
															SELECT SUM(stock_ladger.opening_stock)
															FROM stock_ladger
															WHERE stock_ladger.mrf_id = ".$MRF_ID."
															AND stock_ladger.product_type = ".PRODUCT_SALES."
															AND stock_ladger.product_id = wm_product_master.id
															AND stock_ladger.stock_date = '".$STOCK_DATE."'
														) END AS TOTAL_PURCHASE_OPENING_STOCK"))
														->where("status",1)
														->where("company_id",$COMPANY_ID)
														->get()
														->toArray();
		if(!empty($PURCHASE_PRODUCT))
		{
			foreach($PURCHASE_PRODUCT AS $PRO)
			{
				$PRODUCT_ID 						= 	$PRO['product_id'];
				$TOTAL_PURCHASE_INWARD 				=  (!empty($PRO['TOTAL_PURCHASE_INWARD'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_INWARD']) : 0;
				$TOTAL_PURCHASE_OUTWARD 			=  (!empty($PRO['TOTAL_PURCHASE_OUTWARD'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_OUTWARD']) : 0;
				$TOTAL_PURCHASE_OPENING_STOCK 		=  (!empty($PRO['TOTAL_PURCHASE_OPENING_STOCK'])) ? _FormatNumberV2($PRO['TOTAL_PURCHASE_OPENING_STOCK']) : 0;
				$TOTAL_STOCK_WITH_PURCHASE_INWARD 	=  $TOTAL_PURCHASE_OPENING_STOCK + $TOTAL_PURCHASE_INWARD;
				$CLOSING_PURCHASE_STOCK 			=  $TOTAL_STOCK_WITH_PURCHASE_INWARD - $TOTAL_PURCHASE_OUTWARD;

				######## AVG PRICE CALCULATION FOR PRODUCT WISE 10 MARCH 2021 ############
				$PREV_DATE 			= date('Y-m-d', strtotime($STOCK_DATE .' -1 day'));
				$INWARD_AVG_PRICE 	= ProductInwardLadger::where("product_id",$PRODUCT_ID)
									->where("mrf_id",$MRF_ID)
									->where("product_type",PRODUCT_SALES)
									->where("inward_date",$STOCK_DATE)
									->avg("avg_price");

				$STOCK_AVG_PRICE  	= self::where("mrf_id",$MRF_ID)
									->where("product_id",$PRODUCT_ID)
									->where("product_type",PRODUCT_SALES)
									->where("stock_date",$PREV_DATE)
									->value("avg_price");
				$STOCK_AVG_PRICE 	= (!empty($STOCK_AVG_PRICE)) ? _FormatNumberV2($STOCK_AVG_PRICE) : 0;
				$INWARD_AVG_PRICE 	= (!empty($INWARD_AVG_PRICE)) ? _FormatNumberV2($INWARD_AVG_PRICE) : 0;
				$AVG_PRICE_AMT 		= _FormatNumberV2(($STOCK_AVG_PRICE + $INWARD_AVG_PRICE) / 2);

				######## AVG PRICE CALCULATION FOR PRODUCT WISE ############

				$PRIVIOUS_DATE = array(
					"product_id"	=> $PRODUCT_ID,
					"mrf_id"		=> $MRF_ID,
					"product_type"	=> PRODUCT_SALES,
					"type"			=> "S",
					"opening_stock"	=> (!empty($TOTAL_PURCHASE_OPENING_STOCK)) ? _FormatNumberV2($TOTAL_PURCHASE_OPENING_STOCK) : 0,
					"inward"		=> _FormatNumberV2($TOTAL_PURCHASE_INWARD),
					"outward"		=> _FormatNumberV2($TOTAL_PURCHASE_OUTWARD),
					"closing_stock"	=> _FormatNumberV2($CLOSING_PURCHASE_STOCK),
					"company_id"	=> $COMPANY_ID,
					"stock_date"	=> $STOCK_DATE,
					"created_at"	=> date("Y-m-d H:i:s"),
				);
				###### INERT FLAG PRODUCT STOCK BECOME ZERO ON EVERY FIRST DATE OF MONTH #######
				$INERT_FLAG 			= $PRO['inert_flag'];
				$MONTH_FIRST_DATE 		= date("Y-m")."-01";
				$MONTH_FIRST_DATE_FLAG 	= (strtotime(date("Y-m-d")) == strtotime($MONTH_FIRST_DATE)) ? 1 : 0;
				$CLOSING_PURCHASE_STOCK = ($INERT_FLAG == 1 && $MONTH_FIRST_DATE_FLAG == 1) ? 0 : _FormatNumberV2($CLOSING_PURCHASE_STOCK);
				###### INERT FLAG PRODUCT STOCK BECOME ZERO ON EVERY FIRST DATE OF MONTH #######
				$NEXT_DATE 			= date('Y-m-d', strtotime($STOCK_DATE .' +1 day'));
				$CURRENT_DATE = array(
					"product_id"	=> $PRODUCT_ID,
					"mrf_id"		=> $MRF_ID,
					"product_type"	=> PRODUCT_SALES,
					"type"			=> "S",
					"opening_stock"	=> _FormatNumberV2($CLOSING_PURCHASE_STOCK),
					"inward"		=> 0,
					"outward"		=> 0,
					"closing_stock"	=> 0,
					"company_id"	=> $COMPANY_ID,
					"stock_date"	=> $NEXT_DATE,
					"created_at"	=> date("Y-m-d H:i:s"),
					// "avg_price"		=> $AVG_PRICE_AMT,
				);
				$PRIVIOUS_DATE_DATA = 	self::updateOrCreate(['product_id' 	=> $PRODUCT_ID,
											"mrf_id" 			=> $MRF_ID,
											"product_type" 		=> PRODUCT_SALES,
											// "type"				=> "S",
											"stock_date" 		=> $STOCK_DATE
										], $PRIVIOUS_DATE);
				$CURRENT_DATE_DATA 	= 	self::updateOrCreate(['product_id' 	=> $PRODUCT_ID,
											"mrf_id" 			=> $MRF_ID,
											"product_type" 		=> PRODUCT_SALES,
											// "type"				=> "S",
											"stock_date" 		=> $NEXT_DATE
										], $CURRENT_DATE);
			}
		}
		echo "\r\n-- ".$MRF_ID." EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
	/*
	Use 	: Stock Summary report for Account Team
	Author 	: Axay Shah
	Date 	: 18 Aug,2021
	*/
	public static function StockSummaryReport($request)
	{
		$res 							= array();
		$YEAR 							= (isset($request->year) && !empty($request->year)) ? $request->year : date("Y");
		$BASE_LOCATION 					= (isset($request->base_location_id) && !empty($request->base_location_id)) ? $request->base_location_id : array();
		$MRF_ID 						= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : array();
		$MONTH 							= (isset($request->month) && !empty($request->month)) ? $request->month : date("m");
		$LAST_DATE_OF_CURRENT_MONTH 	= date($YEAR."-".$MONTH."-t");
		$FIRST_DATE_OF_CURRENT_MONTH 	= $YEAR."-".$MONTH."-01";
		$LAST_DATE_OF_CURRENT_MONTH 	= date("Y-m-t", strtotime($FIRST_DATE_OF_CURRENT_MONTH));
		$LAST_DATE_OF_PRIVIOUS_MONTH 	= date("Y-m-t", strtotime ( '-1 month' , strtotime ( $FIRST_DATE_OF_CURRENT_MONTH ) ));
		$PREV_CLOSING_SUM 				= 0;
		$TOTAL_INWARD_SUM 				= 0;
		$TOTAL_OUTWARD_SUM 				= 0;
		$CURR_CLOSING_SUM 				= 0;
		if(!empty($BASE_LOCATION)) {
			$BASE_LOCATION 	= ConvertInArray($BASE_LOCATION);
		}
		$Departments = WmDepartment::GetDepartmentByBaseLocationId($BASE_LOCATION);
		if(!empty($Departments)) {
			$PREV_CLOSING_SUM 	= self::where("stock_date",$FIRST_DATE_OF_CURRENT_MONTH)->whereIn("mrf_id",$Departments)->sum("opening_stock");
			$TOTAL_INWARD_SUM 	= ProductInwardLadger::whereBetween("inward_date",[$FIRST_DATE_OF_CURRENT_MONTH,$LAST_DATE_OF_CURRENT_MONTH])
								->whereIn("mrf_id",$Departments)->sum("quantity");
			$TOTAL_OUTWARD_SUM 	= OutwardLadger::whereBetween("outward_date",[$FIRST_DATE_OF_CURRENT_MONTH,$LAST_DATE_OF_CURRENT_MONTH])
								->whereIn("mrf_id",$Departments)->sum("quantity");
			$CURR_CLOSING_SUM 	= self::where("stock_date",$LAST_DATE_OF_CURRENT_MONTH)->whereIn("mrf_id",$Departments)->sum("closing_stock");
		}
		$res["prev_month_closing_stock"] 	= ($PREV_CLOSING_SUM > 0) ? _FormatNumberV2($PREV_CLOSING_SUM) : "0.00";
		$res["current_month_total_inward"] 	= ($TOTAL_INWARD_SUM > 0) ? _FormatNumberV2($TOTAL_INWARD_SUM) : "0.00";
		$res["current_month_total_outward"] = ($TOTAL_OUTWARD_SUM > 0) ? _FormatNumberV2($TOTAL_OUTWARD_SUM) : "0.00";
		$res["current_month_closing_sum"] 	= ($CURR_CLOSING_SUM > 0) ? _FormatNumberV2($CURR_CLOSING_SUM) : "0.00";
		return $res;
	}

	/*
	Use 	: update stock avg price for specific date
	Author 	: Axay Shah
	Date 	: 08 jan,2021
	*/
	public static function UpdateProductStockAvgPrice($PRODUCT_ID,$PRODUCT_TYPE,$MRF_ID,$DATE,$AVG_PRICE,$NEW_COGS_AVG_PRICE=0)
	{
		$TYPE = ($PRODUCT_TYPE == PRODUCT_PURCHASE)?"P":"S";
		if($PRODUCT_TYPE == PRODUCT_PURCHASE && in_array($PRODUCT_ID,PUCHASE_PRODUCT_STOCK_AVG_ZERO)) {
			$AVG_PRICE = 0;
		}
		if($PRODUCT_TYPE == PRODUCT_SALES && in_array($PRODUCT_ID,SALES_PRODUCT_STOCK_AVG_ZERO)) {
			$AVG_PRICE = 0;
		}
		$data = self::updateOrCreate([	'product_id' 		=> $PRODUCT_ID,
										"mrf_id" 			=> $MRF_ID,
										"product_type" 		=> $PRODUCT_TYPE,
										"stock_date" 		=> $DATE],
									[	"avg_price" 		=> $AVG_PRICE,
										"new_cogs_price" 	=> $NEW_COGS_AVG_PRICE
									]);
	}

	/*
	Use 	: Get Base Station Cogs
	Author 	: Kalpak Prajapati
	Date 	: 03 Oct,2022
	*/
	public static function GetBaseStationCogs($BASELOCATIONID,$DATE,$TODAY=false)
	{
		$TOTALCOGS 	= 0;
		if (empty($BASELOCATIONID)) return $TOTALCOGS;
		if (!$TODAY) {
			$COGSSQL 	= "	SELECT * FROM
							(
									SELECT SUM(stock_ladger.closing_stock * stock_ladger.avg_price) AS COGS_VALUE
									FROM stock_ladger
									JOIN company_product_master ON stock_ladger.product_id = company_product_master.id
									JOIN company_product_quality_parameter ON company_product_master.id = company_product_quality_parameter.product_id
									JOIN wm_department as D on stock_ladger.mrf_id = D.id
									WHERE stock_ladger.product_type = ".PRODUCT_PURCHASE."
									AND D.is_virtual = 0
									AND D.base_location_id IN (".$BASELOCATIONID.")
									AND stock_ladger.stock_date = '".$DATE."'
								UNION
									SELECT SUM(stock_ladger.closing_stock * stock_ladger.avg_price) AS COGS_VALUE
									FROM stock_ladger
									JOIN wm_product_master on stock_ladger.product_id = wm_product_master.id
									JOIN wm_department as D on stock_ladger.mrf_id = D.id
									WHERE stock_ladger.product_type = ".PRODUCT_SALES."
									AND D.is_virtual = 0
									AND D.base_location_id IN (".$BASELOCATIONID.")
									AND stock_ladger.stock_date = '".$DATE."'
							) as TBL_COGS";
			$COGSRES 	= \DB::select($COGSSQL);
			if (!empty($COGSRES)) {
				foreach ($COGSRES as $COGSROW) {
					$TOTALCOGS += $COGSROW->COGS_VALUE;
				}
			}
		} else {
			$COGSSQL 	= "	SELECT * FROM
							(
									SELECT
									stock_ladger.opening_stock AS STOCK_QTY,
									stock_ladger.avg_price,
									stock_ladger.product_id,
									stock_ladger.mrf_id,
									'P' as Product_Type
									FROM stock_ladger
									JOIN company_product_master ON stock_ladger.product_id = company_product_master.id
									JOIN company_product_quality_parameter ON company_product_master.id = company_product_quality_parameter.product_id
									JOIN wm_department as D on stock_ladger.mrf_id = D.id
									WHERE stock_ladger.product_type = ".PRODUCT_PURCHASE."
									AND D.is_virtual = 0
									AND D.base_location_id IN (".$BASELOCATIONID.")
									AND stock_ladger.stock_date = '".$DATE."'
								UNION
									SELECT
									stock_ladger.opening_stock AS STOCK_QTY,
									stock_ladger.avg_price,
									stock_ladger.product_id,
									stock_ladger.mrf_id,
									'S' as Product_Type
									FROM stock_ladger
									JOIN wm_product_master on stock_ladger.product_id = wm_product_master.id
									JOIN wm_department as D on stock_ladger.mrf_id = D.id
									WHERE stock_ladger.product_type = ".PRODUCT_SALES."
									AND D.is_virtual = 0
									AND D.base_location_id IN (".$BASELOCATIONID.")
									AND stock_ladger.stock_date = '".$DATE."'
							) as TBL_COGS";
			$COGSRES 	= \DB::select($COGSSQL);
			if (!empty($COGSRES)) {
				foreach ($COGSRES as $COGSROW) {
					if ($TODAY) {
						if($COGSROW->Product_Type == 'P') {
							$PurchaseOutWard 	= 	OutWardLadger::where("outward_date",$DATE)
													->where("product_id",$COGSROW->product_id)
													->where("sales_product_id",0)
													->where("mrf_id",$COGSROW->mrf_id)
													->sum("quantity");
							$PurchaseInWard	 	= 	ProductInwardLadger::where("inward_date",$DATE)
													->where("product_id",$COGSROW->mrf_id)
													->where("product_type",PRODUCT_PURCHASE)
													->where("mrf_id",$COGSROW->mrf_id)
													->sum("quantity");

							$TOTALCOGS 			+= ((($COGSROW->STOCK_QTY + $PurchaseInWard) - $PurchaseOutWard) * $COGSROW->avg_price);
						} else if($COGSROW->Product_Type == 'S') {
							$SalesOutWard 	= 	OutWardLadger::where("outward_date",$DATE)
												->where("sales_product_id",$COGSROW->product_id)
												->where("mrf_id",$COGSROW->mrf_id)
												->sum("quantity");
							$SalesInWard 	= 	ProductInwardLadger::where("inward_date",$DATE)
												->where("product_id",$COGSROW->product_id)
												->where("product_type",PRODUCT_SALES)
												->where("mrf_id",$COGSROW->mrf_id)
												->sum("quantity");
							$TOTALCOGS 		+= ((($COGSROW->STOCK_QTY + $SalesInWard) - $SalesOutWard) * $COGSROW->avg_price);
						}
					} else {
						$TOTALCOGS 		+= ($COGSROW->STOCK_QTY * $COGSROW->avg_price);
					}
				}
			}
		}
		return round($TOTALCOGS);
	}

	/*
	Use 	: PURCHASE & SALES STOCK LEDGER IN OUT STOCK DETAILS REPORT
	Author 	: AXAY SHAH
	Date 	: 31 MARCH,2023
	*/
	public static function GetPurchaseAndSalesStockDetailsReport($req){
		$data 		= array();
		$product_type = (isset($req['product_type']) && !empty($req['product_type'])) ? $req['product_type'] : '';
		$product_id = (isset($req['product_id']) && !empty($req['product_id'])) ? $req['product_id'] : '';
		$mrf_id 	= (isset($req['mrf_id']) && !empty($req['mrf_id'])) ? $req['mrf_id'] : '';
		$start_date = (isset($req['start_date']) && !empty($req['start_date'])) ? date("Y-m-d",strtotime($req['start_date'])) : date("Y-m-d");
		$end_date 	= (isset($req['end_date']) && !empty($req['end_date'])) ? date("Y-m-d",strtotime($req['end_date'])) : date("Y-m-d");
		if(!empty($product_id) && !is_array($product_id)){
			$product_id = explode(",",$product_id);
			if($product_type == PRODUCT_PURCHASE){
				$data = self::GetPurchaseStockDetailsReport($mrf_id,$start_date,$end_date,$product_id);
			}elseif($product_type == PRODUCT_SALES){
				$data = self::GetSalesStockDetailsReport($mrf_id,$start_date,$end_date,$product_id);
			}
		}
		return $data;
	}

	/*
	Use 	: Purchase Product stock details report
	Author 	: AXAY SHAH
	Date 	: 31 MARCH,2023
	*/
	public static function GetPurchaseStockDetailsReport($MRF_ID,$STARTDATE,$ENDDATE,$PRODUCT_IDS){
		$BEGIN 							= new DateTime($STARTDATE);
		$END 							= new DateTime($ENDDATE);
		$PRIVIOUS_DATE_CLOSING_STOCK 	= 0;
		$DATE_RANGE 					= new DatePeriod($BEGIN, new DateInterval('P1D'), $END);
		$PRODUCT_TYPE 					= PRODUCT_PURCHASE;
		$TEMP_ARRAY 					= array();
		$PRODUCTS 						= CompanyProductMaster::whereIn("id",$PRODUCT_IDS)->get()->toArray();
		$MRF_NAME 						= WmDepartment::where("id",$MRF_ID)->value("department_name");
		if(!empty($PRODUCTS)){
			foreach($PRODUCTS AS $PK => $PV){
				$PRODUCT_ID   = $PV['id'];
				$quality_name = CompanyProductQualityParameter::where("product_id",$PV['id'])->value("parameter_name");
				$PRODUCT_NAME = $PV['name']." ".$quality_name;
				foreach($DATE_RANGE as $DATE_VAL){
					$DATE_ARRAY 		= array();
					$STOCK_DATE 		= $DATE_VAL->format("Y-m-d");
					$PRIVIOUS_DATE 		= date('Y-m-d',strtotime($STOCK_DATE." -1 days"));
					$OPEN_STOCK_DATA  	= StockLadger::where("mrf_id",$MRF_ID)
										->where("product_id",$PRODUCT_ID)
										->where("product_type",$PRODUCT_TYPE)
										->where("stock_date",$STOCK_DATE)
										->first();
					$PREV_AVG_PRICE_DATA = StockLadger::where("mrf_id",$MRF_ID)
										->where("product_id",$PRODUCT_ID)
										->where("product_type",$PRODUCT_TYPE)
										->where("stock_date",$PRIVIOUS_DATE)
										->first();
					$OPENING_STOCK 		= (isset($OPEN_STOCK_DATA->opening_stock)) ? $OPEN_STOCK_DATA->opening_stock : 0;
					$INWARD_STOCK 		= (isset($OPEN_STOCK_DATA->inward)) ? $OPEN_STOCK_DATA->inward : 0;
					$OUTWARD_STOCK 		= (isset($OPEN_STOCK_DATA->outward)) ? $OPEN_STOCK_DATA->outward : 0;
					$CLOSING_STOCK 		= (isset($PREV_AVG_PRICE_DATA->closing_stock)) ? $PREV_AVG_PRICE_DATA->closing_stock : 0;
					$PREV_AVG_PRICE 	= (isset($PREV_AVG_PRICE_DATA->avg_price)) ? $PREV_AVG_PRICE_DATA->avg_price : 0;
					$AVG_PRICE_STOCK 	= $PREV_AVG_PRICE;
					$STOCK_VALUE 		= $CLOSING_STOCK * $AVG_PRICE_STOCK;
					$CLOSING_STOCK_QTY 	= 0;
					$TEMP_ARRAY[] 		=	array(
								"mrf_name" => $MRF_NAME,
								"stock_date" => $STOCK_DATE,
								"product_id" => $PRODUCT_ID,
								"product_name" 	=> $PRODUCT_NAME,
								"trn_type" 		=> "OPENING STOCK",
								"opening_stock" => "$OPENING_STOCK",
								"quantity" 	=> "0",
								"rate" 	=> "0",
								"total_value" 	=> "0",
								"closing_stock" => $OPENING_STOCK,
								"avg_price" => $AVG_PRICE_STOCK,
								"stock_value" 	=>_FormatNumberV2($STOCK_VALUE));


					$SQL = "SELECT * FROM
							(
								SELECT
								t1.id,
								t1.product_id,
								if(t1.quantity is null,0,t1.quantity) as quantity,
								if(t1.avg_price is null,0,t1.avg_price) as avg_price,
									 if(t1.revised_avg_price is null,0,t1.revised_avg_price) as revised_avg_price,
								'INWARD' AS TRN_TYPE,
								t1.inward_date as trn_date,
								t1.created_at
								FROM
									inward_ledger as t1
								WHERE
									t1.product_id = $PRODUCT_ID
									AND t1.mrf_id = $MRF_ID
									AND t1.product_type = $PRODUCT_TYPE
									AND t1.direct_dispatch = 0
									AND t1.inward_date= '".$STOCK_DATE."'
							UNION
								SELECT
									t1.id,
									t1.product_id,
									if(t1.quantity is null,0,t1.quantity) as quantity,
									if(t1.avg_price is null,0,t1.avg_price) as avg_price,
									0 as revised_avg_price,
									'OUTWARD' AS TRN_TYPE,
									t1.outward_date as trn_date,
									t1.created_at
								FROM
									outward_ledger t1
								WHERE t1.product_id = $PRODUCT_ID
									AND t1.direct_dispatch = 0
									AND t1.mrf_id = $MRF_ID
									AND t1.outward_date= '".$STOCK_DATE."'
							) as q ORDER BY created_at";
					$DATA 		= \DB::select($SQL);
					$NEW_STOCK 	= $OPENING_STOCK;
					if(!empty($DATA)){
						$i = 0;
						$CLOSING_STOCK 		= 0;
						$TOTAL_INWARD 		= 0;
						$TOTAL_OUTWARD 		= 0;
						foreach($DATA AS $KEY => $VALUE){
							// echo $PREV_AVG_PRICE."  date ".$STOCK_DATE."<br/>";
							$TOTAL_INWARD_VALUE = 0;
							if($VALUE->TRN_TYPE == "INWARD"){
								$TOTAL_INWARD 		+= $VALUE->quantity;
								$TOTAL_INWARD_VALUE = _FormatNumberV2($VALUE->quantity * $VALUE->avg_price);
							}else{
								$TOTAL_OUTWARD 		+= $VALUE->quantity;
							}
							$CLOSING_STOCK_QTY 		 = _FormatNumberV2(($OPENING_STOCK + $TOTAL_INWARD) - $TOTAL_OUTWARD);
							$TEMP = 0;
							$CLOSING_STOCK_VALUE = _FormatNumberV2($CLOSING_STOCK_QTY * $VALUE->avg_price);
							if($VALUE->TRN_TYPE == "INWARD"){
								$TEMP 					= _FormatNumberV2($TOTAL_INWARD_VALUE + ($NEW_STOCK * $PREV_AVG_PRICE));
								$PREV_AVG_PRICE 		= ($CLOSING_STOCK_QTY > 0 ) ? _FormatNumberV2($TEMP / ($CLOSING_STOCK_QTY)) : 0;
								$CLOSING_STOCK_VALUE 	= _FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE);
							}else{
								$CLOSING_STOCK_VALUE 	= _FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE);
							}
							$TEMP_ARRAY[] = array(
								"mrf_name" 		=> $MRF_NAME,
								"stock_date" 	=> $STOCK_DATE,
								"product_id"	=> $PRODUCT_ID,
								"product_name" 	=> $PRODUCT_NAME,
								"trn_type" 		=> $VALUE->TRN_TYPE,
								"opening_stock" => $NEW_STOCK,
								"quantity" 		=> $VALUE->quantity,
								"rate" 			=> $VALUE->avg_price,
								"total_value" 	=> _FormatNumberV2($VALUE->quantity * $VALUE->avg_price),
								"closing_stock" => $CLOSING_STOCK_QTY,
								"avg_price" 	=> $PREV_AVG_PRICE,
								"stock_value" 	=>_FormatNumberV2($CLOSING_STOCK_VALUE));
							$NEW_STOCK = $CLOSING_STOCK_QTY;
							$i++;
						}
					}
				}
			}
		}
		return $TEMP_ARRAY;
	}
	/*
	Use 	: Sales Product stock details report
	Author 	: AXAY SHAH
	Date 	: 31 MARCH,2023
	*/
	public static function GetSalesStockDetailsReport($MRF_ID,$STARTDATE,$ENDDATE,$PRODUCT_IDS){
		$BEGIN 							= new DateTime($STARTDATE);
		$END 							= new DateTime($ENDDATE);
		$PRIVIOUS_DATE_CLOSING_STOCK 	= 0;
		$DATE_RANGE 					= new DatePeriod($BEGIN, new DateInterval('P1D'), $END);
		$PRODUCT_TYPE 					= PRODUCT_SALES;
		$TEMP_ARRAY 					= array();
		$MRF_NAME 						= WmDepartment::where("id",$MRF_ID)->value("department_name");
		$PRODUCTS 						= WmProductMaster::whereIn("id",$PRODUCT_IDS)->get()->toArray();
		if(!empty($PRODUCTS)){
			foreach($PRODUCTS AS $PK => $PV){
				$PRODUCT_ID   = $PV['id'];
				$PRODUCT_NAME = $PV['title'];
				foreach($DATE_RANGE as $DATE_VAL){
					$DATE_ARRAY 		= array();
					$STOCK_DATE 		= $DATE_VAL->format("Y-m-d");
					$PRIVIOUS_DATE 		= date('Y-m-d',strtotime($STOCK_DATE." -1 days"));
					$OPEN_STOCK_DATA  	= StockLadger::where("mrf_id",$MRF_ID)
										->where("product_id",$PRODUCT_ID)
										->where("product_type",$PRODUCT_TYPE)
										->where("stock_date",$STOCK_DATE)
										->first();
					$PREV_AVG_PRICE_DATA = StockLadger::where("mrf_id",$MRF_ID)
										->where("product_id",$PRODUCT_ID)
										->where("product_type",$PRODUCT_TYPE)
										->where("stock_date",$PRIVIOUS_DATE)
										->first();
					$OPENING_STOCK 		= (isset($OPEN_STOCK_DATA->opening_stock)) ? $OPEN_STOCK_DATA->opening_stock : 0;
					$INWARD_STOCK 		= (isset($OPEN_STOCK_DATA->inward)) ? $OPEN_STOCK_DATA->inward : 0;
					$OUTWARD_STOCK 		= (isset($OPEN_STOCK_DATA->outward)) ? $OPEN_STOCK_DATA->outward : 0;
					$CLOSING_STOCK 		= (isset($PREV_AVG_PRICE_DATA->closing_stock)) ? $PREV_AVG_PRICE_DATA->closing_stock : 0;
					$PREV_AVG_PRICE 	= (isset($PREV_AVG_PRICE_DATA->avg_price)) ? $PREV_AVG_PRICE_DATA->avg_price : 0;
					$AVG_PRICE_STOCK 	= $PREV_AVG_PRICE;
					$STOCK_VALUE 		= $CLOSING_STOCK * $AVG_PRICE_STOCK;
					$CLOSING_STOCK_QTY 	= 0;
					$TEMP_ARRAY[] 		=	array(
								"stock_date" 	=> $STOCK_DATE,
								"product_id" 	=> $PRODUCT_ID,
								"mrf_name" 		=> $MRF_NAME,
								"product_name" 	=> $PRODUCT_NAME,
								"trn_type" 		=> "OPENING STOCK",
								"opening_stock" => "$OPENING_STOCK",
								"quantity" 		=> "0",
								"rate" 			=> "0",
								"total_value" 	=> "0",
								"closing_stock" => $OPENING_STOCK,
								"avg_price" 	=> $AVG_PRICE_STOCK,
								"stock_value" 	=>_FormatNumberV2($STOCK_VALUE));
					$SQL = "SELECT * FROM
							(
								SELECT
								t1.product_id,
								if(t1.quantity is null,0,t1.quantity) as quantity,
								if(t1.avg_price is null,0,t1.avg_price) as avg_price,
								'INWARD' AS TRN_TYPE,
								t1.inward_date as trn_date,
								t1.created_at
								FROM
									inward_ledger as t1
								WHERE
									t1.product_id = $PRODUCT_ID
									AND t1.mrf_id = $MRF_ID
									AND t1.product_type = $PRODUCT_TYPE
									AND t1.direct_dispatch = 0
									AND t1.inward_date= '".$STOCK_DATE."'
							UNION
								SELECT
									t1.product_id,
									if(t1.quantity is null,0,t1.quantity) as quantity,
									if(t1.avg_price is null,0,t1.avg_price) as avg_price,
									'OUTWARD' AS TRN_TYPE,
									t1.outward_date as trn_date,
									t1.created_at
								FROM
									outward_ledger t1
								WHERE t1.sales_product_id = $PRODUCT_ID
									AND t1.direct_dispatch = 0
									AND t1.mrf_id = $MRF_ID
									AND t1.outward_date= '".$STOCK_DATE."'
							) as q ORDER BY created_at";
					$DATA 		= \DB::select($SQL);
					$NEW_STOCK 	= $OPENING_STOCK;
					if(!empty($DATA)){
						$i = 0;
						$CLOSING_STOCK 		= 0;
						$TOTAL_INWARD 		= 0;
						$TOTAL_OUTWARD 		= 0;
						foreach($DATA AS $KEY => $VALUE){
							// echo $PREV_AVG_PRICE."  date ".$STOCK_DATE."<br/>";
							$TOTAL_INWARD_VALUE = 0;
							if($VALUE->TRN_TYPE == "INWARD"){
								$TOTAL_INWARD 		+= $VALUE->quantity; 
								$TOTAL_INWARD_VALUE = _FormatNumberV2($VALUE->quantity * $VALUE->avg_price);
							}else{
								$TOTAL_OUTWARD 		+= $VALUE->quantity; 
							}
							$CLOSING_STOCK_QTY 		 = _FormatNumberV2(($OPENING_STOCK + $TOTAL_INWARD) - $TOTAL_OUTWARD);
							$TEMP = 0;
							$CLOSING_STOCK_VALUE = _FormatNumberV2($CLOSING_STOCK_QTY * $VALUE->avg_price);
							if($VALUE->TRN_TYPE == "INWARD"){
								$TEMP 					= _FormatNumberV2($TOTAL_INWARD_VALUE + ($NEW_STOCK * $PREV_AVG_PRICE));
								$PREV_AVG_PRICE 		= ($CLOSING_STOCK_QTY > 0 ) ? _FormatNumberV2($TEMP / ($CLOSING_STOCK_QTY)) : 0;
								$CLOSING_STOCK_VALUE 	= _FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE);
							}else{
								$CLOSING_STOCK_VALUE 	= _FormatNumberV2($CLOSING_STOCK_QTY * $PREV_AVG_PRICE);
							}
							
							$TEMP_ARRAY[] = array(
								"mrf_name" 		=> $MRF_NAME,
								"stock_date" 	=> $STOCK_DATE,
								"product_id"	=> $PRODUCT_ID,
								"product_name" 	=> $PRODUCT_NAME,
								"trn_type" 		=> $VALUE->TRN_TYPE,
								"opening_stock" => $NEW_STOCK,
								"quantity" 		=> $VALUE->quantity,
								"rate" 			=> $VALUE->avg_price,
								"total_value" 	=> _FormatNumberV2($VALUE->quantity * $VALUE->avg_price),
								"closing_stock" => $CLOSING_STOCK_QTY,
								"avg_price" 	=> $PREV_AVG_PRICE,
								"stock_value" 	=>_FormatNumberV2($CLOSING_STOCK_VALUE));
							$NEW_STOCK = $CLOSING_STOCK_QTY;
							$i++;
						}
					}
				}
			}
		}
		return $TEMP_ARRAY;
	}


}