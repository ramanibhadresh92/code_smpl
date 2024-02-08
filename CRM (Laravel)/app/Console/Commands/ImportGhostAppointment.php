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

class ImportGhostAppointment extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ImportGhostAppointment';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Import Ghost Appointment';

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

		$DIR_TO_SCAN		= storage_path()."/import_collection/*.csv";
		$CSV_File_Names 	= glob($DIR_TO_SCAN);
		foreach ($CSV_File_Names as $CSVFileName)
		{
			$CSV_File_Name 		= basename($CSVFileName);
			$ImportID 			= date("Y-m-d");
			$SERVER_FILE_PATH 	= $CSVFileName;
			echo "\r\n--SERVER_FILE_PATH::".$SERVER_FILE_PATH."--\r\n";
			if (file_exists($SERVER_FILE_PATH))
			{
				$counter					= 0;
				$ImportData 				= true;
				$no_of_lines 				= 0;
				$file_handle 				= fopen($SERVER_FILE_PATH, 'r');
				$vehicleList 				= VehicleMaster::GetCollectionVehicles();
				while (!feof($file_handle))
				{
					$line_of_text = array();
					$line_of_text = fgetcsv($file_handle);
					if($no_of_lines > 0)
					{
						if(!empty($line_of_text[0]) && !empty($line_of_text[5]) && !empty($line_of_text[6]) && !empty($line_of_text[7]) && !empty($line_of_text[10]) && !empty($line_of_text[11]) && !empty($line_of_text[12]))
						{
							$customerCode 		= $line_of_text[0];
							$collectionDate 	= $line_of_text[1];
							// $collectionTime 	= $line_of_text[2];
							$collectionTime 	= $this->GetCollectionTime();
							$vehicleNumber		= strtolower(preg_replace("/[^\da-z]/i","",$line_of_text[3]));
							$supervisorId 		= $line_of_text[4];
							$collection_by_user = $line_of_text[5]; //Collection By Name
							$categoryId 		= $line_of_text[6];
							$productId 			= $line_of_text[7];
							$qualityId 			= $line_of_text[9];
							$productName 		= $line_of_text[8];
							$quantity 			= $line_of_text[10];
							$actQuantity 		= $line_of_text[11];
							$price 				= $line_of_text[12];
							$amount 			= $actQuantity * $price;
							$factoryPrice 		= $this->GetProductFactoryPrice($customerCode,$productId);
							$salesProcessLoss 	= "15";
							$salesProdInert 	= "30";
							$processingCost 	= "15";
							$salesQty 			= ($quantity-(($salesProdInert/100) * $quantity))*(1-($processingCost/100));
							$arrVehicle 		= (isset($vehicleList[$vehicleNumber])?$vehicleList[$vehicleNumber]:array());
							$dateArr 			= explode("-", $collectionDate);
							if (sizeof($dateArr) == 3) {
								$collection_date  	= date("Y-m-d H:i:s",strtotime($dateArr[2]."-".$dateArr[1]."-".$dateArr[0]." ".$collectionTime));
							} else {
								$dateArr 			= explode("/", $collectionDate);
								$collection_date  	= date("Y-m-d H:i:s",strtotime($dateArr[2]."-".$dateArr[1]."-".$dateArr[0]." ".$collectionTime));
							}
							if (sizeof($dateArr) == 3) {
								$CustomerMaster 	= CustomerMaster::select('customer_id','city','company_id')->where('code',trim($customerCode))->first();
								if(!empty($CustomerMaster) && $ImportData && $collection_date != "1970-01-01")
								{
									$vehicleId 							= isset($arrVehicle['vehicle_id'])?$arrVehicle['vehicle_id']:0;
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
									$objAppointment->import_file_name 	= $CSV_File_Name;
									$objAppointment->app_type 			= 0;
									$objAppointment->foc 				= 0;
									$objAppointment->earn_type 			= EARN_TYPE_CASH;
									$objAppointment->dummy_appointment 	= "Y";
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
										$objAppointmentCollection->amount 			= $amount;
										$objAppointmentCollection->payable_amount 	= $amount;
										$objAppointmentCollection->given_amount 	= $amount;
										$objAppointmentCollection->import_id 		= $ImportID;
										$objAppointmentCollection->created_by 		= 1;
										$objAppointmentCollection->created_at 		= $collection_date;
										$objAppointmentCollection->updated_by 		= 1;
										$objAppointmentCollection->updated_at 		= $collection_date;
										if ($objAppointmentCollection->save())
										{
											$objAppointmentCollectionDetails 							= new AppointmentCollectionDetail;
											$objAppointmentCollectionDetails->collection_id 			= $objAppointmentCollection->collection_id;
											$objAppointmentCollectionDetails->city_id 					= $CustomerMaster->city;
											$objAppointmentCollectionDetails->company_id 				= $CustomerMaster->company_id;
											$objAppointmentCollectionDetails->category_id 				= $categoryId;
											$objAppointmentCollectionDetails->product_id 				= $productId;
											$objAppointmentCollectionDetails->product_quality_para_id 	= $qualityId;
											$objAppointmentCollectionDetails->product_customer_price 	= $price;
											$objAppointmentCollectionDetails->product_para_unit_id 		= PARA_PRODUCT_UNIT_IN_KG;
											$objAppointmentCollectionDetails->para_quality_price 		= $price;
											$objAppointmentCollectionDetails->quantity 					= $quantity;
											$objAppointmentCollectionDetails->actual_coll_quantity 		= $actQuantity;
											$objAppointmentCollectionDetails->price 					= $amount;
											$objAppointmentCollectionDetails->no_of_bag 				= 1;
											$objAppointmentCollectionDetails->factory_price 			= $factoryPrice;
											$objAppointmentCollectionDetails->sales_qty 				= $salesQty;
											$objAppointmentCollectionDetails->sales_product_inert 		= $salesProdInert;
											$objAppointmentCollectionDetails->sales_process_loss 		= $salesProcessLoss;
											$objAppointmentCollectionDetails->import_id 				= $ImportID;
											$objAppointmentCollectionDetails->para_status_id 			= PARA_STATUS_NOT_APPROVED;
											$objAppointmentCollectionDetails->created_by 				= 1;
											$objAppointmentCollectionDetails->created_at 				= $collection_date;
											$objAppointmentCollectionDetails->updated_by 				= 1;
											$objAppointmentCollectionDetails->updated_at 				= $collection_date;
											$objAppointmentCollectionDetails->save();
											$counter++;
										}
									}
								} else {
									echo "\r\n".$customerCode." -- Customer Code NOT EXISTS.\r\n";
								}
							} else {
								echo "\r\n Invalid Collection Date Format.\r\n";
								break;
							}
						}
					}
					$no_of_lines++;
				}
				echo "\r\n".$counter." -- Collection data imported successfully.\r\n";
				unlink($SERVER_FILE_PATH);
			} else {
				echo "\r\nfile not found.\r\n";
			}
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