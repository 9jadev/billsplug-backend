<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
class EletrictyPurchaseService
{
    public static function verifyCard($billersCode, $serviceID, $type = 'prepaid') {
        logs()->info(env('SANDBOX_URL').'merchant-verify');
        $data = [
            "serviceID" => $serviceID,
            "billersCode" => $billersCode,
            "type" => $type
        ];
        logs()->info($data);
        $response = Http::withHeaders([
            'api-key' => env('APIKEY'),
            'secret-key' => env('SECRET_KEY')
        ])->post(env('SANDBOX_URL').'merchant-verify', $data);

        logs()->info($response);
        return $response->json();
    }

    public static function buyEletricity($requestId, $serviceID, $amount, $phone = '08011111111', $billersCode, $variation_code) {
        logs()->info(env('SANDBOX_URL').'pay');
        $data = [
            "request_id" => $requestId,
            "serviceID" => $serviceID,
            "billersCode" => $billersCode,
            "variation_code" => $variation_code,
            "amount" => $amount,
            "phone" => $phone
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
