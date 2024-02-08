<?php

namespace App\Exports;
use App\Models\AdminUser;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Appoinment;

class UserExport implements FromView
{
	 // use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    // public function query()
    // {
    //     return AdminUser::query()->where('adminuserid',0);
    // }
    // public function headings(): array
    // {
        
    // }


    public $date;
     public function __construct($date)
    {
        $this->date = $date;
       
    }
    public function view(): View
    {
        $startDate  = $this->date." ".GLOBAL_START_TIME;
        $endDate    = $this->date." ".GLOBAL_END_TIME;
        $SQL        = Appoinment::select("v.vehicle_number","appoinment.vehicle_id","appoinment.appointment_id",        \DB::raw("DATE_FORMAT(appoinment.app_date_time,'%H:%i') as app_time"),
    				\DB::raw("CONCAT(c.first_name,'',c.last_name) as customer_name"),"appoinment.foc")
    				->join("vehicle_master as v","appoinment.vehicle_id","=","v.vehicle_id")
    				->join("customer_master as c","appoinment.customer_id","=","c.customer_id")
                    // ->where("appoinment.para_status_id",APPOINTMENT_SCHEDULED)
                    ->whereBetween("app_date_time",array($startDate,$endDate))
                    ->groupBy("v.vehicle_id")
                    ->groupBy("app_time")
                    ->orderBy("appoinment.vehicle_id")
                    ->orderBy("appoinment.app_date_time")
                    ->get()->toArray();
        $array  = array(
                "1" 	=> "00:00",
                "2" 	=> "00:15",
                "3" 	=> "00:30",
                "4" 	=> "00:45",
                "5" 	=> "01:00",
                "6" 	=> "01:15",
                "7" 	=> "01:30",
                "8" 	=> "01:45",
                "9" 	=> "02:00",
                "10" 	=> "02:15",
                "11" 	=> "02:30",
                "12" 	=> "02:45",
                "13" 	=> "03:00",
                "14" 	=> "03:15",
                "15" 	=> "03:30",
                "16" 	=> "03:45",
                "17" 	=> "04:00",
                "18" 	=> "04:15",
                "19" 	=> "04:30",
                "20" 	=> "04:45",
                "21" 	=> "05:00",
                "22" 	=> "05:15",
                "23" 	=> "05:30",
                "24" 	=> "05:45",
                "25" 	=> "06:00",
                "26" 	=> "06:15",
                "27" 	=> "06:30",
                "28" 	=> "06:45",
                "29" 	=> "07:00",
                "30" 	=> "07:15",
                "31" 	=> "07:30",
                "32" 	=> "07:45",
                "33" 	=> "08:00",
                "34" 	=> "08:15",
                "35" 	=> "08:30",
                "36" 	=> "08:45",
                "37" 	=> "09:00",
                "38" 	=> "09:15",
                "39" 	=> "09:30",
                "40" 	=> "09:45",
                "41" 	=> "10:00",
                "42" 	=> "10:15",
                "43" 	=> "10:30",
                "44" 	=> "10:45",
                "45" 	=> "11:00",
                "46" 	=> "11:15",
                "47" 	=> "11:30",
                "48" 	=> "11:45",
                "49" 	=> "12:00",
                "50" 	=> "12:15",
                "51" 	=> "12:30",
                "52" 	=> "12:45",
                "53" 	=> "13:00",
                "54" 	=> "13:15",
                "55" 	=> "13:30",
                "56" 	=> "13:45",
				"57" 	=> "14:00",
                "58" 	=> "14:15",
                "59" 	=> "14:30",
                "60" 	=> "14:45",
				"61" 	=> "15:00",
                "62" 	=> "15:15",
                "63" 	=> "15:30",
                "64" 	=> "15:45",
				"65" 	=> "16:00",
                "66" 	=> "16:15",
                "67" 	=> "16:30",
                "68" 	=> "16:45",
				"69" 	=> "17:00",
                "70" 	=> "17:15",
                "71" 	=> "17:30",
                "72" 	=> "17:45",
				"73" 	=> "18:00",
                "74" 	=> "18:15",
                "75" 	=> "18:30",
                "76" 	=> "18:45",
				"77" 	=> "19:00",
                "78" 	=> "19:15",
                "79" 	=> "19:30",
                "80" 	=> "19:45",
				"81" 	=> "20:00",
                "82" 	=> "20:15",
                "83" 	=> "20:30",
                "84" 	=> "20:45",
				"85" 	=> "21:00",
                "86" 	=> "21:15",
                "87" 	=> "21:30",
                "88" 	=> "21:45",
				"89" 	=> "22:00",
                "90" 	=> "22:15",
                "91" 	=> "22:30",
                "92" 	=> "22:45",
				"93" 	=> "23:00",
                "94" 	=> "23:15",
                "95" 	=> "23:30",
                "96" 	=> "23:45",
                );
        $result 	= array();
        $vehicle 	= array(); 
        $data 		= array();
        foreach($SQL as $raw){
        	array_push($vehicle,$raw['vehicle_number']);
            $INDEX =  array_search($raw['app_time'],$array);
            $result[$raw['vehicle_number']]['vehicle_number'] = $raw['vehicle_number'];
            $result[$raw['vehicle_number']][$INDEX] = $raw;  
           	$data = array_unique($vehicle);
        }
		return view('email-template.excel', [
            'users' => $result,
            'data'  => $data
        ]);
    }
}
