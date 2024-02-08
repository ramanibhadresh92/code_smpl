<?php

namespace App\Classes;
use DB;

class PushNotification {

    /**
    * Function Name : sendPush
    * @return
    * @author Sachin Patel
    * @date 03 May, 2019
    */
    public static function sendPush($Channel, $Event, $data){
        // $options = array(
        //     'cluster' => env('PUSHER_APP_CLUSTER'),
        //     'useTLS' => true
        // );

        // $pusher = new \Pusher\Pusher(
        //     env('PUSHER_APP_KEY'),
        //     env('PUSHER_APP_SECRET'),
        //     env('PUSHER_APP_ID'),
        //     $options
        // );

        // $checkpush =$pusher->trigger($Channel, $Event, $data);
    }
}

