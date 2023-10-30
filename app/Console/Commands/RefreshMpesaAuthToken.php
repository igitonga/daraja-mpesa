<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MpesaAuthToken;
use Illuminate\Support\Facades\Log;

class RefreshMpesaAuthToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpesa:refresh-auth-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh mpesa auth token used as access token by the other APIs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $securityCredential = base64_encode(env('MPESA_CONSUMER_KEY').':'.env('MPESA_CONSUMER_SECRET'));
    
        $mpesaAuthToken = MpesaAuthToken::first();

        $ch = curl_init(env('MPESA_BASE_URL').'/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic '.$securityCredential]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $decodedResponse = json_decode($response);

        if(is_null($decodedResponse))
            error_log("Token not generated");
            return;

        if(!$mpesaAuthToken)
            $model = new MpesaAuthToken;
            $model->token = $decodedResponse->access_token;
            $model->expires_at = now()->addSeconds($decodedResponse->expires_in);
            $model->save();

        $mpesaAuthToken->token = $decodedResponse->access_token;
        $mpesaAuthToken->expires_at = now()->addSeconds($decodedResponse->expires_in);
        $mpesaAuthToken->save();

        error_log("Authorization token saved...");
        return;
    }
}
