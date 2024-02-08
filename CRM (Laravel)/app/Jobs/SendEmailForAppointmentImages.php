<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;
use App\Models\AppointmentImages;
class SendEmailForAppointmentImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
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
