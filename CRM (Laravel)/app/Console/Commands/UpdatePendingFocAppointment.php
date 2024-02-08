<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Auth;
use App\Models\FocAppointmentStatus;
use App\Models\FocAppointment;
use App\Models\AdminUser;
use App\Models\AppointmentCollection;
use App\Facades\LiveServices;
class UpdatePendingFocAppointment extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'updatefocappointment';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'update appointment after finilize';

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
		$CheckDate  = date("Y-m-d");
		$startTime  = $CheckDate." 00:00:00";
		$endTime    = $CheckDate." 23:59:59";
		echo "\r\n--StartTime::".date("Y-m-d H:i:s")."--\r\n";
		$FocList    =   FocAppointment::select("foc_appointment.appointment_id","foc_appointment.collection_by",
						\DB::raw("CASE WHEN 1=1 THEN
						(
							SELECT count(0) from foc_appointment_status
							where foc_appointment_status.appointment_id = foc_appointment.appointment_id
							GROUP BY foc_appointment_status.appointment_id
						) END AS COMPLETED_CNT"),
						\DB::raw("CASE WHEN 1=1 THEN
						(
							SELECT count(0) from customer_master
							where customer_master.route = foc_appointment.route
							and customer_master.longitude != 0
							AND customer_master.collection_type IN (".COLLECTION_TYPE_FOC.",".COLLECTION_TYPE_FOC_PAID.")
							GROUP BY customer_master.route
						) END AS ROUTE_CUSTOMER_CNT"),
						\DB::raw("CASE WHEN 1=1 THEN
						(
							SELECT count(0) from customer_master
							where customer_master.route = foc_appointment.route
							and customer_master.longitude != 0
							AND customer_master.collection_type IN (".COLLECTION_TYPE_FOC.",".COLLECTION_TYPE_FOC_PAID.") 
							AND customer_master.slab_id > 0 
							GROUP BY customer_master.route
						) END AS SLAB_CUSTOMER_CNT"))
					->whereBetWeen("foc_appointment.app_date_time",[$startTime,$endTime])
					->where("foc_appointment.complete","0")
					->orderBy("foc_appointment.app_date_time","ASC")
					->having(\DB::raw("COMPLETED_CNT"),'>=',\DB::raw("ROUTE_CUSTOMER_CNT"))
					->having(\DB::raw("SLAB_CUSTOMER_CNT"),'<=',0)
					->get();
		if(!empty($FocList))
		{
			foreach($FocList as $foc){

				if($foc->COMPLETED_CNT  >= $foc->ROUTE_CUSTOMER_CNT){
					$Admin      = AdminUser::find($foc->collection_by);
					Auth::login($Admin);
					$FocStatus 					= FocAppointmentStatus::where("appointment_id",$foc->appointment_id)->where("reach",1)->orderBy("created_date","DESC")->first();
					$request 					= new \Request();
					$request->appointment_id    = $FocStatus->appointment_id;
					$request->longitude         = $FocStatus->longitude;
					$request->latitude          = $FocStatus->latitude;
					$request->collection_remark = $FocStatus->collection_remark;
					$request->reach_time        = $FocStatus->reach_time;
					$request->complete_time     = $FocStatus->complete_time;
					$request->reach             = $FocStatus->reach;
					$request->customer_id       = $FocStatus->customer_id;
					$request->action_name       = "SAVE_FOC_APPOINTMENT";
					$request->collection_qty    = $FocStatus->collection_qty;
					$data = AppointmentCollection::addFocAppointmentStatus($request);
				}
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}
