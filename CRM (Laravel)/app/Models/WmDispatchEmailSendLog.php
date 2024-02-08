<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmDispatchEmailSendLog extends Model
{
    protected 	$table 		=	'wm_dispatch_email_send_log';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public      $timestamps =   true;
    /*
    Use     : Store Email send log for dispatch
    Author  : Axay Shah
    Date    : 03 Auguest,2023
    */
    public static function StoreEmailSentLogForDispatch($emails,$dispatch_id,$client_id)
    {
        $save                   =  new self;
        $save->email_address    =  $emails;
        $save->client_id        =  $client_id;
        $save->dispatch_id      =  $dispatch_id;
        if($save->save()){
            return $save->id;
        }
        return 0;
    }
}
