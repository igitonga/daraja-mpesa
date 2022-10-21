<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MpesaController extends Controller
{    
    // generating access token >> Auth
    public function getAccessToken(){
        //dd(base64_encode(env('MPESA_CONSUMER_KEY')));
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => env('MPESA_BASE_URL').'/oauth/v1/generate?grant_type=client_credentials',
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Basic QXpzMktlalUxQVJ2SUw1SmRKc0FSYlYyZ0RyV21wT0I6aGlwR3ZGSmJPeHJpMzMwYw=='
                ],
                CURLOPT_RETURNTRANSFER => true,
            )
        );
        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        // return $response;
        return $response->access_token;
    }

    //register urls
    public function registerURLS(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => env('MPESA_BASE_URL').'/mpesa/c2b/v1/registerurl',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(array(
            "ShortCode" => env('MPESA_SHORTCODE'),
            "ResponseType" => "Completed",
            "ConfirmationURL" => env('MPESA_TEST_URL')."/callback/confirmation",
            "ValidationURL" => env('MPESA_TEST_URL')."/callback/validation"
        )),
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$this->getAccessToken(),
            'Content-Type: application/json',
        ),
        ));

        $response = json_decode(curl_exec($curl));

        curl_close($curl);
        return $response;
        
    }

    //customer to business simulation
    public function c2b(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => env('MPESA_BASE_URL').'/mpesa/c2b/v1/simulate',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(array(
            "ShortCode" => env('MPESA_SHORTCODE'),
            "CommandID" => "CustomerPayBillOnline",
            "Amount" => "1",
            "Msisdn" => "254708374149",
            "BillRefNumber" => "MI1"
        )),
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$this->getAccessToken(),
            'Content-Type: application/json',
        ),
        ));

        $response = json_decode(curl_exec($curl));

        curl_close($curl);
        return $response;
    }

    public function b2c(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => env('MPESA_BASE_URL').'/mpesa/b2c/v1/paymentrequest',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => json_encode(array(
            "InitiatorName" => "John Doe",
            "SecurityCredential" => env('MPESA_SECURITY_CREDENTIALS'),
            "CommandID" => "BusinessPayment",
            "Amount" => "1",
            "PartyA" => env('MPESA_SHORTCODE'),
            "PartyB" => "254708374149",
            "Remarks" => "None",
            "QueueTimeOutURL" => env('MPESA_TEST_URL')."/callback/queue",
            "ResultURL" => env('MPESA_TEST_URL')."/callback/result",
          "Occasion" => "None"
         )),
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$this->getAccessToken(),
            'Content-Type: application/json'
          ),
        ));
        
        $response = json_decode(curl_exec($curl));

        curl_close($curl);
        return $response;
    }

    public function stkPush(){
        $curl = curl_init();

        $BusinessShortCode = "174379";
        $Timestamp = date('YmdHis');
        $PasswordKey = env('MPESA_PASS_KEY');
        $Password=base64_encode($BusinessShortCode.$PasswordKey.$Timestamp);

        curl_setopt_array($curl, array(
        CURLOPT_URL => env('MPESA_BASE_URL').'/mpesa/stkpush/v1/processrequest',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(array(
            "BusinessShortCode" => $BusinessShortCode,
            "Password" => $Password,
            "Timestamp" => $Timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => "1",
            "PartyA" => "254713394693",
            "PartyB" => env('MPESA_SHORTCODE'),
            "PhoneNumber" => "254713394693",
            "CallBackURL" => env('MPESA_TEST_URL')."/callback/stkpush",
            "AccountReference" => "Test",
            "TransactionDesc" => "Test"
        )),
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$this->getAccessToken(),
            'Content-Type: application/json',
        ),
        ));

        $response = json_decode(curl_exec($curl));

        curl_close($curl);
        return $response;
    }


    /*
     *Responses coming from SAFARICOM
     */
    public function validation(Request $request){
        Log::info('Validation endpoint hit');
        Log::info($request->all());

        return [
            'ResultCode' => 0,
            'ResultDesc' => 'Accept Service',
            'ThirdPartyTransID' => rand(3000, 10000)
        ];
    }

    public function confirmation(Request $request){
        Log::info('Confirmation endpoint hit');
        Log::info($request->all());

        return [
            'ResultCode' => 0,
            'ResultDesc' => 'Accept Service',
            'ThirdPartyTransID' => rand(3000, 10000)
        ];
    }

    public function queueTimeOut(Request $request){
        Log::info('QueueTimeOut endpoint hit');
        Log::info($request->all());

        return [
            'ResultCode' => 0,
            'ResultDesc' => 'Accept Service',
            'ThirdPartyTransID' => rand(3000, 10000)
        ];
    }

    public function result(Request $request){
        Log::info('Result endpoint hit');
        Log::info($request->all());

        return [
            'ResultCode' => 0,
            'ResultDesc' => 'Accept Service',
            'ThirdPartyTransID' => rand(3000, 10000)
        ];
    }

    public function stkPushCallback(Request $request){
        Log::info('STKPush endpoint hit');
        Log::info($request->all());

        return [
            'ResultCode' => 0,
            'ResultDesc' => 'Accept Service',
            'ThirdPartyTransID' => rand(3000, 10000)
        ];
    }

}
