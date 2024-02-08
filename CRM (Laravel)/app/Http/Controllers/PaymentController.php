<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;


class PaymentController extends Controller
{
    public function initiatePayment()
    {
        // Generate a unique order ID
        $order_id = uniqid();

        // Other necessary parameters
        /*$merchant_id = Config::get('cca.AVENUE_MERCHANT_ID');
        $access_code = Config::get('cca.AVENUE_ACCESS_CODE');*/

        $merchant_id = 3134968;
        $access_code = 'AVKP44KL39CE91PKEC';
        $currency = 'INR'; // Change it based on your currency
        $amount = 100.00; // Change it based on your actual amount

        // Create an array with the payment parameters
        $params = [
            'order_id' => $order_id,
            'merchant_id' => $merchant_id,
            'currency' => $currency,
            'amount' => $amount,
            'redirect_url' => route('payment.response'), // Assuming you have named the route
            'cancel_url' => route('payment.cancel'), // Implement the cancel route
            'language' => 'EN', // Change it based on your language preference
            // Add other parameters as needed
        ];

        // Generate the checksum hash
        $enc_request = $this->generateChecksum($params);

        echo $enc_request;
        
        // Add the checksum to the parameters
        $params['checksum'] = $checksum;

        // Redirect to CCAvenue with the payment form
        return view('payment.redirect')->with('params', $params);
    }

    protected function generateChecksum($params)
    {
        return Crypt::encryptString($params);
    }
}