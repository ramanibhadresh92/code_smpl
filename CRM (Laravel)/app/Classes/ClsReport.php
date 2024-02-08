<?php

namespace App\Classes;
use App\Models\Appoinment;
use App\Models\AppointmentCollection;
use App\Models\AppointmentNotification;
use App\Models\AppointmentTimeReport;
use App\Models\CustomerContactDetails;
use App\Models\CompanyMaster;
use App\Models\CustomerMaster;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClsReport {

    /** variable declaration of paging class variable */
    var $clspaging;

    /** variable declaration */
    var $is_donation;//added for charity
    var $donation_to;//added for charity
    var $report_for;
    var $report_type;
    var $report_period;
    var $report_starttime;
    var $report_endtime;
    var $showmenu;
    var $cust_group;
    var $customer_group;
    var $product_id;
    var $category_id;
    var $price_group;
    var $cust_type;
    var $query;
    var $download;
    var $generatexls;//added by Anushree
    var $INVOICE_DATE;
    var $Total_Weight;
    var $Total_Price;
    var $collection_by;//added for colrep
    var $product_for;// added for colrep
    var $referred_by;
    var $vehicle_id;
    var $vehicle_cost;
    var $vehicle_type;
    var $Driver_Name;
    var $supervisor_id;
    var $type_of_product 		= 1; //WITHOUT CWD
    var $RETAIL_CUST_GROUP;// added for RETAIL_CUST_GROUP
    var $ARR_REPORT_PERIOD 		=  array("1"=>"Today","2"=>"Yesterday","3"=>"This Week","4"=>"This Month","5"=>"Custom");
    var $ARR_REPORT_PERIOD2		=  array("1"=>"This Month","2"=>"Custom");
    var $CUSTOMER_LOSS_COND		=  array("1"=>"Last 1 Month","3"=>"Last 3 Months","6"=>"Last 6 Months","9"=>"Last 9 Months","12"=>"Last 1 Year","999"=>"-No Appointments-");
    var $ARR_PAYMENT_OPTIONS 	=  array("1"=>"Cash","2"=>"Cheque","3"=>"Draft");
    var $ARR_PAYMENT_METHOD  	=  array("1"=>"Full","2"=>"Part","3"=>"Advanced");
    var $TYPE_OF_PRODUCTS  		=  array("0"=>"ALL","1"=>"WITHOUT CW DISPOSAL","2"=>"ONLY CW DISPOSAL");
    var $PAYMENT_MODE_DAILY		= "1010001";
    var $PAYMENT_MODE_WEEKLY	= "1010002";
    var $PAYMENT_MODE_15DAYS	= "1010003";
    var $PAYMENT_MODE_MONTHLY	= "1010004";
    var $payment_report_flag	= 1; //due payments only
    var $customer_loss_flag		= 1; //last 1 month
    var $PAYMENT_MODE			= 0; //customer payment mode
    var $paymentmode 			= "";
    var $DEFAULT_REMARK_CASH_TYPE = 'Our Account department will deliver the same within 24 Hrs.';
    var $DEFAULT_REMARK_CHEQUE_TYPE = 'Your payment has been processed by cheque no [CHEQUE_NO] dt [CHEQUE_DATE], will be couriered to you today.';
    var $REFERRAL_COLLECTION_QTY_PERC = 45; // Show 45 Percentage Collection Quantity for Referral Customer Collection
    var $ATTENDANCE_TYPE_FULLDAY	= 'Present';
    var $ATTENDANCE_TYPE_HALFDAY	= 'Half Day';
    var $city_id;
    var $zone;
    var $ward;
    var $society;
    var $pid;
    var $CW_DISPOSAL_ID 			= 50;
    var $compositions				= array(32=>"0.14",54=>"0.28",47=>"0.52",57=>"5.65",
        42=>"4.20",85=>"3.14",12=>"0.98",53=>"0.84",
        6=>"6.45",3=>"23.50",19=>"0.60",4=>"0.30",1=>"1.71",
        46=>"1.26",86=>"1.21",11=>"2.50",13=>"5.77",
        33=>"0.54",68=>"0.48",44=>"0.38",69=>"0.30");

    var $mrf_dept_id = '';
    var $report_month;
    var $report_year;
    var $collection_types;
    var $is_unique;
    var $custtype;
    var $importid;
    var $appointment_id;
    var $appointment_ids;

    function __construct() {
        $this->report_for		= "";
        $this->is_donation      = "";// added for charity
        $this->donation_to      = ""; // added for charity
        $this->collection_by	= "";// added for colrep
        $this->product_for		= "";// added for colrep
        $this->referred_by		= "";
        $this->supervisor_id	= "";
        $this->vehicle_id		= "";
        $this->report_type		= "";
        $this->report_period	= 1;
        $this->report_starttime	= "";
        $this->report_endtime	= "";
        $this->cust_group		= "";
        $this->price_group 		= "";
        $this->cust_type		= "";
        $this->showmenu			= 1;
        $this->query			= "";
        $this->download			= "";
        $this->INVOICE_DATE		= "";
        $this->Total_Weight		= 0;
        $this->Total_Price		= 0;
        $this->RETAIL_CUST_GROUP= 13011;
        //added by Anushree
        $this->generatexls      = "";
        $this->mrf_dept_id      = "";
        $this->PAYMENT_MOD      = 0;
        $this->report_month		= "";
        $this->report_year		= "";
        $this->collection_types	= 1;
        $this->is_unique		= 0;
        $this->city_id 			= 0;
        $this->zone 			= 0;
        $this->ward 			= 0;
        $this->society 			= 0;
        $this->customer_group 	= array();
        $this->product_id 		= array();
        $this->category_id 		= array();
        $this->custtype 		= array();
        $this->pid 				= 0;
        $this->paymentmode 		= array();
        $this->type_of_product 	= 1; //WITHOUT CWD
        $this->importid 		= 0;
        $this->appointment_id 	= "";
        $this->appointment_ids 	= array();
    }
 
    public function pageadminCollectionReport(){
        switch($this->report_period)
        {
            case 1:
                {
                    $this->report_starttime = date("Y-m-d")." 00:00:00";
                    $this->report_endtime 	= date("Y-m-d")." 23:59:59";
                    $this->INVOICE_DATE		= date("d-M-Y");
                    break;
                }
            case 2:
                {
                    $this->report_starttime = date("Y-m-d", strtotime("-1 day"))." 00:00:00";
                    $this->report_endtime 	= date("Y-m-d", strtotime("-1 day"))." 23:59:59";
                    $this->INVOICE_DATE		= date("d-M-Y",strtotime($this->report_starttime))." To ".date("d-M-Y",strtotime($this->report_endtime));
                    break;
                }
            case 3:
                {
                    $this->report_starttime = $this->week_day(1)." 00:00:00";
                    $this->report_endtime 	= $this->week_day(7)." 23:59:59";
                    $this->INVOICE_DATE		= date("d-M-Y",strtotime($this->report_starttime))." To ".date("d-M-Y",strtotime($this->report_endtime));
                    break;
                }
            case 4:
                {
                    $this->report_starttime = date("Y")."-".date("m")."-01"." 00:00:00";
                    $this->report_endtime	= date('Y-m-d',strtotime('-1 second',strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00'))))." 23:59:59";
                    $this->INVOICE_DATE		= date("d-M-Y",strtotime($this->report_starttime))." To ".date("d-M-Y",strtotime($this->report_endtime));
                    break;
                }
            default:
                {
                    if ($this->report_starttime != "" && $this->report_endtime != "") {
                        $this->report_starttime = $this->report_starttime." 00:00:00";
                        $this->report_endtime = $this->report_endtime." 23:59:59";
                        $this->INVOICE_DATE		= date("d-M-Y",strtotime($this->report_starttime))." To ".date("d-M-Y",strtotime($this->report_endtime));
                    }
                    break;
                }
        }
    }


    /**
     * Function Name : getCollectionRFP
     * @param
     * @return
     * @author Sachin Patel
     */
    function getCollectionRFP()
    {
        return AppointmentNotification::where('collection_by',$this->report_for)->where('notification_dt','>=',$this->report_starttime)->where('notification_dt','<=',$this->report_endtime)->where('request_appointment',1)->count();
    }


    /**
     * Function Name : getCollectionRFPAccepted
     * @param
     * @return
     * @author Sachin Patel
     */
    function getCollectionRFPAccepted()
    {
        return AppointmentNotification::where('collection_by',$this->report_for)->where('notification_dt','>=',$this->report_starttime)->where('notification_dt','<=',$this->report_endtime)->where('appointment_accepted',1)->count();
    }


    /**
     * Function Name : getCollectionAccepted
     * @param
     * @return
     * @author Sachin Patel
     */
    function getCollectionAccepted()
    {
       return Appoinment::select('pr.para_value as App_Status', DB::raw('count(*) as CNT'))
            ->join('parameter as pr','appoinment.para_status_id','=','pr.para_id')
            ->where('appoinment.collection_by',$this->report_for)
            ->where('app_date_time','>=',$this->report_starttime)->where('app_date_time','<=',$this->report_endtime)
            ->groupBy('appoinment.para_status_id')->get();
    }


    /**
     * Function Name : getCollectionAmount
     * @param
     * @return
     * @author Sachin Patel
     */
    function getCollectionAmount()
    {
       $Gross_Amount = AppointmentCollection::select(DB::raw('SUM(amount) as Gross_Amount'))
           ->where('para_status_id','!=',COLLECTION_PENDING)
           ->where('collection_by',$this->report_for)
           ->where('collection_dt','>=',$this->report_starttime)->where('collection_dt','<=',$this->report_endtime)->get();

        return isset($Gross_Amount->Gross_Amount) ? round($Gross_Amount->Gross_Amount) : 0;
    }

    /**
     * Function Name : getCollectionInWeight
     * @param
     * @return
     * @author Sachin Patel
     */
    function getCollectionInWeight()
    {
         $query = AppointmentCollection::select('P.name as Product_Name','PR.para_value as UNIT_NAME','ap.is_donation as Donation','PQP.parameter_name as Product_Quality','P.enurt as Product_Inert',DB::raw('sum(CD.actual_coll_quantity) AS Total_Quantity'),DB::raw('sum(CD.actual_coll_quantity * CD.para_quality_price) as Total_Price'))
             ->join('appoinment as ap','appointment_collection.appointment_id', '=', 'ap.appointment_id')
             ->join('appointment_collection_details as CD','appointment_collection.collection_id', '=', 'CD.collection_id')
             ->join('product_master as P','CD.product_id', '=', 'P.product_id')
             ->join('parameter as PR','CD.product_para_unit_id', '=', 'PR.para_id');

            $query->join('product_quality_parameter as PQP', function($join)
            {
                $join->on('P.product_id', '=', 'PQP.product_id');
                $join->on('CD.product_quality_para_id', '=', 'PQP.product_quality_para_id');

            });

        $query->where('appointment_collection.collection_dt','>=',$this->report_starttime)->where('appointment_collection.collection_dt','<=',$this->report_endtime);
        $query->where('appointment_collection.para_status_id','!=',COLLECTION_PENDING);
        $query->where('appointment_collection.collection_by',$this->report_for);
        $query->groupBy('CD.product_quality_para_id');
        $data = $query->get();
        return $data;

    }

    /**
     * Function Name : getVarienceReport
     * @param
     * @return
     * @author Sachin Patel
     */
    function getVarienceReport()
    {
       $data =  DB::table('audit_collection')->select('P.name as Product_Name','PR.para_value as UNIT_NAME','PQP.parameter_name as Product_Quality',
							'audit_collection.expected_collection AS expected_collection',
							'audit_collection.actual_collection AS actual_collection',DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as CollectionBy"),DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as AuditBy"))
               ->leftJoin('product_master as P','audit_collection.product_id', '=', 'P.product_id')
               ->leftJoin('adminUser as U1','audit_collection.collection_by','=','U1.adminuserid')
               ->leftJoin('adminUser as U2','audit_collection.audit_by','=','U2.adminuserid')
               ->leftJoin('parameter as PR','audit_collection.para_unit_id','=','PR.para_id')
               ->leftJoin('product_quality_parameter as PQP','audit_collection.product_quality_para_id','=','PQP.product_quality_para_id')
               ->whereNotNull('P.product_id')
               ->where('audit_collection.audit_dt','>=',$this->report_starttime)->where('audit_collection.audit_dt','<=',$this->report_endtime)
               ->where('audit_collection.expected_collection','>','audit_collection.actual_collection')
               ->groupBy('audit_collection.product_quality_para_id')->get();

       return $data;
    }

    /**
     * Function Name : getAvgTransactionServiceTime
     * @param
     * @return
     * @author Sachin Patel
     */
    public static function getAvgTransactionServiceTime()
    {
        $report_starttime   = date("Y-m-d")." 00:00:00";
        $report_endtime 	= date("Y-m-d")." 23:59:59";
           return AppointmentTimeReport::select(DB::raw('AVG(TIME_TO_SEC(TIMEDIFF(appointment_time_report.endtime,appointment_time_report.starttime))/60) as Avg_Service_Time'))
                    ->where('para_report_status_id',COLLECTION_COMPLETED)
                    ->whereBetween('starttime',[$report_starttime,$report_endtime])
                    ->where('starttime','!=','0000-00-00 00:00:00')
                    ->where('endtime','!=','0000-00-00 00:00:00')
                    ->where('collection_by',Auth::user()->adminuserid)->get();

    }

}

