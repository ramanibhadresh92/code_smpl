<?php

namespace App\Models;

use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\VehicleDriverMappings;
use Illuminate\Support\Facades\Auth;
 
use App\Models\ViewCustomerMaster;
use App\Models\InertDeduction;
use App\Models\CustomerMaster;
use App\Models\CustomerAvgCollection;
use App\Models\AppointmentTimeReport;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use App\Models\AppointmentNotification;
use App\Models\CompanyCategoryMaster;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductQualityParameter;
use App\Classes\PartnerAppointment;
use App\Classes\SendSMS;
use App\Models\AdminUser;
use App\Models\ViewAppointmentList;
use App\Models\LocationMaster;
use App\Models\StateMaster;
use App\Models\CountryMaster;
use App\Models\Parameter;
use App\Models\UserCityMpg;
use App\Models\DailyPurchaseSummary;
use App\Models\DailySalesSummary;
use App\Models\AppointmentRunningLate;
use App\Models\CompanyMaster;
use App\Models\FocAppointment;
use App\Models\FocAppointmentStatus;
use App\Models\AppointmentImages;
use App\Models\BaseLocationCityMapping;
use App\Models\UserBaseLocationMapping;
use Mail;
use DB;
use Log;
use PDF;
use setasign\Fpdi\Fpdi;
class WmDailySalesProductionReportDetails extends Model
{
	protected 	$table 		=	'wm_daily_sales_production_report_details';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   false;
	  

	public static function addDetails($id,$date,$qty=0){
		$record_id 			= 0;
		$self 				=  new self;
		$self->record_id 	= $id;
		$self->qty 			= $qty;
		$self->date 		= $date ;
		if($self->save()){
			$record_id = $self->id;
		}
		return $record_id;
	}
	
}