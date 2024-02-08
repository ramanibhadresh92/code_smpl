<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmDispatch;

class SendDataToEPR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendDataToEPR';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Dispatch Data to EPR';

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
        // if(EPR_CRON_ENABLE){
            // return false;
            WmDispatch::CallEPRUrl();    
        // }
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}
