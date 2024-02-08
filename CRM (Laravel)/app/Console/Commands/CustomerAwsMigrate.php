<?php

namespace App\Console\Commands;

use App\Models\CustomerMaster;
use Illuminate\Console\Command;

class CustomerAwsMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customermigrateaws';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Customer Migrate Aws';

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
        CustomerMaster::migrateCustomerAws();
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}
