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
        $auth = MpesaAuthToken::first();
        $url = env('MPESA_BASE_URL').'/mpesa/b2c/v3/paymentrequest';

        $transaction = new Transaction;
        $transaction->amount = $request->amount;
        $transaction->type = 'b2c';
        $transaction->phone_number = $request->phone;
        $transaction->save();

        $body = array(
            "InitiatorName" => "John Doe",
            "SecurityCredential" => env('MPESA_SECURITY_CREDENTIALS'),
            "CommandID" => "BusinessPayment",
            "Amount" => $request->amount,
            "PartyA" => env('MPESA_SHORTCODE'),
            "PartyB" => $request->phone,
            "Remarks" => "None",
            "QueueTimeOutURL" => env('MPESA_CALLBACK_URL')."/api/queue/".$transaction->id,
            "ResultURL" => env('MPESA_CALLBACK_URL')."/api/result/".$transaction->id,
            "Occasion" => "None"
        );  
        
        if(isset($response->errorCode)){
            if($response->errorCode == "404.001.03"){
                Session::flash('error','Access token has expired'); 
                return redirect()->back();
            }
        }

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
      //dd($response);  
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


    /*
     *Responses coming from SAFARICOM
     */
    public function c2bStkpushCallback(Request $request, $id){
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

    public function b2bCallback(Request $request, $id){
        Log::info("------------Callback response-------------");

        $json = file_get_contents('php://input');
        $obj = json_decode($json, TRUE);

        Log::info($obj);

        $transaction = Transaction::find($id);
        
        $transactionCallback = new TransactionCallback;
        $transactionCallback->transaction_id = $transaction->id;
        $transactionCallback->conversation_id = $obj['conversationID'];
        $transactionCallback->request_id = $obj['requestId'];
        $transactionCallback->result_description = $obj['resultDesc'];
        $transactionCallback->callback_metadata = '';
        $transactionCallback->save();

        if($obj['resultCode'] == 0){
            $transaction->status = "success";
            $transaction->save();
        }
        else{
            $transaction->status = "failed";
            $transaction->save();    
        }

    }

    public function queue(Request $request, $id){
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
