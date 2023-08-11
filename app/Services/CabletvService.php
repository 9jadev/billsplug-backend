<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
class CabletvService
{
    public static function verifyCard($billersCode, $serviceID) {
        logs()->info(env('SANDBOX_URL').'merchant-verify');
        $data = [
            "serviceID" => $serviceID,
            "billersCode" => $billersCode,

        ];
        logs()->info($data);
        $response = Http::withHeaders([
            'api-key' => env('APIKEY'),
            'secret-key' => env('SECRET_KEY')
        ])->post(env('SANDBOX_URL').'merchant-verify', $data);

        logs()->info($response);
        return $response->json();
    }
    public static function buyCableTv($requestId, $subscription_type, $serialId,$amount, $phone = '08011111111',$quantity = '1',$variation_code) {
        logs()->info(env('SANDBOX_URL').'pay');
        $data = [
            "subscription_type" => $subscription_type,
            "request_id" => $requestId,
            "serviceID" => $serialId,
            "billersCode" => $phone,
            "amount" => $amount,
            "phone" => $phone,
            "variation_code" => $variation_code
        ];
        logs()->info($data);
        $response = Http::withHeaders([
            'api-key' => env('APIKEY'),
            'secret-key' => env('SECRET_KEY')
        ])->post(env('SANDBOX_URL').'pay', $data);

        logs()->info($response);
        return $response->json();
    }

}
