<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;
use App\Models\AppointmentImages;
use App\Models\MediaMaster;
class SendAppointmentImageEmail implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	public $details;
	public $filePath;
	public $media;
	public $path;


	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($details)
	{

		$this->details = $details;
		// dd($this->details['message']);
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		try{
			$data = $this->details;
			$mail = \Mail::send([],[], function($message) use ($data)
			{
				$message->setBody($data['message'],'text/html');
				$message->from($data['FromEmail'],$data['FromName']);
				$message->to($data['toEmail']);
				$message->subject($data['subject']);
				$size           = sizeOf($data['appointment_image_id']);
				if(!empty($data['appointment_image_id'])){
					$AppointmentImages  = new AppointmentImages();
					$tableName          = $AppointmentImages->getTable();
					foreach($data['appointment_image_id'] as $filePath){
						$media = \DB::table($tableName)->where('id',$filePath)->first();
						if($media){
							$path   = public_path("/").$media->dirname."/".$media->filename;
							$message->attach($path, ['as' => basename($path),'mime' =>'']);
						}
					}
				}
			},true);
		}catch(\Exception $ex){
			return $ex;
		}

	}
}
