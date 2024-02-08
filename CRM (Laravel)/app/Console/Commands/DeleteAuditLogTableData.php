<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Audits;
class DeleteAuditLogTableData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DeleteAuditLogTableData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command use to delete Audit Log Table Data';

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
        $from_Id    = Audits::min('id');
        $to_Id      = $from_Id + 10000;
        $data       = Audits::where('id','<', $to_Id)->delete();
     
    }
}
