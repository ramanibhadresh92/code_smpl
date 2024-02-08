<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appoinment;

class SaveDailySummaryData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SaveDailySummaryReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save Daily Summary Data';

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
        $Appoinment = new Appoinment;
        $Today      = date("Y-m-d");
        $StartDate  = date("Y-m-d");
        $ReportDate = $StartDate;
        while(strtotime($ReportDate) <= strtotime($Today)) {
            echo "\r\n--ReportDate::".$ReportDate."--\r\n";
            $Appoinment->SaveDailySummaryReport($ReportDate);
            $ReportDate = date("Y-m-d",strtotime($ReportDate . ' +1 day'));
        }
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}
