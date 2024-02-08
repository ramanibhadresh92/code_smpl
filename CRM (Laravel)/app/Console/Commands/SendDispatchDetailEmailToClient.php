<?php

namespace App\Console\Commands;

use App\Models\WmDispatch;
use Illuminate\Console\Command;

class SendDispatchDetailEmailToClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendDispatchDetailEmailToClient';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email sent daily basis to client when dispatch get approved';

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
        WmDispatch::SendDispatchDetailEmailToClient();
        echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
    }
}
