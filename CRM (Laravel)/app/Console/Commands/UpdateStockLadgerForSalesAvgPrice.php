<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmBatchMaster;
class UpdateStockLadgerForSalesAvgPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateStockLadgerForSalesAvgPrice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command use to update sales stock avg price';

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
        $data = WmBatchMaster::UpdateSalesProductStockAvgPriceV2();
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}
