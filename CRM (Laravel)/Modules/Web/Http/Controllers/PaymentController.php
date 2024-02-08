<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;


class PaymentController extends LRBaseController
{
    public function initiatePayment()
    {   
        $data = '{"tid":"1704694140128","merchant_id":"","order_id":"123654789","amount":"1.00","currency":"INR","redirect_url":"http:\/\/localhost\/PHPKits\/NON_SEAMLESS_KIT\/ccavResponseHandler.php","cancel_url":"http:\/\/localhost\/PHPKits\/NON_SEAMLESS_KIT\/ccavResponseHandler.php","language":"EN","billing_name":"Charli","billing_address":"Room no 1101, near Railway station Ambad","billing_city":"Indore","billing_state":"MP","billing_zip":"425001","billing_country":"India","billing_tel":"9876543210","billing_email":"test@test.com","delivery_name":"Chaplin","delivery_address":"room no.701 near bus stand","delivery_city":"Hyderabad","delivery_state":"Andhra","delivery_zip":"425001","delivery_country":"India","delivery_tel":"9876543210","merchant_param1":"additional Info.","merchant_param2":"additional Info.","merchant_param3":"additional Info.","merchant_param4":"additional Info.","merchant_param5":"additional Info.","promo_code":"","customer_identifier":""}';

        $post = json_decode($data, true);        

        $merchant_data='2';
        $working_key='E065C110A0C130BD068C008B437B9EDC';//Shared by CCAVENUES
        $access_code='AVKP44KL39CE91PKEC';//Shared by CCAVENUES
        
        foreach ($post as $key => $value){
            $merchant_data.=$key.'='.$value.'&';
        }

        $encrypted_data=encrypt($merchant_data,$working_key); // Method for encrypting the data.

        $formData = array(
            'enc_request' => $merchant_data,
            'access_code' => $access_code,
            'request_type' => 'JSON',
            'response_type' => 'JSON',
            'Command' => 'initiateTransaction',
            'amount' => 1
        );

        $thirdPartyUrl = 'https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction';

        $response = Http::withoutVerifying()->post($thirdPartyUrl, $formData);

        //$response = Http::post($thirdPartyUrl, $formData);
        
        $responseData = $response->json();


        echo "<pre>";
        print_r($encrypted_data);
        die;
        

    }
}