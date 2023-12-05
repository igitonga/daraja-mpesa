<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MpesaResponseController extends Controller
{
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

    public function b2cQueue(Request $request, $id){
        Log::info("------------Callback response-------------");

        $json = file_get_contents('php://input');
        $obj = json_decode($json, TRUE);

        Log::info($obj);

        $transaction = Transaction::find($id);
        
        $transactionCallback = new TransactionCallback;
        $transactionCallback->transaction_id = $transaction->id;
        $transactionCallback->conversation_id = $obj['Result']['ConversationID'];
        $transactionCallback->result_description = $obj['Result']['ResultDesc'];
        $transactionCallback->save();

        if($obj['Result']['ResultCode'] == 0){
            $transaction->status = "success";
            $transaction->save();
        }
        else{
            $transaction->status = "failed";
            $transaction->save();    
        }

    }

    public function b2cResult(Request $request, $id){
        Log::info("------------Callback response-------------");

        $json = file_get_contents('php://input');
        $obj = json_decode($json, TRUE);

        Log::info($obj);

        $transaction = Transaction::find($id);
        
        $transactionCallback = new TransactionCallback;
        $transactionCallback->transaction_id = $transaction->id;
        $transactionCallback->conversation_id = $obj['Result']['ConversationID'];
        $transactionCallback->result_description = $obj['Result']['ResultDesc'];
        $transactionCallback->save();

        if($obj['Result']['ResultCode'] == 0){
            $transaction->status = "success";
            $transaction->save();
        }
        else{
            $transaction->status = "failed";
            $transaction->save();    
        }

    }
}
