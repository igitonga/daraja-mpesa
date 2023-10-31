<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MpesaAuthToken;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MpesaController;

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
        $mpesaController = new MpesaController;
        $mpesaAuthToken = MpesaAuthToken::first();
        
        $auth = $mpesaController->getAccessToken(env('MPESA_CONSUMER_KEY'),env('MPESA_CONSUMER_SECRET'));

        if(is_null($mpesaAuthToken)){
            $model = new MpesaAuthToken;
            $model->token = $auth->access_token;
            $model->expires_at = now()->addSeconds($auth->expires_in);
            $model->save();
            error_log("Authorization token saved...");
        }
        elseif($mpesaAuthToken)   { 
            $mpesaAuthToken->token = $auth->access_token;
            $mpesaAuthToken->expires_at = now()->addSeconds($auth->expires_in);
            $mpesaAuthToken->save();
            error_log("Authorization token saved...");
        }
        else{
            error_log("Token not generated");
        }
    }
}
