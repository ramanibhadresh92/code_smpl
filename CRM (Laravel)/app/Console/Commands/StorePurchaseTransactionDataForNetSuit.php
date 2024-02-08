<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NetSuitPurchaseTransactionMaster;
class StorePurchaseTransactionDataForNetSuit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'StorePurchaseTransactionDataForNetSuit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command use to store puchase data transaction for net suit in LR system';

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
        $data = NetSuitPurchaseTransactionMaster::StorePurchaseTransactionDataForNetSuit();
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}
