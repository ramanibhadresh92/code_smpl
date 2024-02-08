<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\BaseLocationMaster;
use App\Models\BaseLocationCityMapping;
use App\Models\WorkComplain;

class WGNAReport extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'WGNAReport';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'WGNA Report';

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
		$CompanyMaster          	= new CompanyMaster;
		$BaseLocationMaster     	= new BaseLocationMaster;
		$BaseLocationCityMapping    = new BaseLocationCityMapping;
		$WorkComplain    			= new WorkComplain;
		$arrCompany             	= $CompanyMaster->select('company_id','company_name','company_email')->where('status','Active')->get();
		$StartTime              	= date("Y-m-d",strtotime("-7 day"))." 00:00:00";
		$EndTime                	= date("Y-m-d")." 23:59:59";
		if (!empty($arrCompany))
		{
			foreach($arrCompany as $Company)
			{
				$BaseLocations  = $BaseLocationMaster->select('id','base_location_name','contact_email_address')
													->where('company_id',$Company->company_id)
													->get();
				if (!empty($BaseLocations))
				{
					foreach($BaseLocations as $BaseLocation)
					{
						$BaseLocationCity       		= $BaseLocationCityMapping->select('city_id')
																	->where('company_id',$Company->company_id)
																	->where('base_location_id',$BaseLocation->id)
																	->pluck('city_id')
																	->toArray();
						$arrFilter['city_id']			= $BaseLocationCity;
						$arrFilter['company_id']		= $Company->company_id;
						$arrFilter['StartTime']			= $StartTime;
						$arrFilter['EndTime']			= $EndTime;
						$arrFilter['COMPANY_NAME']		= $Company->company_name;
						$Attachments 					= WorkComplain::GetWorkComplainData($arrFilter);
						$ToEmail 						= "kalpak@yugtia.com";
						if (!empty($Attachments) && !empty($BaseLocation->contact_email_address))
						{
							$ToEmail        = !empty($BaseLocation->contact_email_address)?$BaseLocation->contact_email_address:$ToEmail;
							$FromEmail      = array('Email'=>$Company->company_email,'Name'=>$Company->company_name);
							$Subject        = $Company->company_name.' - '.$BaseLocation->base_location_name.' - WGNA Report From '._FormatedDate($StartTime,false,"d-M-Y")." To "._FormatedDate($EndTime,false,"d-M-Y");
							$Message 		= "	Hello All,<br />
												Please find attached $Subject.<br />
												Thanks,<br />
												LETS RECYCLE Admin";
							WorkComplain::SendWGNAReportEmail($Message,$Attachments,$FromEmail,$ToEmail,$Subject);
						}
					}
				}
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}