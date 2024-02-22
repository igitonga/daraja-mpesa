<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Session;
use App\Models\MpesaAuthToken;
use App\Models\TransactionCallback;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Artisan;
use DB;

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

    //business to customer simulation
    public function b2c(Request $request){
        DB::beginTransaction();
        $auth = MpesaAuthToken::first();

        if(is_null($auth))
            Artisan::call("mpesa:refresh-auth-token");
            $auth = MpesaAuthToken::first();

        $url = env('MPESA_BASE_URL').'/mpesa/b2c/v3/paymentrequest';

        $transaction = new Transaction;
        $transaction->amount = $request->amount;
        $transaction->type = 'b2c';
        $transaction->phone_number = $request->phone;
        $transaction->save();

        $body = array(
            "OriginatorConversationID" => Str::random(12),
            "InitiatorName" => "testapi",
            "SecurityCredential" => env('MPESA_SECURITY_CREDENTIALS'),
            "CommandID" => "BusinessPayment",
            "Amount" => $request->amount,
            "PartyA" => env('MPESA_SHORTCODE'),
            "PartyB" => $request->phone,
            "Remarks" => "None",
            "QueueTimeOutURL" => env('MPESA_CALLBACK_URL')."/api/b2c/queue/".$transaction->id,
            "ResultURL" => env('MPESA_CALLBACK_URL')."/api/b2c/result/".$transaction->id,
            "Occasion" => "None"
        ); 
        
        $response = $this->makePayment($auth->token,$body,$url);
        
        if(isset($response->errorCode)){
            Session::flash('error',$response->errorMessage); 
            DB::rollback();
            return redirect()->back();
        }

        if($response->ResponseCode == "0"){
            Session::flash('Success','Transaction was successful'); 
            DB::commit();
            return redirect()->back();
         }
         else{
            Session::flash('error','Something went wrong'); 
            DB::rollback();
            return redirect()->back();
         }
    }

    //stkpush customer to business
    public function stkPush(Request $request){
        DB::beginTransaction();
        $auth = MpesaAuthToken::first();

        if(is_null($auth))
            Artisan::call("mpesa:refresh-auth-token");
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
            "CallBackURL" => env('MPESA_CALLBACK_URL').'/api/c2b/stkpush/callback/'.$transaction->id,
            "AccountReference" => env('APP_NAME'),
            "TransactionDesc" => "Deposit"
        );

        $response = $this->makePayment($auth->token,$body,$url);

        if(isset($response->errorCode)){
            Session::flash('error',$response->errorMessage); 
            DB::rollback();
            return redirect()->back();
        }

        if($response->ResponseCode == "0"){
            Session::flash('Success','Input your mpesa pin'); 
            DB::commit();
            return redirect()->back();
         }
         else{
            Session::flash('error','Something went wrong'); 
            DB::rollback();
            return redirect()->back();
         }
    }

    public function dynamicQRcode(Request $request){
        DB::beginTransaction();
        $auth = MpesaAuthToken::first();

        if(is_null($auth))
            Artisan::call("mpesa:refresh-auth-token");
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

        if(isset($response->errorCode)){
            Session::flash('error',$response->errorMessage); 
            DB::rollback();
            return redirect()->back();
        }

        if( isset($response->ResponseCode) && $response->ResponseCode == "0"){
            $code = $response->QRCode;
            Session::flash('Success','Read QR code below'); 
            return view('welcome', compact('code'));
         }
         else{
            Session::flash('error','Something went wrong'); 
            DB::rollback();
            return redirect()->back();
         }
    }

    public function b2b(Request $request){
        DB::beginTransaction();
        $auth = MpesaAuthToken::first();

        if(is_null($auth))
            Artisan::call("mpesa:refresh-auth-token");
            $auth = MpesaAuthToken::first();

        $url = env('MPESA_BASE_URL').'/v1/ussdpush/get-msisdn';

        $transaction = new Transaction;
        $transaction->amount = $request->amount;
        $transaction->type = 'b2b';
        $transaction->save();

        $body = array(
            "primaryShortCode" => "7318002",
            "receiverShortCode" => "174379",
            "amount" => $request->amount,
            "paymentRef" => "TestAccount",
            "callbackUrl" => env('MPESA_CALLBACK_URL').'/api/b2b/callback/'.$transaction->id,
            "partnerName" => "Test",
            "RequestRefID" => "ODk4O-Tk4NWU4O-DQ66HD-D4OThkY"
        );

        $response = $this->makePayment($auth->token,$body,$url);
      
        if(isset($response->errorCode)){
            Session::flash('error',$response->errorMessage); 
            DB::rollback();
            return redirect()->back();
        }
        
        if($response->ResponseBody->code == "0"){
            Session::flash('Success','Transaction initiated'); 
            DB::commit();
            return redirect()->back();
         }
         else{
            Session::flash('error','Something went wrong'); 
            DB::rollback();
            return redirect()->back();
         }
    }

    public function buyGoodsAndServices(Request $request){
        DB::beginTransaction();
        $auth = MpesaAuthToken::first();

        if(is_null($auth))
            Artisan::call("mpesa:refresh-auth-token");
            $auth = MpesaAuthToken::first();

        $url = env('MPESA_BASE_URL').'/mpesa/b2b/v1/paymentrequest';

        $transaction = new Transaction;
        $transaction->amount = $request->amount;
        $transaction->type = 'buyGoodsAndServices';
        $transaction->save();

        $body = array(
            "Initiator" => "API_Usename",
            "SecurityCredential" => "GisJmyDdFZsNfh0EkOTqAE25ONer10DlcdLXt4yC2kQhG+9LSlO/PjfSqv6MGOZkp35EkRUEm/oX2jbp3XtJ0YmQNqL+Iqbkd+vYTCKGd0XHqayDTtlW1+t5Zwu2XQBNRjNb2ShpV49o9syHGgZ8Gw5ejkj9t+T4UDNJQBWykq/vxYw+KyqotnsWFOAB8GRO3k77QtQPHELbogd+dVb2UXGJc8MFrLpnqt7JmcbdIew7gN+fOosA1VyRV8I/FJuh69AuB3M7O57+nlz0FDzbHuBwUwctE3dp0ixMFICeE7sj/cAb+n4cZ+Uqxij2FG52e0IVwKB7hg1oEGCwrV7XSw==",
            "CommandID" => "BusinessPayBill",
            "SenderIdentifierType" => "4",
            "RecieverIdentifierType" => "4",
            "Amount" => $request->amount,
            "PartyA" => "123456",
            "PartyB" => "000000",
            "AccountReference" => "353353",
            "Requester" => "254700000000",
            "Remarks" => "OK",
            "QueueTimeOutURL" => env('MPESA_CALLBACK_URL')."/buy-goods-services/queue/".$transaction->id,
            "ResultURL" => env('MPESA_CALLBACK_URL')."/buy-goods-services/result/".$transaction->id,
        );

        $response = $this->makePayment($auth->token,$body,$url);
      //dd($response);
        if(isset($response->errorCode)){
            Session::flash('error',$response->errorMessage); 
            DB::rollback();
            return redirect()->back();
        }
        
        if($response->ResponseCode == "0"){
            Session::flash('Success','Transaction initiated'); 
            DB::commit();
            return redirect()->back();
         }
         else{
            Session::flash('error','Something went wrong'); 
            DB::rollback();
            return redirect()->back();
         }
    }

    public function paybill(Request $request){
        DB::beginTransaction();
        $auth = MpesaAuthToken::first();

        if(is_null($auth))
            Artisan::call("mpesa:refresh-auth-token");
            $auth = MpesaAuthToken::first();

        $url = env('MPESA_BASE_URL').'/mpesa/b2b/v1/paymentrequest ';

        $transaction = new Transaction;
        $transaction->amount = $request->amount;
        $transaction->type = 'paybill';
        $transaction->save();

        $body = array(
            "Initiator" => "API_Usename",
            "SecurityCredential" => "GisJmyDdFZsNfh0EkOTqAE25ONer10DlcdLXt4yC2kQhG+9LSlO/PjfSqv6MGOZkp35EkRUEm/oX2jbp3XtJ0YmQNqL+Iqbkd+vYTCKGd0XHqayDTtlW1+t5Zwu2XQBNRjNb2ShpV49o9syHGgZ8Gw5ejkj9t+T4UDNJQBWykq/vxYw+KyqotnsWFOAB8GRO3k77QtQPHELbogd+dVb2UXGJc8MFrLpnqt7JmcbdIew7gN+fOosA1VyRV8I/FJuh69AuB3M7O57+nlz0FDzbHuBwUwctE3dp0ixMFICeE7sj/cAb+n4cZ+Uqxij2FG52e0IVwKB7hg1oEGCwrV7XSw==",
            "CommandID" => "BusinessPayBill",
            "SenderIdentifierType" => "4",
            "RecieverIdentifierType" => "4",
            "Amount" => $request->amount,
            "PartyA" => "123456",
            "PartyB" => "000000",
            "AccountReference" => "353353",
            "Requester" => "254700000000",
            "Remarks" => "OK",
            "QueueTimeOutURL" => env('MPESA_CALLBACK_URL')."/paybill/queue/".$transaction->id,
            "ResultURL" => env('MPESA_CALLBACK_URL')."/paybill/result/".$transaction->id,
        );

        $response = $this->makePayment($auth->token,$body,$url);
      //dd($response);
        if(isset($response->errorCode)){
            Session::flash('error',$response->errorMessage); 
            DB::rollback();
            return redirect()->back();
        }
        
        if($response->ResponseCode == "0"){
            Session::flash('Success','Transaction initiated'); 
            DB::commit();
            return redirect()->back();
         }
         else{
            Session::flash('error','Something went wrong'); 
            DB::rollback();
            return redirect()->back();
         }
    }


}
