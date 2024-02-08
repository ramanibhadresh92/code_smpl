<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CFMAttendanceMaster;
class CFMAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CFMAttendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update LR Driver and supervisor attendance in CFM project';

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
        $data = CFMAttendanceMaster::GetAttendanceForCFM();
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}
