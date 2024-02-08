<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\WmProductMaster;
use Mail;
Use DateTime,DateInterval,DatePeriod;

class UpdateSalesStockAvgPriceDumyTable extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'UpdateSalesStockAvgPriceDumyTable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'UpdateSalesStockAvgPriceDumyTable';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// return false;
		echo "start time ".date("Y-m-d H:i:s")."<br/>";
		######## STOCK #######
		$begin          = new DateTime('2022-02-02');
	    $end            = new DateTime('2022-03-01');
	    $interval       = DateInterval::createFromDateString('1 day');
	    $period         = new DatePeriod($begin, $interval, $end);
	    $PRODUCT_ARR    = WmProductMaster::where("status",1)->where("id",18)->pluck("id")->toArray();
	   // prd($PRODUCT_ARR);
	    // $MRF_ID         = 11;
	    $PRODUCT_TYPE   = 2;
		$MRF_IDS 		= array(22);
		foreach($MRF_IDS AS $MRF_ID){
		    foreach ($period as $dt) {
		        $STOCK_DATE  = $dt->format("Y-m-d");
		        foreach($PRODUCT_ARR AS $PRODUCT_ID){
		            $LEDGER_ENTRY   =   \DB::table("sales_avg_data_update")
		            ->where("product_id",$PRODUCT_ID)
		            ->where("product_type",$PRODUCT_TYPE)
		            ->where("mrf_id",$MRF_ID)
		            ->where("process",0)
		            ->where("entry_date",$STOCK_DATE)
		            ->get()
		            ->toArray();
		            // prd($LEDGER_ENTRY);
		            if(!empty($LEDGER_ENTRY)){
		            	foreach($LEDGER_ENTRY AS $KEY => $ENTRY_VAL){
		            		// PRD($ENTRY_VAL);
							$CLOSING_QTY 	= 0;
							$AVG_PRICE 		= 0;
							$DATA = \DB::table("stock_ladger_20_01_2022_axay")
				            ->where("stock_date",$STOCK_DATE)
				            ->where("product_id",$PRODUCT_ID)
				            ->where("mrf_id",$MRF_ID)
				            ->where("product_type",$PRODUCT_TYPE)
				            ->first();
				            
				           	$OPENING_STOCK  = (isset($DATA->opening_stock)) ? $DATA->opening_stock : 0;
				            $OPENING_AVG    = (isset($DATA->avg_price)) ? $DATA->avg_price : 0;
		            		
		                   	$TOTAL_IN_QTY   =  \DB::table("sales_avg_data_update")
		                    ->where("product_id",$PRODUCT_ID)
		                    ->where("mrf_id",$MRF_ID)
		                    ->where("process",1)
		                    ->where("type",1)
		                    ->where("product_type",PRODUCT_SALES)
		                    ->where("entry_date",$STOCK_DATE)
		                    ->sum("quantity");

		                    $TOTAL_OUT_QTY  =   \DB::table("sales_avg_data_update")
		                    ->where("product_id",$PRODUCT_ID)
		                    ->where("process",1)
		                    ->where("product_type",PRODUCT_SALES)
		                    ->where("type",2)
		                    ->where("mrf_id",$MRF_ID)
		                    ->where("entry_date",$STOCK_DATE)
		                    ->sum("quantity");

		                    $AVG_PRICE 		= 0;
		                    $TOTAL_PRICE    = 0;
		                    $GRAND_TOTAL    = 0;
							

		                    $CLOSING_QTY    = _FormatNumberV2(($OPENING_STOCK + $TOTAL_IN_QTY) - $TOTAL_OUT_QTY);
		                    $GRAND_TOTAL    = _FormatNumberV2(($CLOSING_QTY * $OPENING_AVG));
		                    echo "================NEW RECORD=================<br/>";
		                	echo "OPENING = ".$OPENING_STOCK." , TOTAL_IN_QTY = ".$TOTAL_IN_QTY." , TOTAL_OUT_QTY = ".$TOTAL_OUT_QTY." , CLOSING_QTY = ".$CLOSING_QTY." , OPENING_AVG = ". $OPENING_AVG ." GRAND TOTAL = ".$GRAND_TOTAL."<br/>" ;
		                    if($ENTRY_VAL->type == 1){
		                		$CLOSING_QTY 	= $CLOSING_QTY + $ENTRY_VAL->quantity;
		                		$GRAND_TOTAL 	= $GRAND_TOTAL + _FormatNumberV2($ENTRY_VAL->quantity * $ENTRY_VAL->avg_price);
		                	}else{
		                		$CLOSING_QTY 	= $CLOSING_QTY - $ENTRY_VAL->quantity;
		                		$GRAND_TOTAL 	= _FormatNumberV2($CLOSING_QTY * $OPENING_AVG);
		                	}


		                	$AVG_PRICE      = (!empty($CLOSING_QTY)) ? _FormatNumberV2($GRAND_TOTAL / $CLOSING_QTY) : 0;
		                	
		                	echo "CLOSING_QTY = ".$CLOSING_QTY." ,GRAND TOTAL = ".$GRAND_TOTAL."<br/>" ;
		                	echo "FINAL AVG PRICE = ".$AVG_PRICE."<br/>";
		                	echo "=================================<br/>";
		                	
		                	
		                    \DB::table("sales_avg_data_update")
		                    ->where("id",$ENTRY_VAL->id)
		                    ->update(["process" => 1]);
		                    $NEXT_DATE  = date('Y-m-d', strtotime('+1 day', strtotime($STOCK_DATE)));
					        ########### UPDATE ON CURRENT DAY ##############
			                \DB::table("stock_ladger_20_01_2022_axay")
			                ->where("stock_date",$STOCK_DATE)
			                ->where("product_id",$PRODUCT_ID)
			                ->where("mrf_id",$MRF_ID)
			                ->where("product_type",$PRODUCT_TYPE)
			                ->update(["avg_price" => $AVG_PRICE]);
			            	########### UPDATE ON NEXT DAY ##############
			                \DB::table("stock_ladger_20_01_2022_axay")
			                ->where("stock_date",$NEXT_DATE)
			                ->where("product_id",$PRODUCT_ID)
			                ->where("mrf_id",$MRF_ID)
			                ->where("product_type",$PRODUCT_TYPE)
			                ->update(["avg_price" => $AVG_PRICE]);
		            	}
		            }else{
		            	$DATA = \DB::table("stock_ladger_20_01_2022_axay")
			            ->where("stock_date",$STOCK_DATE)
			            ->where("product_id",$PRODUCT_ID)
			            ->where("mrf_id",$MRF_ID)
			            ->where("product_type",$PRODUCT_TYPE)
			            ->first();
			           	$OPENING_STOCK  = (isset($DATA->opening_stock)) ? $DATA->opening_stock : 0;
			            $OPENING_AVG    = (isset($DATA->avg_price)) ? $DATA->avg_price : 0;
		                $CLOSING_QTY    = (isset($DATA->closing_stock)) ? $DATA->closing_stock : 0;
		                
		                $NEXT_DATE  		= date('Y-m-d', strtotime('+1 day', strtotime($STOCK_DATE)));
		                $PRIVIOUS_DATE  	= date('Y-m-d', strtotime('-1 day', strtotime($STOCK_DATE)));
		                
		                $PRIVIOUS_DATE_AVG 	= \DB::table("stock_ladger_20_01_2022_axay")
			            ->where("stock_date",$PRIVIOUS_DATE)
			            ->where("product_id",$PRODUCT_ID)
			            ->where("mrf_id",$MRF_ID)
			            ->where("product_type",$PRODUCT_TYPE)
			            ->value('avg_price');
						$AVG_PRICE 		= ($CLOSING_QTY > 0) ? _FormatNumberV2($PRIVIOUS_DATE_AVG) : 0;
			          	########### UPDATE ON CURRENT DAY ##############
		                \DB::table("stock_ladger_20_01_2022_axay")
		                ->where("stock_date",$STOCK_DATE)
		                ->where("product_id",$PRODUCT_ID)
		                ->where("mrf_id",$MRF_ID)
		                ->where("product_type",$PRODUCT_TYPE)
		                ->update(["avg_price" => $AVG_PRICE]);
		            	########### UPDATE ON NEXT DAY ##############
		                \DB::table("stock_ladger_20_01_2022_axay")
		                ->where("stock_date",$NEXT_DATE)
		                ->where("product_id",$PRODUCT_ID)
		                ->where("mrf_id",$MRF_ID)
		                ->where("product_type",$PRODUCT_TYPE)
		                ->update(["avg_price" => $AVG_PRICE]);
		            }
		            echo $STOCK_DATE." AVG PRICE ".$AVG_PRICE;
					
		        }
		    }
	  	}
	    echo "end time ".date("Y-m-d H:i:s")."<br/>";
		####### STOCK #########
	}
}