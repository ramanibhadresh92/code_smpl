<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\AppointmentCollectionDetail;

class GenerateInvestoreReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendReportToInvestor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Daily Summary Report to Investor';

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
        
        // $StartTime      = date("Y-m-d",strtotime("-2 days"))." 00:00:00";
        // $EndTime        = date("Y-m-d",strtotime("-2 days"))." 23:59:59";

        $StartTime      = date("Y-m-d")." 00:00:00";
        $EndTime        = date("Y-m-d")." 23:59:59";

        echo "\r\n--Report StartTime::".$StartTime."--\r\n";
        echo "\r\n--Report EndTime::".$EndTime."--\r\n";

        $CompanyMaster  = new CompanyMaster;
        $arrCompany     = $CompanyMaster->select('company_id','company_name','company_email','certificate_logo')->where('status','Active')->get();
        if (!empty($arrCompany))
        {
            foreach($arrCompany as $Company)
            {
                $arrAttachemnts     = array();
                $arrCollectionPDF = AppointmentCollectionDetail::GetCollectionDetailByRange($Company,$StartTime,$EndTime,true);
                if (!empty($arrCollectionPDF) && file_exists($arrCollectionPDF)) {
                    array_push($arrAttachemnts,$arrCollectionPDF);
                }
                $arrSalesPDF = AppointmentCollectionDetail::GetSalesDetailByRange($Company,$StartTime,$EndTime,true);
                if (!empty($arrSalesPDF) && file_exists($arrSalesPDF)) {
                    array_push($arrAttachemnts,$arrSalesPDF);
                }
                AppointmentCollectionDetail::SendInvestorSummaryEmail($Company,$arrAttachemnts,$StartTime,$EndTime);
            }
        }
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}