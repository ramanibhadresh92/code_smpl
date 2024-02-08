<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Model;

class InertDeduction extends Model implements Auditable
{
    protected 	$table 		=	'inert_deduction';
    protected 	$primaryKey =	'deduction_id'; // or null
    protected 	$guarded 	=	['deduction_id'];
    public      $timestamps =   true;
    use AuditableTrait;

    /*
    Use     : Get appointment Deduction Data
    Author  : Axay Shah
    Date    : 05 Dec,2018
    */
    public static function getAppointmentDeductionData($appointment_id = 0){
        $appIds 	= '';
        $inert = self::select('inert_deduction.appointment_id')
        ->leftjoin('inert_deduction_detail','inert_deduction_detail.deduction_id','=','inert_deduction.deduction_id')
        ->where('inert_deduction_detail.appointment_id',$appointment_id)
        ->groupBy('inert_deduction.appointment_id')->get();
		if(!$inert->isEmpty()) {
			foreach($inert as $in) {
					$appIds .= $in->appointment_id;
            }
		}
		return (!empty($appIds)?rtrim($appIds,","):'');
    }
    /*
    Use     : Get appointment Deduction Amount
    Author  : Axay Shah
    Date    : 05 Dec,2018
    */
    public static function getAppointmentDeductionAmount($appointment_id=0)
	{
		$deductAmount 	= 0;
		if(!empty($appointment_id)) {
            $getDeductAmount =  self::select(\DB::raw('SUM(inert_deduction_detail.deducted_amount) AS deduct_amount'))
                                ->leftjoin('inert_deduction_detail','inert_deduction_detail.deduction_id','=','inert_deduction.deduction_id')
                                ->where('inert_deduction_detail.appointment_id',$appointment_id)
                                ->get();
            if(!$getDeductAmount->isEmpty()){

            }
			$deductAmount = ($getDeductAmount[0]->deduct_amount != 0)? $getDeductAmount[0]->deduct_amount:0;
		}
		return $deductAmount;
	}

    /**
     * Function Name : GetCustomerPendingDeductionAmount
     * @param $customer_id
     * @return
     * @author Sachin Patel
     */
    public static function GetCustomerPendingDeductionAmount($customer_id=0,$appointment_id=0)
    {
        $pendingInertAmount = 0;

        if(!empty($appointment_id) && !empty($customer_id)) {


            /*$query 	= " SELECT SUM(amount-deducted_amount) AS pending_amount
						FROM ".$this->tablename." 
						WHERE customer_id = '".$customer_id."' AND approve_status = ".$this->INERT_STATUS_APPROVE." 
						AND approve_date < '".$appCollDate."'  ";

            $dbqry 	= new dbquery($query);
            $row 	= $dbqry->getrowarray(MYSQL_ASSOC);
            $pendingInertAmount = (!empty($row['pending_amount'])?$row['pending_amount']:0);*/
        }

        if(!empty($appointment_id) && !empty($customer_id)) {
            $appointment = Appoinment::where('appointment_id',$appointment_id)->first();

            $amount = self::select(DB::raw('SUM(amount-deducted_amount) AS pending_amount'))
                ->where('customer_id',$customer_id)
                ->where('approve_status',INERT_STATUS_APPROVE)
                ->where('approve_date','<',$appointment->created_at)->first();
            $pendingInertAmount = (!empty($amount->pending_amount)?$amount->pending_amount:0);

        }


        return $pendingInertAmount;
    }



    /**
     * Function Name : UpdateInertDeductionAmount
     * @param integer $appointment_id
     * @param integer $customer_id
     * @param  float $deduct_amount
     * @return
     * @author Sachin Patel
     */
    public static function UpdateInertDeductionAmount($appointment_id,$customer_id,$deduct_amount,$request)
    {
        $collectionAmount =  AppointmentCollection::getCollectionTotalExcludeInertByAppointment($appointment_id);

        if($deduct_amount > $collectionAmount) {
            $deduct_amount = $collectionAmount;
        }

        $deduct_request_amount = $deduct_amount;

        $result = self::select('deduction_id', 'customer_id', 'amount', 'deducted_amount',DB::raw('CASE WHEN 1 = 1 THEN( 
							SELECT SUM(amount-deducted_amount) FROM inert_deduction WHERE deduction_id = inert_deduction.deduction_id
						) END as deduct_remain_amount '))
                    ->where('approve_status',INERT_STATUS_APPROVE)
                    ->where('customer_id',$customer_id)
                    ->having('deduct_remain_amount','>',0)->get();
        if(!empty($result)){
            foreach ($result as $key => $value){
                if($deduct_amount <= $value->deduct_remain_amount) {
                    $remainQty = $value->deduct_remain_amount - $deduct_amount;
                    $updateQty = $deduct_amount;
                }else{
                    $remainQty 	= $deduct_amount - $value->deduct_remain_amount;
                    $updateQty 	= $value->deduct_remain_amount;
                }

                /*Update Inert deduction amount*/
                $final_qty 	= ($value->deducted_amount != "0.00" ? ($value->deducted_amount + $updateQty):$updateQty);
                $value->update([
                    'deducted_amount' => $final_qty,
                ]);


                $request->deduct_request_amount     = $deduct_request_amount;
                $request->deducted_amount           = $updateQty;
                $request->deduction_id              = $value->deduction_id;
                $request->customer_id               = $customer_id;

                self::saveInertDeductionDetail($result);

                if($deduct_amount < $value->deduct_remain_amount) {
                    break;
                }

                $deduct_amount = $remainQty;

            }
        }
    }

    /**
     * Function Name : saveInertDeductionDetail
     * @param
     * @param
     * @return
     * @author sachin Patel
     */
    public static function saveInertDeductionDetail($request)
    {
        DB::table('inert_deduction_detail')->insert([
            'appointment_id' => $request->appointment_id,
            'customer_id' => $request->customer_id,
            'deduction_id' => $request->deduction_id,
            'deduct_request_amount' => $request->deduct_request_amount,
            'deducted_amount' => $request->deducted_amount,
            'created_date' => Carbon::now(),
        ]);
    }

}
