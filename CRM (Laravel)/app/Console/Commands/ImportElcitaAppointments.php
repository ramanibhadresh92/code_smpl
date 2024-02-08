<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerMaster;
use App\Models\Appoinment;
use App\Models\VehicleMaster;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use Mail;
use DB;

class ImportElcitaAppointments extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ImportElcitaAppointments';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Import Elcita Appointment For Custom Products';

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
		echo "\r\n--StartTime::".date("Y-m-d H:i:s")."--\r\n";

		$SELECT_SQL 	= "SELECT * FROM elcita_collection_import WHERE processed = 0 ORDER BY id ASC";
		$SelectRes 		= DB::select($SELECT_SQL);
		$ImportID 		= "20230606";
		$ImportFileName = "Pending-LR-entries-".$ImportID.".xlsx";
		$ImportData 	= true;
		$no_of_lines 	= 0;
		$counter 		= 0;
		if (!empty($SelectRes))
		{
			foreach ($SelectRes as $SelectRows)
			{
				$UPDATE_SQL = "UPDATE elcita_collection_import SET processed = 2 WHERE id = ".$SelectRows->id;
				DB::connection()->statement($UPDATE_SQL);

				$customerCode 		= $SelectRows->customer_code;
				$collectionDate 	= $SelectRows->collection_date;
				$collectionTime 	= $this->GetCollectionTime();
				$vehicleId 			= $SelectRows->vehicle_id;
				$supervisorId 		= $SelectRows->adminuserid;
				$categoryId 		= 5; //Misc Corporate Waste
 				$dateArr 			= explode("-", $collectionDate);
				$collection_date  	= date("Y-m-d H:i:s",strtotime($dateArr[2]."-".$dateArr[1]."-".$dateArr[0]." ".$collectionTime));
				$CustomerMaster 	= CustomerMaster::select('customer_id','city','company_id')->where('code',trim($customerCode))->first();
				if(!empty($CustomerMaster) && $ImportData && $collection_date != "1970-01-01")
				{
					$objAppointment 					= new Appoinment;
					$objAppointment->customer_id 		= $CustomerMaster->customer_id;
					$objAppointment->city_id 			= $CustomerMaster->city;
					$objAppointment->company_id 		= $CustomerMaster->company_id;
					$objAppointment->vehicle_id 		= $vehicleId;
					$objAppointment->collection_by 		= $supervisorId;
					$objAppointment->supervisor_id 		= $supervisorId;
					$objAppointment->app_date_time 		= $collection_date;
					$objAppointment->para_status_id 	= APPOINTMENT_COMPLETED;
					$objAppointment->import_id 			= $ImportID;
					$objAppointment->import_file_name 	= $ImportFileName;
					$objAppointment->app_type 			= 0;
					$objAppointment->foc 				= 1;
					$objAppointment->earn_type 			= EARN_TYPE_FREE;
					$objAppointment->dummy_appointment 	= "N";
					$objAppointment->created_by 		= 1;
					$objAppointment->created_at 		= $collection_date;
					$objAppointment->updated_by 		= 1;
					$objAppointment->updated_at 		= $collection_date;
					if($objAppointment->save())
					{
						$objAppointmentCollection 					= new AppointmentCollection;
						$objAppointmentCollection->appointment_id 	= $objAppointment->appointment_id;
						$objAppointmentCollection->city_id 			= $CustomerMaster->city;
						$objAppointmentCollection->company_id 		= $CustomerMaster->company_id;
						$objAppointmentCollection->vehicle_id 		= $vehicleId;
						$objAppointmentCollection->collection_by 	= $supervisorId;
						$objAppointmentCollection->para_status_id 	= COLLECTION_NOT_APPROVED;
						$objAppointmentCollection->collection_dt 	= $collection_date;
						$objAppointmentCollection->amount 			= 0;
						$objAppointmentCollection->payable_amount 	= 0;
						$objAppointmentCollection->given_amount 	= 0;
						$objAppointmentCollection->import_id 		= $ImportID;
						$objAppointmentCollection->created_by 		= 1;
						$objAppointmentCollection->created_at 		= $collection_date;
						$objAppointmentCollection->updated_by 		= 1;
						$objAppointmentCollection->updated_at 		= $collection_date;
						if ($objAppointmentCollection->save())
						{

							if (!empty($SelectRows->dry_waste)) {
								$productId 													= 261;
								$qualityId 													= 261;
								$objAppointmentCollectionDetails 							= new AppointmentCollectionDetail;
								$objAppointmentCollectionDetails->collection_id 			= $objAppointmentCollection->collection_id;
								$objAppointmentCollectionDetails->city_id 					= $CustomerMaster->city;
								$objAppointmentCollectionDetails->company_id 				= $CustomerMaster->company_id;
								$objAppointmentCollectionDetails->category_id 				= $categoryId;
								$objAppointmentCollectionDetails->product_id 				= $productId;
								$objAppointmentCollectionDetails->product_quality_para_id 	= $qualityId;
								$objAppointmentCollectionDetails->product_customer_price 	= 0;
								$objAppointmentCollectionDetails->product_para_unit_id 		= PARA_PRODUCT_UNIT_IN_KG;
								$objAppointmentCollectionDetails->para_quality_price 		= 0;
								$objAppointmentCollectionDetails->quantity 					= $SelectRows->dry_waste;
								$objAppointmentCollectionDetails->actual_coll_quantity 		= $SelectRows->dry_waste;
								$objAppointmentCollectionDetails->price 					= 0;
								$objAppointmentCollectionDetails->no_of_bag 				= 1;
								$objAppointmentCollectionDetails->factory_price 			= 0;
								$objAppointmentCollectionDetails->sales_qty 				= $SelectRows->dry_waste;
								$objAppointmentCollectionDetails->sales_product_inert 		= 0;
								$objAppointmentCollectionDetails->sales_process_loss 		= 0;
								$objAppointmentCollectionDetails->import_id 				= $ImportID;
								$objAppointmentCollectionDetails->para_status_id 			= PARA_STATUS_NOT_APPROVED;
								$objAppointmentCollectionDetails->created_by 				= 1;
								$objAppointmentCollectionDetails->created_at 				= $collection_date;
								$objAppointmentCollectionDetails->updated_by 				= 1;
								$objAppointmentCollectionDetails->updated_at 				= $collection_date;
								$objAppointmentCollectionDetails->save();
							}
							if (!empty($SelectRows->wet_waste)) {
								$productId 													= 262;
								$qualityId 													= 262;
								$objAppointmentCollectionDetails 							= new AppointmentCollectionDetail;
								$objAppointmentCollectionDetails->collection_id 			= $objAppointmentCollection->collection_id;
								$objAppointmentCollectionDetails->city_id 					= $CustomerMaster->city;
								$objAppointmentCollectionDetails->company_id 				= $CustomerMaster->company_id;
								$objAppointmentCollectionDetails->category_id 				= $categoryId;
								$objAppointmentCollectionDetails->product_id 				= $productId;
								$objAppointmentCollectionDetails->product_quality_para_id 	= $qualityId;
								$objAppointmentCollectionDetails->product_customer_price 	= 0;
								$objAppointmentCollectionDetails->product_para_unit_id 		= PARA_PRODUCT_UNIT_IN_KG;
								$objAppointmentCollectionDetails->para_quality_price 		= 0;
								$objAppointmentCollectionDetails->quantity 					= $SelectRows->wet_waste;
								$objAppointmentCollectionDetails->actual_coll_quantity 		= $SelectRows->wet_waste;
								$objAppointmentCollectionDetails->price 					= 0;
								$objAppointmentCollectionDetails->no_of_bag 				= 1;
								$objAppointmentCollectionDetails->factory_price 			= 0;
								$objAppointmentCollectionDetails->sales_qty 				= $SelectRows->wet_waste;
								$objAppointmentCollectionDetails->sales_product_inert 		= 0;
								$objAppointmentCollectionDetails->sales_process_loss 		= 0;
								$objAppointmentCollectionDetails->import_id 				= $ImportID;
								$objAppointmentCollectionDetails->para_status_id 			= PARA_STATUS_NOT_APPROVED;
								$objAppointmentCollectionDetails->created_by 				= 1;
								$objAppointmentCollectionDetails->created_at 				= $collection_date;
								$objAppointmentCollectionDetails->updated_by 				= 1;
								$objAppointmentCollectionDetails->updated_at 				= $collection_date;
								$objAppointmentCollectionDetails->save();
							}
							if (!empty($SelectRows->reject_waste)) {
								$productId 													= 263;
								$qualityId 													= 263;
								$objAppointmentCollectionDetails 							= new AppointmentCollectionDetail;
								$objAppointmentCollectionDetails->collection_id 			= $objAppointmentCollection->collection_id;
								$objAppointmentCollectionDetails->city_id 					= $CustomerMaster->city;
								$objAppointmentCollectionDetails->company_id 				= $CustomerMaster->company_id;
								$objAppointmentCollectionDetails->category_id 				= $categoryId;
								$objAppointmentCollectionDetails->product_id 				= $productId;
								$objAppointmentCollectionDetails->product_quality_para_id 	= $qualityId;
								$objAppointmentCollectionDetails->product_customer_price 	= 0;
								$objAppointmentCollectionDetails->product_para_unit_id 		= PARA_PRODUCT_UNIT_IN_KG;
								$objAppointmentCollectionDetails->para_quality_price 		= 0;
								$objAppointmentCollectionDetails->quantity 					= $SelectRows->reject_waste;
								$objAppointmentCollectionDetails->actual_coll_quantity 		= $SelectRows->reject_waste;
								$objAppointmentCollectionDetails->price 					= 0;
								$objAppointmentCollectionDetails->no_of_bag 				= 1;
								$objAppointmentCollectionDetails->factory_price 			= 0;
								$objAppointmentCollectionDetails->sales_qty 				= $SelectRows->reject_waste;
								$objAppointmentCollectionDetails->sales_product_inert 		= 0;
								$objAppointmentCollectionDetails->sales_process_loss 		= 0;
								$objAppointmentCollectionDetails->import_id 				= $ImportID;
								$objAppointmentCollectionDetails->para_status_id 			= PARA_STATUS_NOT_APPROVED;
								$objAppointmentCollectionDetails->created_by 				= 1;
								$objAppointmentCollectionDetails->created_at 				= $collection_date;
								$objAppointmentCollectionDetails->updated_by 				= 1;
								$objAppointmentCollectionDetails->updated_at 				= $collection_date;
								$objAppointmentCollectionDetails->save();
							}
							if (!empty($SelectRows->garden_waste)) {
								$productId 													= 264;
								$qualityId 													= 264;
								$objAppointmentCollectionDetails 							= new AppointmentCollectionDetail;
								$objAppointmentCollectionDetails->collection_id 			= $objAppointmentCollection->collection_id;
								$objAppointmentCollectionDetails->city_id 					= $CustomerMaster->city;
								$objAppointmentCollectionDetails->company_id 				= $CustomerMaster->company_id;
								$objAppointmentCollectionDetails->category_id 				= $categoryId;
								$objAppointmentCollectionDetails->product_id 				= $productId;
								$objAppointmentCollectionDetails->product_quality_para_id 	= $qualityId;
								$objAppointmentCollectionDetails->product_customer_price 	= 0;
								$objAppointmentCollectionDetails->product_para_unit_id 		= PARA_PRODUCT_UNIT_IN_KG;
								$objAppointmentCollectionDetails->para_quality_price 		= 0;
								$objAppointmentCollectionDetails->quantity 					= $SelectRows->garden_waste;
								$objAppointmentCollectionDetails->actual_coll_quantity 		= $SelectRows->garden_waste;
								$objAppointmentCollectionDetails->price 					= 0;
								$objAppointmentCollectionDetails->no_of_bag 				= 1;
								$objAppointmentCollectionDetails->factory_price 			= 0;
								$objAppointmentCollectionDetails->sales_qty 				= $SelectRows->garden_waste;
								$objAppointmentCollectionDetails->sales_product_inert 		= 0;
								$objAppointmentCollectionDetails->sales_process_loss 		= 0;
								$objAppointmentCollectionDetails->import_id 				= $ImportID;
								$objAppointmentCollectionDetails->para_status_id 			= PARA_STATUS_NOT_APPROVED;
								$objAppointmentCollectionDetails->created_by 				= 1;
								$objAppointmentCollectionDetails->created_at 				= $collection_date;
								$objAppointmentCollectionDetails->updated_by 				= 1;
								$objAppointmentCollectionDetails->updated_at 				= $collection_date;
								$objAppointmentCollectionDetails->save();
							}
							$counter++;
						}
					}
					$UPDATE_SQL = "UPDATE elcita_collection_import SET processed = 1 WHERE id = ".$SelectRows->id;
					DB::connection()->statement($UPDATE_SQL);
					$no_of_lines++;
				} else {
					echo "\r\n".$customerCode." -- Customer Code NOT EXISTS.\r\n";
				}
			}
			echo "\r\n".$counter." -- Collection data imported successfully.\r\n";
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}

	public function GetProductFactoryPrice($CustomerCode,$productId)
	{
		$factory_price 		= 0;
		$SelectSql			= "	SELECT company_product_price_details.factory_price
								FROM customer_master
								INNER JOIN company_product_price_details ON customer_master.price_group = company_product_price_details.para_waste_type_id
								WHERE customer_master.code = '".trim($CustomerCode)."'
								AND company_product_price_details.product_id = ".intval($productId);
		$SelectRes  = DB::select($SelectSql);
		if (!empty($SelectRes)) {
			foreach ($SelectRes as $SelectRows) {
				$factory_price = $SelectRows->factory_price;
			}
		}
		return $factory_price;
	}

	public function GetCollectionTime()
	{
		$hour 		= rand(10,18);
		$hour 		= ($hour) < 10?"0".$hour:$hour;
		$minutes 	= array(10,15,20,25,30,35,40,45,50,55);
		$key 		= array_rand($minutes);
		$minute 	= isset($minutes[$key])?$minutes[$key]:"00";
		return $hour.":".$minute.":00";
	}
}