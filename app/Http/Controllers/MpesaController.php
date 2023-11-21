<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Session;
use App\Models\MpesaAuthToken;
use App\Models\TransactionCallback;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MpesaController extends Controller
{    
    // generating access token >> Auth
    public function getAccessToken($consumerKey,$consumerSecert){
        $securityCredential = base64_encode($consumerKey.':'.$consumerSecert);

        $ch = curl_init(env('MPESA_BASE_URL').'/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic '.$securityCredential]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }

    public function makePayment($token,$body,$url){
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$token,
            'Content-Type: application/json',
        ),
        ));

        $response = json_decode(curl_exec($curl));

        curl_close($curl);

        return $response;
    }

    //customer to business simulation
    public function c2b(){
        $auth = MpesaAuthToken::first();
        $url = env('MPESA_BASE_URL').'/mpesa/c2b/v1/registerurl';

        $body = array(
            "ShortCode" => env('MPESA_SHORTCODE'),
            "CommandID" => "CustomerPayBillOnline",
            "Amount" => "1",
            "Msisdn" => $request->phone,
            "BillRefNumber" => "MI1"
        );
        $response = $this->makePayment($auth->token,$body,$url);
        
        if($response->ResponseCode == "0"){
            Session::flash('Success','Input your mpesa pin'); 
            return redirect()->back();
         }
         else{
            Session::flash('error','Something went wrong'); 
            return redirect()->back();
         }
    }

    //business to customer simulation
    public function b2c(Request $request){
        $body = array(
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
        );  
        
        if($response->ResponseCode == "0"){
            Session::flash('Success','URL successfully registered'); 
            return redirect()->back();
         }
         else{
            Session::flash('error','Something went wrong'); 
            return redirect()->back();
         }
    }

    //stkpush customer to business
    public function stkPush(Request $request){
        $auth = MpesaAuthToken::first();
        $url = env('MPESA_BASE_URL').'/mpesa/stkpush/v1/processrequest';

        $BusinessShortCode = 174379;
        $Timestamp = date('YmdHis');
        $PasswordKey = env('MPESA_PASS_KEY');
        $Password=base64_encode($BusinessShortCode.$PasswordKey.$Timestamp);

        $transaction = new Transaction;
        $transaction->amount = $request->amount;
        $transaction->type = 'c2b';
        $transaction->phone_number = $request->phone;
        $transaction->save();

        $body = array(
            "BusinessShortCode" => $BusinessShortCode,
            "Password" => $Password,
            "Timestamp" => $Timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => $request->amount,
            "PartyA" => $request->phone,
            "PartyB" => $BusinessShortCode,
            "PhoneNumber" => $request->phone,
            "CallBackURL" => env('MPESA_CALLBACK_URL').'/api/response/callback/'.$transaction->id,
            "AccountReference" => env('APP_NAME'),
            "TransactionDesc" => "Deposit"
        );

        $response = $this->makePayment($auth->token,$body,$url);

        if(isset($response->errorCode)){
            if($response->errorCode == "404.001.03"){
                Session::flash('error','Access token has expired'); 
                return redirect()->back();
            }
        }

        if($response->ResponseCode == "0"){
            Session::flash('Success','Input your mpesa pin'); 
            return redirect()->back();
         }
         else{
            Session::flash('error','Something went wrong'); 
            return redirect()->back();
         }
    }

    public function dynamicQRcode(Request $request){
        $auth = MpesaAuthToken::first();
        $url = env('MPESA_BASE_URL').'/mpesa/qrcode/v1/generate';

        $body = array(
            "MerchantName" => 'LC Gitonga',
            "RefNo" => 'Payment001',
            "Amount" => $request->amount,
            "TrxCode" => "BG",
            "CPI" => "373132",
            "CPI" => "300",
        ); 

        $response = $this->makePayment($auth->token,$body,$url);

        if($response->ResponseCode == "0"){
            $code = $response->QRCode;
            Session::flash('Success','Read QR code below'); 
            return view('welcome', compact('code'));
         }
         else{
            Session::flash('error','Something went wrong'); 
            return redirect()->back();
         }
    }

    public function b2b(Request $request){
        $auth = MpesaAuthToken::first();
        $url = env('MPESA_BASE_URL').'/v1/ussdpush/get-msisdn';

        $transaction = new Transaction;
        $transaction->amount = $request->amount;
        $transaction->type = 'b2b';
        $transaction->save();

        $body = array(
            "primaryShortCode" => 000001,
            "receiverShortCode" => 000002,
            "amount" => $request->amount,
            "paymentRef" => "Yeey B2B",
            "CallBackURL" => 'https://eed3-41-80-118-221.ngrok-free.app',
            "partnerName" => "Vendor",
            "RequestRefID" => Str::random(12),
        );

        $response = $this->makePayment($auth->token,$body,$url);
        dd($response);
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
    public function responseCallback(Request $request, $id){
        Log::info("------------Callback response-------------");

        $json = file_get_contents('php://input');
        $obj = json_decode($json, TRUE);

        Log::info($obj);

        $transaction = Transaction::find($id);
        
        $transactionCallback = new TransactionCallback;
        $transactionCallback->transaction_id = $transaction->id;
        $transactionCallback->merchant_request_id = $obj['Body']['stkCallback']['MerchantRequestID'];
        $transactionCallback->checkout_request_id = $obj['Body']['stkCallback']['CheckoutRequestID'];
        $transactionCallback->result_description = $obj['Body']['stkCallback']['ResultDesc'];
        $transactionCallback->callback_metadata = '';
        $transactionCallback->save();

        if($obj['Body']['stkCallback']['ResultCode'] == 0){
            $transaction->status = "success";
            $transaction->save();
        }
        else{
            $transaction->status = "failed";
            $transaction->save();    
        }

    }

}
