<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\BaseLocationMaster;
use App\Models\BaseLocationCityMapping;
use App\Models\AppointmentCollectionDetail;
use DB;

class CollectionNotUnloaded extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CollectionNotUnloaded';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Collection Not Unloaded Report';

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
        $days                       = 1;
        $CompanyMaster              = new CompanyMaster;
        $arrCompany                 = $CompanyMaster->select('company_id','company_name','company_email')->where('status','Active')->get();
        $StartTime                  = date("Y-m-d",strtotime("-$days day"))." 00:00:00";
        $EndTime                    = date("Y-m-d",strtotime("-$days day"))." 23:59:59";
        if (!empty($arrCompany)) 
        {
            foreach($arrCompany as $Company)
            {
                echo "\r\n--Company ID::".$Company->company_id." StartTime ::".$StartTime." EndTime ::".$EndTime."--\r\n";
                $BaseLocationDetails = BaseLocationMaster::GetCompanyBaseLocations($Company->company_id);
                if (!empty($BaseLocationDetails))
                {
                    foreach ($BaseLocationDetails as $BaseLocationDetail) 
                    {
                        $arrFilter          = array("AdminUserCompanyID"=>$Company->company_id,"city_id"=>explode(",",$BaseLocationDetail['Base_Location_City']));
                        $arrCollection      = AppointmentCollectionDetail::GetCollectionNotUnloaded($StartTime,$EndTime,false,$arrFilter);
                        if (!empty($arrCollection['collectionRows']['data']))
                        {
                            $ToEmail        = (!empty($BaseLocationDetail['Report_Email_Address'])?$BaseLocationDetail['Report_Email_Address']:COLLECTION_NOT_UNLOADED_TO_EMAIL);
                            $FromEmail      = array('Email'=>$Company->company_email,'Name'=>$Company->company_name);
                            $Subject        = $Company->company_name.' - '.$BaseLocationDetail['Base_Location'].' - Collection Not Unloaded for Date '._FormatedDate($StartTime,false,"d-M-Y");
                            AppointmentCollectionDetail::SendCollectionNotUnloadedEmail($arrCollection['collectionRows']['data'],$FromEmail,$ToEmail,$Subject);
                        }
                    }
                }
            }
        }
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}