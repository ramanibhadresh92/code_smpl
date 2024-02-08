<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Models\BailStockLedger;
class UpdateBailStockLadger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateBailStockLadger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command use to update daily opening and closing stock of bail also inward and outward of product';

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
        $data = BailStockLedger::BailUpdateStock();
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}
