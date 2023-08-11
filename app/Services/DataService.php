<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
class DataService
{
    public static function buyData($requestId, $serviceID,$amount, $phone = '08011111111',$quantity,$variation_code) {
        logs()->info(env('SANDBOX_URL').'pay');
        $data = [
            "request_id" => $requestId,
            "serviceID" => $serviceID,
            "billersCode" => $phone,
            "quantity" => $quantity,
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

    public static function listPackages($serviceID) {
        logs()->info(env('SANDBOX_URL').'service-variations?serviceID='.$serviceID);
        $response = Http::withHeaders([
            'api-key' => env('APIKEY'),
            'public-key' => env('PUBLICKEY')
        ])->get(env('SANDBOX_URL').'service-variations?serviceID='.$serviceID);
        logs()->info($response);
        return $response->json();
    }

    public static function buyAirtime($requestId, $serviceID,$amount, $phone = '08011111111') {
        logs()->info(env('SANDBOX_URL').'pay');
        $data = [
            "request_id" => $requestId,
            "serviceID" => $serviceID,
            "amount" => $amount,
            "phone" => $phone
        ];
    }
}
