<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Session;

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
        
        if($response->ResponseCode == "0"){
            Session::flash('Success','URL successfully registered'); 
            return redirect()->back();
         }
         else{
            Session::flash('error','Something went wrong'); 
            return redirect()->back();
         }
        
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

    //business to customer simulation
    public function b2c(Request $request){
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
            "Amount" => $request->amount,
            "PartyA" => env('MPESA_SHORTCODE'),
            "PartyB" => $request->phone,
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
dd($response);
        curl_close($curl);
        
        if($response->ResponseCode == "0"){
            Session::flash('Success','URL successfully registered'); 
            return redirect()->back();
         }
         else{
            Session::flash('error','Something went wrong'); 
            return redirect()->back();
         }
    }

    //online customer to business
    public function stkPush(Request $request){
        $curl = curl_init();

        $BusinessShortCode = 174379;
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
            "Amount" => $request->amount,
            "PartyA" => $request->phone,
            "PartyB" => $BusinessShortCode,
            "PhoneNumber" => 254713394693,
            "CallBackURL" => env('MPESA_TEST_URL')."/callback/stkpush",
            "AccountReference" => "LC",
            "TransactionDesc" => "Deposit"
        )),
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$this->getAccessToken(),
            'Content-Type: application/json',
        ),
        ));

        $response = json_decode(curl_exec($curl));

        curl_close($curl);
        
        if($response->ResponseCode == "0"){
            Session::flash('Success','Input your mpesa pin'); 
            return redirect()->back();
         }
         else{
            Session::flash('error','Something went wrong'); 
            return redirect()->back();
         }
    }


    /*
     *Responses coming from SAFARICOM
     */
    public function validation(Request $request){
        // DATA
        $mpesaResponse = file_get_contents('php://input');
        // log the response
        $logFile = "M_PESAConfirmationResponse.txt";
        // write to file
        $log = fopen($logFile, "a");
    
        fwrite($log, $mpesaResponse);
        fclose($log);

        return [
            'ResultCode' => 0,
            'ResultDesc' => 'Accept Service',
            'ThirdPartyTransID' => rand(3000, 10000)
        ];
    }

    public function confirmation(Request $request){
        // DATA
        $mpesaResponse = file_get_contents('php://input');
        // log the response
        $logFile = "M_PESAConfirmationResponse.txt";
        // write to file
        $log = fopen($logFile, "a");
    
        fwrite($log, $mpesaResponse);
        fclose($log);
    
        echo $response;

        return [
            'ResultCode' => 0,
            'ResultDesc' => 'Accept Service',
            'ThirdPartyTransID' => rand(3000, 10000)
        ];
}

    public function queueTimeOut(Request $request){
        // DATA
        $mpesaResponse = file_get_contents('php://input');
        // log the response
        $logFile = "M_PESAConfirmationResponse.txt";
        // write to file
        $log = fopen($logFile, "a");
    
        fwrite($log, $mpesaResponse);
        fclose($log);
    
        echo $response;

        return [
            'ResultCode' => 0,
            'ResultDesc' => 'Accept Service',
            'ThirdPartyTransID' => rand(3000, 10000)
        ];
}

    public function result(Request $request){
        // DATA
        $mpesaResponse = file_get_contents('php://input');
        // log the response
        $logFile = "M_PESAConfirmationResponse.txt";
        // write to file
        $log = fopen($logFile, "a");
    
        fwrite($log, $mpesaResponse);
        fclose($log);
    
        echo $response;

        return [
            'ResultCode' => 0,
            'ResultDesc' => 'Accept Service',
            'ThirdPartyTransID' => rand(3000, 10000)
        ];
    }

    public function stkPushCallback(){
        //saving reponse to txt file
        $mpesaResponse = file_get_contents('php://input');
        $logFile = "MPESAConfirmationResponse.txt";
        $log = fopen($logFile, "a");
        fwrite($log, $mpesaResponse);
        fclose($log);
    }

    public function store(){
        //reading from txt file
        $file = \file_get_contents("MPESAConfirmationResponse.txt");
        $file2 = \json_decode($file, true);
        $stkCallBack = json_encode($file2['Body']['stkCallback']);
        $callBackData = json_decode($stkCallBack,true);
        $ResultCode = json_encode($file2['Body']['stkCallback']['ResultCode']);
        
        if($ResultCode == 0){
            //get user details data in CallbackMetadata
            $CallbackMetadata = json_encode($callBackData['CallbackMetadata']['Item']);
            $data = json_decode($CallbackMetadata,true);
            $Amount = json_encode($data[0]['Value']);
            $MpesaReceiptNumber = json_encode($data[1]['Value']);
            $TransactionDate = json_encode($data[3]['Value']);
            $PhoneNumber = json_encode($data[4]['Value']);

            //save to MPESA transactions table    
            $transaction = new Transaction;
            $transaction->user_id = 1;
            $transaction->type = "Deposit";
            $transaction->amount = $Amount;
            $transaction->receipt_number = str_replace(['"',"'"], "", $MpesaReceiptNumber);
            $transaction->transaction_date = $TransactionDate;
            $transaction->phone_number = $PhoneNumber;
            $transaction->status = "done";
            $transaction->save();
        }
    }

}
