<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmClientMaster;
use App\Models\ShippingAddressMaster;
use App\Models\LocationMaster;
class MergeDuplicateVendor extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'MergeDuplicateVendor';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Merge Duplicate Vendor Details';

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

		/*$arrMergeClients 	= array("CL-00761"=>"CL-00762",
									"CL-00770"=>"CL-00771,CL-01096",
									"CL-00780"=>"CL-00781",
									"CL-00755"=>"CL-00829",
									"CL-01005"=>"CL-01033,CL-01074,CL-01082,CL-01083,CL-01084,CL-01085,CL-01086,CL-01087,CL-01089,CL-01090,CL-01092",
									"CL-01075"=>"CL-01076,CL-01077",
									"CL-00804"=>"CL-00805,CL-00846",
									"CL-00945"=>"CL-00980,CL-00981,CL-00982,CL-00983,CL-00984,CL-00985,CL-00986,CL-00987,CL-00988,CL-00989,CL-00990,CL-00991,CL-00992,CL-00993,CL-00994,CL-00995,CL-00996,CL-00997");*/
		$arrMergeClients 	= array("CL-00770"=>"CL-00771,CL-01096",
									"CL-00780"=>"CL-00781",
									"CL-00755"=>"CL-00829",
									"CL-01005"=>"CL-01033,CL-01074,CL-01082,CL-01083,CL-01084,CL-01085,CL-01086,CL-01087,CL-01089,CL-01090,CL-01092",
									"CL-01075"=>"CL-01076,CL-01077",
									"CL-00804"=>"CL-00805,CL-00846",
									"CL-00945"=>"CL-00980,CL-00981,CL-00982,CL-00983,CL-00984,CL-00985,CL-00986,CL-00987,CL-00988,CL-00989,CL-00990,CL-00991,CL-00992,CL-00993,CL-00994,CL-00995,CL-00996,CL-00997");
		foreach ($arrMergeClients as $LR_CLIENT_CODE => $DUPLICATE_CODE)
		{
			$arrClientCodes	= explode(",",$DUPLICATE_CODE);
			$MainClientID 	= WmClientMaster::where("wm_client_master.code",$LR_CLIENT_CODE)->select("id")->first();
			if (isset($MainClientID->id) && !empty($MainClientID->id))
			{
				$ClientMasters 	= WmClientMaster::whereIn("wm_client_master.code",$arrClientCodes)->where("status","A")->get();
				if (!empty($ClientMasters))
				{
					foreach ($ClientMasters as $ClientMaster)
					{
						if (!empty($ClientMaster->city_id)) {
							$CityMaster = LocationMaster::where("location_id",$ClientMaster->city_id)->select("city","state")->first();
							$city 		= (isset($CityMaster->city) && !empty($CityMaster->city))?ucfirst($CityMaster->city):"";
							$state 		= (isset($CityMaster->state) && !empty($CityMaster->state))?ucfirst($CityMaster->state):"";
						} else {
							$city 		= "";	
							$state 		= "";	
						}
						$client_id 			= $MainClientID->id;
						$consignee_name 	= $ClientMaster->client_name;
						$oriaddress 		= str_replace(' ', '-',$ClientMaster->address);  // Replaces all spaces with hyphens.
						$NewAddress			= preg_replace("/[^a-zA-Z0-9]/","",$oriaddress);	// Removes special chars.
				   		$EncodedAddress 	= base64_encode($NewAddress);
						$shipping_address 	= $ClientMaster->address;
						$city_id 			= $ClientMaster->city_id;
						$state_code 		= $ClientMaster->gst_state_code;
						$pincode 			= $ClientMaster->pincode;
						$gst_no 			= $ClientMaster->gstin_no;
						$company_id 		= $ClientMaster->company_id;
						$created_by 		= $ClientMaster->created_by;
						$updated_by 		= $ClientMaster->updated_by;
						$created_at 		= date("Y-m-d H:i:s");
						$updated_at 		= date("Y-m-d H:i:s");

						$ShippingAddressMaster 						= new ShippingAddressMaster;
						$ShippingAddressMaster->client_id 			= $client_id;
						$ShippingAddressMaster->consignee_name 		= $consignee_name;
						$ShippingAddressMaster->billing_address 	= 1;
						$ShippingAddressMaster->shipping_address 	= $shipping_address;
						$ShippingAddressMaster->encoded_address 	= $EncodedAddress;
						$ShippingAddressMaster->city_id 			= $city_id;
						$ShippingAddressMaster->city 				= $city;
						$ShippingAddressMaster->state 				= $state;
						$ShippingAddressMaster->state_code 			= $state_code;
						$ShippingAddressMaster->pincode 			= $pincode;
						$ShippingAddressMaster->gst_no 				= $gst_no;
						$ShippingAddressMaster->company_id 			= $company_id;
						$ShippingAddressMaster->created_by 			= $created_by;
						$ShippingAddressMaster->updated_by 			= (empty($updated_by)?$created_by:$updated_by);
						$ShippingAddressMaster->created_at 			= $created_at;
						$ShippingAddressMaster->updated_at 			= $updated_at;
						$ShippingAddressMaster->save();
						if (isset($ShippingAddressMaster->id) && !empty($ShippingAddressMaster->id)) {
							WmClientMaster::where("id",$ClientMaster->id)->update(["status"=>"I","merged_client_id"=>$MainClientID->id]);
						}
					}
				}
			}
		}		
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}