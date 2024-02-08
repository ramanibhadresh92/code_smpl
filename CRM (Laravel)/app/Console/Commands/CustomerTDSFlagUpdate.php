<?php

namespace App\Console\Commands;

use App\Models\AppointmentCollectionDetail;
use Illuminate\Console\Command;

class CustomerTDSFlagUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CustomerTDSFlagUpdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CustomerTDSFlagUpdate';

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
        AppointmentCollectionDetail::CheckForTDSTransactionAmount();
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}
