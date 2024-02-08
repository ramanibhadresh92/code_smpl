<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmServiceMaster;

class SendInvoiceToEPR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendInvoiceToEPR';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Service Invoices Data to EPR';

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
        // if(EPR_INVOICE_CRON_ENABLE){
            WmServiceMaster::SendInvoiceToEPR();
        // }
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}
