<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockLadger;
class UpdateSalesStockLadger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateSalesStockLadger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command use to update daily opening and closing stock also inward and outward of sales product';

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
        $data = StockLadger::UpdateSalesStock();
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}
