<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AppointmentImagesSendEmailDetail;
use App\Models\Appoinment;
use App\Jobs\SendAppointmentImageEmail;
use Image;
class AppointmentImages extends Model
{
    protected 	$table 		=	'appointment_images';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public      $timestamps =   false;


    public function getFilenameAttribute($value)
    {
        return url('/'.$this->dirname."/".$value);
    }

    /*
    Use     :  Get appointment images
    Author  :  Axay Shah
    Date    :  19 Dec,2018
    */

    public static function getAppointmentImage($request){
        $data = self::select("*")->where("id","!=","0");
        if($request->has('appointment_id') && !empty($request->input('appointment_id')))
    	{
        	$data->whereIn('appointment_id', explode(",",$request->input('appointment_id')));
        }

        if($request->has('customer_id') && !empty($request->input('customer_id')))
    	{
        	$data->whereIn('customer_id', explode(",",$request->input('customer_id')));
        }

        if($request->has('foc_appointment') && $request->input('foc_appointment') == 1)
    	{
        	$data->where('foc_appointment',$request->input('foc_appointment'));
        }
        return $data->get();
    }

    /*
    Use     :  send email to customer contact for appointment images
    Author  :  Axay Shah
    Date    :  19 Dec,2018
    */

    public static function saveAppointmentImageEmailDetail($request){

        $imageIdsData =  $imageIds = $toEmail = "";
        if(isset($request->toEmail) && !empty($request->toEmail) && is_array($request->toEmail)){
            $toEmail = implode(',',$request->toEmail);
        }
        if(isset($request->appointment_image_id) && !empty($request->appointment_image_id) && is_array($request->appointment_image_id)){
            $imageIds       = self::select(\DB::raw('group_concat(id) as imageid'))->whereIn('id',$request->appointment_image_id)->get();
            if(!empty($imageIds)) $imageIdsData = $imageIds[0]->imageid;
        }
        $imageLog   =   AppointmentImagesSendEmailDetail::insert([
                        "appointment_id"			=>	$request->appointment_id,
                        "appointment_image_id"	    =>	$imageIdsData,
                        "contact_detail_email_id"	=>	$toEmail,
                        "subject"					=>	$request->subject,
                        "message"					=>	$request->message,
                        "created_by"				=>	Auth()->user()->adminuserid,
                        "created_date"			    =>	date("Y-m-d H:i:s")
                        ]);
                        log_action("Send_Appointment_Image_Email",$request->appointment_id,'appointment_images_send_email_detail');
                        /*Email send code remain - 19 Dec,2018*/
                        /*Email send in backgroup using queue*/
                        $data['toEmail'] = $toEmail;
                        $data['FromEmail'] = env('MAIL_FROM_ADDRESS');
                        $data['FromName'] = env('MAIL_FROM_NAME');
                        $data['message'] = $request->message;
                        $data['subject'] = $request->subject;
                        $data['appointment_image_id'] = $request->appointment_image_id;
                        SendAppointmentImageEmail::dispatch($data);
                        return true;
    }


    /*
    Use     :  Save Appointment Images
    Author  :  Axay Shah
    Date    :  08 Feb,2019
    */

    public static function saveAppointmentImages($request)
    {
        $data = array();
        $partialPath = PATH_COMPANY.'/'.Auth()->user()->company_id."/".Auth()->user()->city.'/'.PATH_APPOINTMENT_IMG;
        $path  =public_path(PATH_IMAGE.'/').$partialPath;
        if (!file_exists($path)) {
            \File::makeDirectory($path, $mode = 0777, true, true);
        }
        if(isset($request->image_data) && !empty($request->image_data))
        {
            $file                           = $request->file('image_data');
            $request->filename              = $request->customer_id."_".$request->appointment_id."_".rand(4,time()).time().'.'.$file->getClientOriginalExtension();
            $customer                       = new self();
            $customer->appointment_id       = (isset($request->appointment_id)      && !empty($request->appointment_id))      ? $request->appointment_id        : 0;
            $customer->customer_id          = (isset($request->customer_id)         && !empty($request->customer_id))         ? $request->customer_id           : 0;
            $customer->dirname              = PATH_IMAGE.'/'.$partialPath;
            $customer->filename             = (isset($request->filename)            && !empty($request->filename))            ? $request->filename              : '';
            $customer->verification_image   = (isset($request->verification_image)  && !empty($request->verification_image))  ? $request->verification_image    : 0;
            $customer->foc_appointment      = (isset($request->foc_appointment)     && !empty($request->foc_appointment))     ? $request->foc_appointment       : 0;
            $customer->for_invoice          = (isset($request->for_invoice)         && !empty($request->for_invoice))         ? $request->for_invoice           : 0;
            $customer->created_dt           = date('Y-m-d H:i:s');
            $customer->save();

            $imgName        = RESIZE_PRIFIX.$request->filename;
            $img            = Image::make($file->getRealPath());
            $img->resize(RESIZE_HIGHT, RESIZE_WIDTH, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path(PATH_IMAGE.'/').$partialPath.'/'.$imgName);
            $file->move(public_path(PATH_IMAGE.'/').$partialPath.'/', $request->filename);
            $data[] = $customer;
        }
        return $data;
    }

    /*
    Use     :  uploadAppointmentImage
    Author  :  Kalpak Prajapati
    Date    :  06 Jan,2021
    */
    public static function uploadAppointmentImage($Document,$customer_id=0,$appointment_id=0,$company_id=0,$city_id=0)
    {
        $data           = array();
        $partialPath    = PATH_COMPANY.'/'.$company_id."/".$city_id.'/'.PATH_APPOINTMENT_IMG;
        $path           = public_path(PATH_IMAGE.'/').$partialPath;
        if (!file_exists($path)) {
            \File::makeDirectory($path, $mode = 0777, true, true);
        }
        if(isset($Document) && !empty($Document))
        {
            $customer                       = self::where("appointment_id",$appointment_id)->where("for_invoice",1)->first();
            if(!$customer){
                $customer                   = new self();
            }
            $file                           = $Document;
            $filename                       = $customer_id."_".$appointment_id."_".rand(4,time()).time().'.'.$file->getClientOriginalExtension();
            $ext                            = $file->getClientOriginalExtension();
            $customer->appointment_id       = $appointment_id;
            $customer->customer_id          = $customer_id;
            $customer->dirname              = PATH_IMAGE.'/'.$partialPath;
            $customer->filename             = $filename;
            $customer->verification_image   = 0;
            $customer->foc_appointment      = 0;
            $customer->for_invoice          = 1;
            $customer->created_dt           = date('Y-m-d H:i:s');
            if($customer->save()){
                if($appointment_id > 0){
                    $data = Appoinment::where("appointment_id",$appointment_id)->update(["invoice_media_id"=>$customer->id]);
                }
            }
            $file->move(public_path(PATH_IMAGE.'/').$partialPath.'/', $filename);
            $StorePath      = public_path(PATH_IMAGE.'/').$partialPath;
            $Newfilename    = "new_".$filename;
            $NewPath        = public_path(PATH_IMAGE.'/').$partialPath.'/'.$Newfilename;
            $OldPath        = public_path(PATH_IMAGE.'/').$partialPath.'/'.$filename;
            if($ext == "pdf"){
                ConvertPDFVersion($NewPath,$OldPath);
                rename($NewPath,$StorePath."/".$filename);
            }
        }
        return $data;
    }
}
