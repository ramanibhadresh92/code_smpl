<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmDispatch;
use App\Facades\LiveServices;
// use curl;
class StoreEwayBillStrickAmountCopyForEPR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'StoreEwayBillStrickAmountCopyForEPR';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command use strick amount of eway bill and store that eway bill in dispatch document';

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
        WmDispatch::DispatchDocumentReduction();   
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}
