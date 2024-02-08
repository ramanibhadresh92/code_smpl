<?php

namespace App\Console\Commands;

use App\Models\NetSuitStockLedger;
use Illuminate\Console\Command;
class StoreNetSuitStockLedger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'StoreNetSuitStockLedger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'command use for store stock ledger data to net suit ';

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
        NetSuitStockLedger::StoreStockForNetSuit();
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}
