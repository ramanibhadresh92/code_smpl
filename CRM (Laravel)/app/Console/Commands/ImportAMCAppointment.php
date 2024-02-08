<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerMaster;
use App\Models\Appoinment;
use App\Models\VehicleMaster;
use App\Models\VehicleDriverMappings;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use Mail;
use DB;

class ImportAMCAppointment extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ImportAMCAppointment';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Import AMC Appointment';

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
		$CSV_File_Name 		= "AMC-AUG-2018-7.csv";
		$ImportID 			= "20190801";
		$ImportIDApp		= "AMC-AUG-2018-7";
		$SERVER_FILE_PATH 	= storage_path($CSV_File_Name);

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
					if(!empty($line_of_text[0]) && !empty($line_of_text[5]) && !empty($line_of_text[2]) && !empty($line_of_text[10]))
					{
						$customerCode 		= "CUS-".$line_of_text[0];
						$collectionDate 	= $line_of_text[1];
						$collectionTime 	= $line_of_text[2];
						$vehicleNumber		= strtolower(preg_replace("/[^\da-z]/i","",$line_of_text[3]));
						$collection_by_user = $line_of_text[5]; //Collection By Name
						$productId 			= $line_of_text[6];
						$quantity 			= $line_of_text[9];
						$actQuantity 		= $line_of_text[10];
						$price 				= $line_of_text[11];
						$amount 			= $actQuantity * $price;
						$arrProduct			= $this->GetProductCatAndQuality($productId);
						$categoryId 		= $arrProduct['category_id'];
						$qualityId 			= $arrProduct['quality_id'];
						$factoryPrice 		= $this->GetProductFactoryPrice($customerCode,$productId);
						$salesProcessLoss 	= "15";
						$salesProdInert 	= "30";
						$processingCost 	= "15";
						$salesQty 			= ($quantity-(($salesProdInert/100) * $quantity))*(1-($processingCost/100));
						$arrVehicle 		= (isset($vehicleList[$vehicleNumber])?$vehicleList[$vehicleNumber]:array());
						$dateArr 			= explode("/", $collectionDate);
						$collection_date  	= $dateArr[2]."-".$dateArr[1]."-".$dateArr[0]." ".$collectionTime;
						$CustomerMaster 	= CustomerMaster::select('customer_id','city','company_id')->where('code',trim($customerCode))->first();
						if(!empty($CustomerMaster) && $ImportData)
						{
							$vehicleId 						= isset($arrVehicle['vehicle_id'])?$arrVehicle['vehicle_id']:0;
							$supervisorId 					= VehicleDriverMappings::getVehicleMappedCollectionBy($vehicleId);
							$supervisorId 					= ($supervisorId > 0)?$supervisorId:1;
							$vehicleId 						= ($vehicleId > 0)?$vehicleId:1;
							$objAppointment 				= new Appoinment;
							$objAppointment->customer_id 	= $CustomerMaster->customer_id;
							$objAppointment->city_id 		= $CustomerMaster->city;
							$objAppointment->company_id 	= $CustomerMaster->company_id;
							$objAppointment->vehicle_id 	= $vehicleId;
							$objAppointment->collection_by 	= $supervisorId;
							$objAppointment->supervisor_id 	= $supervisorId;
							$objAppointment->app_date_time 	= $collection_date;
							$objAppointment->para_status_id = APPOINTMENT_COMPLETED;
							$objAppointment->import_id 		= $ImportIDApp;
							$objAppointment->app_type 		= 0;
							$objAppointment->foc 			= 0;
							$objAppointment->earn_type 		= EARN_TYPE_CASH;
							$objAppointment->created_by 	= 1;
							$objAppointment->created_at 	= date('Y-m-d H:i:s');
							$objAppointment->updated_by 	= 1;
							$objAppointment->updated_at 	= date('Y-m-d H:i:s');
							if($objAppointment->save())
							{
								$objAppointmentCollection 					= new AppointmentCollection;
								$objAppointmentCollection->appointment_id 	= $objAppointment->appointment_id;
								$objAppointmentCollection->vehicle_id 		= $vehicleId;
								$objAppointmentCollection->collection_by 	= $supervisorId;
								$objAppointmentCollection->para_status_id 	= COLLECTION_NOT_APPROVED;
								$objAppointmentCollection->collection_dt 	= $collection_date;
								$objAppointmentCollection->amount 			= $amount;
								$objAppointmentCollection->payable_amount 	= $amount;
								$objAppointmentCollection->given_amount 	= $amount;
								$objAppointmentCollection->import_id 		= $ImportID;
								$objAppointmentCollection->created_by 		= 1;
								$objAppointmentCollection->created_at 		= date('Y-m-d H:i:s');
								$objAppointmentCollection->updated_by 		= 1;
								$objAppointmentCollection->updated_at 		= date('Y-m-d H:i:s');
								if ($objAppointmentCollection->save())
								{
									$objAppointmentCollectionDetails 							= new AppointmentCollectionDetail;
									$objAppointmentCollectionDetails->collection_id 			= $objAppointmentCollection->collection_id;
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
									$objAppointmentCollectionDetails->created_at 				= date('Y-m-d H:i:s');
									$objAppointmentCollectionDetails->updated_by 				= 1;
									$objAppointmentCollectionDetails->updated_at 				= date('Y-m-d H:i:s');
									$objAppointmentCollectionDetails->save();
									$counter++;
								}
							}
						}
					}
				}
				$no_of_lines++;
			}
			echo "\r\n".$counter." -- Collection data imported successfully.\r\n";
		} else {
			echo "\r\nfile not found.\r\n";
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}

	public function GetProductCatAndQuality($productId)
	{
		$category_id 		= 0;
		$quality_id 		= 0;
		$SelectSql			= "	SELECT company_product_master.category_id
								FROM company_product_master
								WHERE company_product_master.id = ".intval($productId);
		$SelectRes  = DB::select($SelectSql);
		if (!empty($SelectRes)) {
			foreach ($SelectRes as $SelectRows) {
				$category_id = $SelectRows->category_id;
			}
		}
		$SelectSql			= "	SELECT company_product_quality_parameter.company_product_quality_id
								FROM company_product_quality_parameter
								WHERE company_product_quality_parameter.product_id = ".intval($productId);
		$SelectRes  = DB::select($SelectSql);
		if (!empty($SelectRes)) {
			foreach ($SelectRes as $SelectRows) {
				$quality_id = $SelectRows->company_product_quality_id;
			}
		}
		return array("category_id"=>$category_id,"quality_id"=>$quality_id);
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
}