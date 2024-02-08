<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerMaster;
use App\Models\Appoinment;
use App\Models\VehicleMaster;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use App\Models\AppointmentRequest;
use App\Models\AppointmentTimeReport;
use Mail;
use DB;

class InsertMetaData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'InsertMetaData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To INSERT META DATA FOR various reports';

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

		$MAX_COLLECTION_DETAILS_ID = 0;

		$SelectSql 	= "SELECT collection_detail_id FROM lr_ejbi_data.lr_ejbi_meta_data ORDER BY collection_detail_id DESC limit 1";
		$SelectRes  = DB::connection('META_DATA_CONNECTION')->select($SelectSql);
		if (!empty($SelectRes) && isset($SelectRes[0]->collection_detail_id)) {
			$MAX_COLLECTION_DETAILS_ID = $SelectRes[0]->collection_detail_id;
		}
		if (!empty($MAX_COLLECTION_DETAILS_ID)) {
			$InsertMetaData	= "INSERT INTO lr_ejbi_meta_data
							(
								SELECT
									appointment_collection_details.collection_detail_id,
									appointment_collection_details.category_id,
									category_master.category_name,
									appointment_collection_details.product_id,
									product_master.name AS product_name,
									appointment_collection_details.product_quality_para_id,
									product_master.foc_product,
									product_quality_parameter.parameter_name,
									product_price_details.factory_price AS factory_price,
									appointment_collection_details.product_customer_price,
									appointment_collection_details.quantity,
									appointment_collection_details.price,
									appointment_collection.collection_id,
									appointment_collection.appointment_id,
									appoinment.foc,
									appoinment.customer_id,
									CONCAT(customer_master.first_name,' ',customer_master.last_name) AS customer_name,
									customer_master.code,
									appointment_collection.collection_by,
									CONCAT(adminuser.firstname,' ',adminuser.lastname) AS appointment_collection_by,
									appointment_collection.collection_dt,
									appoinment.app_date_time,
								  	customer_group.para_value AS customer_group,
								  	customer_price_group.group_value AS customer_price_group,
								  	customer_route.para_value AS customer_route,
								  	custtype.para_value AS type_of_customer,
								  	(CASE customer_master.collection_type WHEN 3 THEN 'FOC+PAID' WHEN 2 THEN 'PAID' WHEN 1 THEN 'FOC' END ) AS collection_type_of_customer,
									customer_master.collection_frequency AS collection_frequency,
									para_payment_mode.para_value AS payment_mode,
									cust_ref.para_value AS referred_by,
									customer_master.frequency_per_day AS collection_per_day,
									city_master.city AS customer_city,
									state_master.state_name AS customer_state,
									app_reach_time.endtime app_reached_time,
									app_done_time.endtime app_completed_time,
									customer_master.longitude AS customer_longitude,
									customer_master.lattitude AS customer_lattitude,
									(IF(appoinment.longitude = 0,customer_master.longitude,appoinment.longitude)) AS appointment_longitude,
									(IF(appoinment.lattitude = 0,customer_master.lattitude,appoinment.lattitude)) AS appointment_lattitude,
									ROUND(
								  	(
								    	111.1111 * DEGREES(
								      	ACOS(
								        	COS(
								          		RADIANS(customer_master.lattitude)
								        		) * COS(
								          				RADIANS(
								            (
								              IF(
								                appoinment.lattitude = 0,
								                customer_master.lattitude,
								                appoinment.lattitude
								              )
								            )
								          )
								        ) * COS(
								          RADIANS(
								            customer_master.longitude -(
								              IF(
								                appoinment.longitude = 0,
								                customer_master.longitude,
								                appoinment.longitude
								              )
								            )
								          )
								        ) + SIN(
								          RADIANS(customer_master.lattitude)
								        ) * SIN(
								          RADIANS(
								            (
								              IF(
								                appoinment.lattitude = 0,
								                customer_master.lattitude,
								                appoinment.lattitude
								              )
								            )
								          )
								        )
								      )
								    )
								  ),
								  2
								) AS distance_in_km,
								CONCAT(App_Created_By.firstname,' ',App_Created_By.lastname) AS appointment_given_by,
								letsrecycle_backoffice.customer_master.cust_group,
								letsrecycle_backoffice.customer_master.ctype,
								letsrecycle_backoffice.customer_master.city,
								letsrecycle_backoffice.customer_master.company_id,
								letsrecycle_backoffice.appoinment.vehicle_id,
								letsrecycle_backoffice.vehicle_master.vehicle_number

								FROM
								  letsrecycle_backoffice.appointment_collection_details
								INNER JOIN
								  letsrecycle_backoffice.company_category_master as category_master ON category_master.id = appointment_collection_details.category_id
								INNER JOIN
								  letsrecycle_backoffice.company_product_master product_master ON product_master.id = appointment_collection_details.product_id
								INNER JOIN
								  letsrecycle_backoffice.company_product_quality_parameter as product_quality_parameter ON product_quality_parameter.company_product_quality_id = appointment_collection_details.product_quality_para_id
								INNER JOIN
								  letsrecycle_backoffice.appointment_collection ON appointment_collection.collection_id = appointment_collection_details.collection_id
								INNER JOIN
								  letsrecycle_backoffice.appoinment ON appoinment.appointment_id = appointment_collection.appointment_id
								INNER JOIN
								  letsrecycle_backoffice.customer_master ON customer_master.customer_id = appoinment.customer_id
								INNER JOIN
								  letsrecycle_backoffice.adminuser ON adminuser.adminuserid = appointment_collection.collection_by
								INNER JOIN
								  letsrecycle_backoffice.company_product_price_details product_price_details ON product_price_details.para_waste_type_id = customer_master.price_group AND product_price_details.product_id = appointment_collection_details.product_id
								LEFT JOIN
								  letsrecycle_backoffice.adminuser AS App_Created_By ON App_Created_By.adminuserid = appoinment.created_by
								LEFT JOIN
								  letsrecycle_backoffice.vehicle_master ON appoinment.vehicle_id = vehicle_master.vehicle_id
								LEFT JOIN
								  letsrecycle_backoffice.company_parameter AS customer_group ON customer_group.para_id = customer_master.cust_group
								LEFT JOIN
								  letsrecycle_backoffice.company_price_group_master AS customer_price_group ON customer_price_group.id = customer_master.price_group
								LEFT JOIN
								  letsrecycle_backoffice.company_parameter AS customer_route ON customer_route.para_id = customer_master.route
								LEFT JOIN
								  letsrecycle_backoffice.parameter AS custtype ON custtype.para_id = customer_master.ctype
								LEFT JOIN
								  letsrecycle_backoffice.parameter AS para_payment_mode ON para_payment_mode.para_id = customer_master.para_payment_mode_id
								LEFT JOIN
								  letsrecycle_backoffice.parameter AS cust_ref ON cust_ref.para_id = customer_master.para_referral_type_id
								LEFT JOIN
								  letsrecycle_backoffice.location_master AS city_master ON city_master.location_id = customer_master.city
								LEFT JOIN
								  letsrecycle_backoffice.state_master ON state_master.state_id = customer_master.state
								LEFT JOIN
								  letsrecycle_backoffice.appointment_time_report AS app_done_time ON app_done_time.appointment_id = appoinment.appointment_id AND app_done_time.para_report_status_id = 9003
								LEFT JOIN
								  letsrecycle_backoffice.appointment_time_report AS app_reach_time ON app_reach_time.appointment_id = appoinment.appointment_id AND app_reach_time.para_report_status_id = 9002
								WHERE appointment_collection_details.collection_detail_id > $MAX_COLLECTION_DETAILS_ID
								GROUP BY appointment_collection_details.collection_detail_id
							)";
			$SelectRes  = DB::connection('META_DATA_CONNECTION')->statement($InsertMetaData);
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}