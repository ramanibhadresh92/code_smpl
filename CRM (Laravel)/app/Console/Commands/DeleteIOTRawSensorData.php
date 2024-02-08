<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use DB;
class DeleteIOTRawSensorData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DeleteIOTRawSensorData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete IOT Sensor Raw Data 180 Days Old Logs';

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
     * @return int
     */
    public function handle()
    {
        echo "\r\n--StartTime::".date("Y-m-d H:i:s")."--\r\n";

        $DELETE_DATE    = date("Y-m-d",strtotime("-180 Days"))." 00:00:00";
        $RECORD_LIMIT   = 1000000;

        echo "\r\n--iot_sensor_raw_data StartTime::".date("Y-m-d H:i:s")."--\r\n";

        // $DELETE_SQL = "DELETE FROM iot_sensor_raw_data WHERE created_at <= '$DELETE_DATE' AND processed = 2 LIMIT $RECORD_LIMIT";

        // echo "\r\n--".$DELETE_SQL."--\r\n";

        // DB::statement(DB::raw($DELETE_SQL));

        echo "\r\n--net_suit_api_log_master StartTime::".date("Y-m-d H:i:s")."--\r\n";

        $DELETE_SQL = "DELETE FROM net_suit_api_log_master WHERE created_at <= '$DELETE_DATE' LIMIT $RECORD_LIMIT";

        echo "\r\n--".$DELETE_SQL."--\r\n";

        DB::statement(DB::raw($DELETE_SQL));

        echo "\r\n--appointment_request StartTime::".date("Y-m-d H:i:s")."--\r\n";

        $DELETE_SQL = " DELETE FROM appointment_request
                        WHERE created_date <= '$DELETE_DATE'
                        AND (request_respose = 'Success' OR request_respose = 'Success - Same Token')
                        AND request_status = 1
                        LIMIT $RECORD_LIMIT";

        echo "\r\n--".$DELETE_SQL."--\r\n";

        DB::statement(DB::raw($DELETE_SQL));

        echo "\r\n--appointment_request EndTime::".date("Y-m-d H:i:s")."--\r\n";

        echo "\r\n--appointment_pending_lead_request StartTime::".date("Y-m-d H:i:s")."--\r\n";

        $DELETE_SQL = " DELETE FROM appointment_pending_lead_request WHERE created_at <= '$DELETE_DATE' LIMIT $RECORD_LIMIT";

        echo "\r\n--".$DELETE_SQL."--\r\n";

        DB::statement(DB::raw($DELETE_SQL));

        echo "\r\n--appointment_pending_lead_request EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}