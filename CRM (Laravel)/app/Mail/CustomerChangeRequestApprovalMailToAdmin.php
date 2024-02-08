<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
// use PDF;
class CustomerChangeRequestApprovalMailToAdmin extends Mailable
{
    use Queueable, SerializesModels;
    protected $event;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        // $data = $this->event->decoded['old_filed'];
        // $data = array(
        //     'company_name'      => $this->event->company_name,
        //     'old_value'         => (object)$this->event->decoded['old_filed'],
        //     'new_value'         => (object)$this->event->decoded['new_filed'],
        //     'customer_id'       => $this->event->customer_id,
        //     'customer_name'     => $this->event->Customer_name,
        //     'mobile_no'         => $this->event->mobile_no,
        //     'code'              => $this->event->code,
        //     'owner_mobile_no'   => $this->event->owner_mobile_no,
        //     'owner_name'        => $this->event->owner_name,
        //     'vehicle_number'    => $this->event->vehicle_number,
        //     'vehicle_id'        => $this->event->vehicle_id,
        //     'module_id'         => $this->event->module_id
        // );
        $data = $this->event;
        $data['old_filed'] = $data->decoded['old_filed'];
        $data['new_filed'] = $data->decoded['new_filed'];
        foreach($data->decoded['old_filed'] as $key=>$value){
            $data['old_'.$key] = $value;
        }
        foreach($data->decoded['new_filed'] as $key=>$value){
            $data['new_'.$key] = $value;
        }

        // dd($data->old_vehicle_number);
        view()->share('data',$data);
        // $pdf = PDF::loadView('pdf.datachange')->setPaper("letter","portrait")->save(public_path().'/check.pdf');
                // 'old_value'         => (object)$this->event->decoded['old_filed'],
                // 'new_value'         => (object)$this->event->decoded['new_filed'],
                // 'customer_id'       => $this->event->customer_id,
                // 'customer_name'     => $this->event->Customer_name,
                // 'mobile_no'         => $this->event->mobile_no,
                // 'code'              => $this->event->code,
                // 'owner_mobile_no'   => $this->event->owner_mobile_no,
                // 'owner_name'        => $this->event->owner_name,
                // 'vehicle_number'    => $this->event->vehicle_number,
                // 'vehicle_id'        => $this->event->vehicle_id,
                // 'module_id'         => $this->event->module_id,
    
                //return view('pdf');
            
        // If you want to store the generated pdf to the server then you can use the store function
        // $pdf->save(public_path().'/check.pdf');
        // return $pdf;
    // Finally, you can download the file using download function
    // return $pdf->download('customers.pdf');
        // return $this->view('email-template.email')->with([            
        //     'company_name'      => $this->event->company_name,
        //     'old_value'         => (object)$this->event->decoded['old_filed'],
        //     'new_value'         => (object)$this->event->decoded['new_filed'],
        //     'customer_id'       => $this->event->customer_id,
        //     'customer_name'     => $this->event->Customer_name,
        //     'mobile_no'         => $this->event->mobile_no,
        //     'code'              => $this->event->code,
        //     'owner_mobile_no'   => $this->event->owner_mobile_no,
        //     'owner_name'        => $this->event->owner_name,
        //     'vehicle_number'    => $this->event->vehicle_number,
        //     'vehicle_id'        => $this->event->vehicle_id,
        //     'module_id'         => $this->event->module_id,


        // ])
        // ->subject('TESTING BY AXAY');
    }
}
