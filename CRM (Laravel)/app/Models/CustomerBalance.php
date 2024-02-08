<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerBalance extends Model
{
    protected 	$table 		=	'customer_balance';
    protected 	$primaryKey =	'balance_id'; // or null
    protected 	$guarded 	=	['balance_id'];
    public      $timestamps =   false;
    /*
    Use     : Updating Appointment balance 
    Author  : Axay Shah
    Date    : 03 Dec,2018
    */

    public static function UpdateAppointmentBalance($appointment_id=0,$amount_paid=0)
	{
        return self::where('appointment_id',$appointment_id)->update(['d_amount'=>$amount_paid]);
    }
    /*
    Use     : get Customer Balance Amount 
    Author  : Axay Shah
    Date    : 05 Dec,2018
    */

    public static function getCustomerBalanceAmount($customer_id=0, $appointment_id=0)
	{
        $balanceAmount 	= 0;
		if(!empty($appointment_id)) {

            $customerBalance = self::select('customer_balance.c_amount','customer_balance.d_amount','appointment_collection.given_amount')
            ->join('appointment_collection','appointment_collection.appointment_id','=','customer_balance.appointment_id')
            ->where('customer_balance.customer_id',$customer_id)
            ->where('customer_balance.appointment_id','<',$appointment_id)
            ->orderBy('customer_balance.appointment_id','DESC')
            ->first();
            if($customerBalance){
                $balanceAmount 	= $customerBalance->c_amount ;
            }
        }
		return $balanceAmount;
    }
    /*
    Use     : get Customer Balance Amount 
    Author  : Axay Shah
    Date    : 05 Dec,2018
    */

    public static function getCustomerBalanceAppId($customer_id=0, $appointment_id=0)
	{
        $appId = '';
		if(!empty($appointment_id) && !empty($customer_id)) {
			$customerBalanceApp = self::select('appointment_id')
            ->where('customer_id',$customer_id)
            ->where('appointment_id','<',$appointment_id)
            ->orderBy('appointment_id','DESC')
            ->first();
			if($customerBalanceApp){
                $appId = (!empty($customerBalanceApp->appointment_id)) ? $customerBalanceApp->appointment_id : '';
            }
		}
		return $appId;
	}

    /**
     * Function Name : getCustomerAppsBalanceAmount
     * @param $request
     * @return
     * @author sachin Patel
     */


    public static function getCustomerAppsBalanceAmount($request)
    {
        $balanceAmount = self::select(DB::raw('SUM(c_amount) AS balance'))
            ->where('customer_id', $request->customer_id)
            ->whereBetween('created_date',array($request->from_date,$request->to_date))
            ->first();
        return $balanceAmount;

    }


    /**
     * Function Name : SaveCustomerBalance
     * @param
     * @param
     * @return
     * @author Sachin Patel
     */
    public static  function SaveCustomerBalance($request)
    {
        self::create([
            'appointment_id' => $request->appointment_id,
            'customer_id' => $request->customer_id,
            'c_amount' => $request->c_amount,
            'd_amount' => $request->d_amount,
            'created_date' => Carbon::now(),
        ]);

    }

}
