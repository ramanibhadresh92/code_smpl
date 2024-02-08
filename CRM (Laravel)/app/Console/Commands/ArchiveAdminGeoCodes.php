<?php

namespace App\Console\Commands;

use App\Models\AdminGeoCode;
use Illuminate\Console\Command;

class ArchiveAdminGeoCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ArchiveAdminGeoCodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ArchiveAdminGeoCodes';

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
        AdminGeoCode::admin_geocodes_archive();
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}
