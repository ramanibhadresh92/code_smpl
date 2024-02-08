<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApiDataLogger;
class DeleteApiLogTableData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DeleteApiLogTableData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command use to delete API Log Table Data';

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
        $DELETE_DATE    = date('Y-m-d', strtotime(' -2 day'))." ".GLOBAL_START_TIME;
        $data           = ApiDataLogger::where("created_at","<",$DELETE_DATE)->delete();
     
    }
}
