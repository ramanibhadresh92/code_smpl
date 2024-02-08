<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\WmDepartment;
use App\Models\AppointmentCollectionDetail;
use App\Models\BaseLocationMaster;
use App\Models\BaseLocationCityMapping;

class CollectionVarianceReport extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'SendCollectionVarianceReport';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send Collection Variance Report';

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
		$CompanyMaster              = new CompanyMaster;
		$WmDepartment               = new WmDepartment;
		$BaseLocationCityMapping    = new BaseLocationCityMapping;

		$arrCompany     = $CompanyMaster->select('company_id','company_name','company_email')->where('status','Active')->get();
		$StartTime      = date("Y-m-d",strtotime("-2 Days"))." 00:00:00";
		$EndTime        = date("Y-m-d",strtotime("-2 Days"))." 23:59:59";
		$ToEmail        = COLLECTION_VARIANCE_EMAIL_TO;
		if (!empty($arrCompany))
		{
			foreach($arrCompany as $Company)
			{
				$arrMRF     = $WmDepartment->select('id','department_name','location_id')
											->where('company_id',$Company->company_id)
											->where('status','1')
											->get();
				if (!empty($arrMRF))
				{
					foreach($arrMRF as $MRF)
					{
						echo "\r\n--Company ID::".$Company->company_id." MRF ID::".$MRF->id."--\r\n";
						$BaseLocation       = $BaseLocationCityMapping->select('base_location_id')
																		->where('company_id',$Company->company_id)
																		->where('city_id',$MRF->location_id)
																		->first();
						if (isset($BaseLocation->base_location_id))
						{
							$BaseLocationDetails    = BaseLocationMaster::find($BaseLocation->base_location_id);
							$BaseLocationCity       = $BaseLocationCityMapping->select('city_id')
																			->where('company_id',$Company->company_id)
																			->where('base_location_id',$BaseLocation->base_location_id)
																			->pluck('city_id')
																			->toArray();
							$arrVarianceReport = AppointmentCollectionDetail::GetCollectionVarianceForEmail($Company->company_id,$MRF->id,$StartTime,$EndTime,$BaseLocationCity);
							if (!empty($arrVarianceReport) && !empty($BaseLocationDetails))
							{
								$ToEmail        = !empty($BaseLocationDetails->contact_email_address)?$BaseLocationDetails->contact_email_address:$ToEmail;
								$FromEmail      = array('Email'=>$Company->company_email,'Name'=>$Company->company_name);
								$Subject        = $Company->company_name.' - '.$BaseLocationDetails->base_location_name.' - Collection Variance Report Of '.$MRF->department_name.' for Date '._FormatedDate($StartTime,false,"d-M-Y");
								AppointmentCollectionDetail::SendCollectionVarianceEmail($arrVarianceReport,$FromEmail,$ToEmail,$Subject);
							}
						}
					}
				}
				echo "\r\n--Company ID::".$Company->company_id." All MRF--\r\n";
				$ToEmail 			= COLLECTION_VARIANCE_EMAIL_TO;
				$arrVarianceReport  = AppointmentCollectionDetail::GetCollectionVarianceForEmail($Company->company_id,"",$StartTime,$EndTime,array());
				if (!empty($arrVarianceReport))
				{
					$FromEmail      = array('Email'=>$Company->company_email,'Name'=>$Company->company_name);
					$Subject        = $Company->company_name.' - Collection Variance Report (ALL MRF) for Date '._FormatedDate($StartTime,false,"d-M-Y");
					AppointmentCollectionDetail::SendCollectionVarianceEmail($arrVarianceReport,$FromEmail,$ToEmail,$Subject);
				}
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}