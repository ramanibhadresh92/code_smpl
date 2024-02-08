<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appoinment;

class GenerateDailySummaryReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendDailySummaryReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collection and Sales Summary Report';

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
        $Appoinment->SendDailySummaryReport();
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}