<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\WmDepartment;
use App\Models\WmBatchMaster;
use App\Models\BaseLocationMaster;
use App\Models\BaseLocationCityMapping;
use App\Models\AppointmentCollection;
use App\Models\AdminGeoCode;
use App\Models\Appoinment;
use App\Facades\LiveServices;
use Mail;
use DB;

class GenerateBatchKmData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GenerateBatchKmData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate Batch Km Data';

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
		$Day 			= "1";
		$StartTime      = date("Y-m-d",strtotime("-$Day days"))." 00:00:00";
		$EndTime        = date("Y-m-d",strtotime("-$Day days"))." 23:59:59";

		echo "\r\n--StartTime::".$StartTime." EndTime::".$EndTime."--\r\n";

		$CompanyMaster  			= new CompanyMaster;
		$WmDepartment  				= new WmDepartment;
		$BaseLocationCityMapping    = new BaseLocationCityMapping;
		$WmBatchMaster    			= new WmBatchMaster;
		$AppointmentCollection    	= new AppointmentCollection;
		$Appoinment    				= new Appoinment;
		$arrCompany     			= $CompanyMaster->select('company_id','company_name','company_email','certificate_logo')->where('status','Active')->get();
		if (!empty($arrCompany))
		{
			foreach($arrCompany as $Company)
			{
				$arrMRF     = $WmDepartment->select('id','department_name','location_id')
											->where('company_id',$Company->company_id)
											->where('status','1')->get();
				if (!empty($arrMRF))
				{
					foreach($arrMRF as $MRF)
					{
						// echo "\r\n--Company ID::".$Company->company_id." MRF ID::".$MRF->id."--\r\n";
						$arrBatchReport 		= $WmBatchMaster->select('batch_id','vehicle_id','collection_id')
																->where('master_dept_id',$MRF->id)
																->whereBetween("created_date",[$StartTime,$EndTime])
																->orderBy("batch_id","ASC")
																->get();
						if (!empty($arrBatchReport))
						{
							foreach($arrBatchReport as $BatchReport)
							{
								// echo "\r\n-- MRF ID::".$MRF->id." Batch ID::".$BatchReport->batch_id."--\r\n";
								$collectionIds 			= explode(",",$BatchReport->collection_id);
								$AppointmentCollection 	= AppointmentCollection::select(
															DB::raw("MIN(appoinment.app_date_time) AS Min_App_Date"),
															DB::raw("MAX(appoinment.app_date_time) AS Max_App_Date"),
															DB::raw("MIN(appointment_collection.collection_dt) AS Min_Coll_Date"),
															DB::raw("MAX(appointment_collection.collection_dt) AS Max_Coll_Date"))
															->leftjoin($Appoinment->getTable()." AS appoinment","appointment_collection.appointment_id","=","appoinment.appointment_id")
															->whereIn("appointment_collection.collection_id",$collectionIds)
															->first();
								if (!empty($AppointmentCollection)) {
									$Min_Date 	= (strtotime($AppointmentCollection->Min_App_Date) <= strtotime($AppointmentCollection->Min_Coll_Date))?$AppointmentCollection->Min_App_Date:$AppointmentCollection->Min_Coll_Date;
									$Max_Date 	= (strtotime($AppointmentCollection->Max_App_Date) >= strtotime($AppointmentCollection->Max_Coll_Date))?$AppointmentCollection->Max_App_Date:$AppointmentCollection->Max_Coll_Date;
									$Min_Date 	= date("Y-m-d H:i",strtotime($Min_Date." -15 minute")).":00";
									$Max_Date 	= date("Y-m-d H:i",strtotime($Max_Date." +15 minute")).":59";
									$AdminGeoCode 	= AdminGeoCode::select("lat","lon")
														->where("vehicle_id",$BatchReport->vehicle_id)
														->whereBetween("created_at",[$Min_Date,$Max_Date])
														->orderBy("created_at","ASC")
														->get();
									if (!empty($AdminGeoCode)) {
										$Total_Km 	= 0;
										$Prev_Lat 	= 0;
										$Prev_Lon 	= 0;
										foreach($AdminGeoCode as $Distance)
										{
											if (!empty($Prev_Lat) && !empty($Prev_Lon)) {
												$Total_Km += distance($Prev_Lat,$Prev_Lon,$Distance->lat,$Distance->lon);
											}
											$Prev_Lat = $Distance->lat;
											$Prev_Lon = $Distance->lon;
										}
										if ($Total_Km > 0) {
											echo "\r\n-- Batch ID::".$BatchReport->batch_id." Total KM::".round($Total_Km,2)."--\r\n";
											WmBatchMaster::where("batch_id",$BatchReport->batch_id)->update(["batch_total_km"=>round($Total_Km,2)]);
											// die;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}