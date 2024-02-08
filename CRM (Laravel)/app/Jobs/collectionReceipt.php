<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;
use Log;
class collectionReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $details;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        
        $this->details = $details;
        // dd($this->details['filePath']);;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->details;
        try{
            $mail = \Mail::send([],[], function($message) use($data)
            {
                $message->setBody($data['message'],'text/html'); 
                $message->from($data['fromEmail'],$data['fromName']);
                $message->to($data['to']);
                $message->subject($data['subject']);
                $message->attach($data['filePath'], [
                    'as' => $data['filename'], 
                    'mime' => $data['mime']
                ]);
            });
            $to = "";
            if(!empty($data['to'])){
                $to = implode(",",$data['to']);
            }
            if (file_exists($data['filePath'])) @unlink($data['filePath']);
            $LogRemarks = "Appointment Invoice Email Sent to customer on ".$to;
            log_action('Appointment_Updated',$data['appointment_id'],"appoinment",false,$LogRemarks);
        }catch(\Exception $ex){
            $to = "";
            if(!empty($data['to'])){
                $to = implode(",",$data['to']);
            }
            $LogRemarks = "Appointment Invoice Email Sent Failed to customer on ".$to;
            log_action('Appointment_Updated',$data['appointment_id'],"appoinment",false,$LogRemarks); 
        }
    }
}
