<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\CustomerMaster;

class UpdateCustomerAverageCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateCustomerAverageCollectionData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Customer Average Collection Based on Last 5 transaction details';

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
        CustomerMaster::UpdateCustomerAverageCollection();
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}