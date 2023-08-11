<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
class AirtimeService
{
    public static function buyAirtime($requestId, $serviceID,$amount, $phone = '08011111111') {
        logs()->info(env('SANDBOX_URL').'pay');
        $data = [
            "request_id" => $requestId,
            "serviceID" => $serviceID,
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

    public static function buyAirtimeInternational($requestId, $serviceID,$billersCode, $variation_code, $amount, $phone = '08011111111', $operator_id, $country_code, $product_type_id, $email) {
        logs()->info(env('SANDBOX_URL').'pay');
        $data = [
            "request_id" => $requestId,
            "serviceID" => $serviceID,
            "billersCode" => $billersCode,
            "variation_code" => $variation_code,
            "amount" => $amount,
            "phone" => $phone,
            "operator_id" => $operator_id,
            "country_code" => $country_code,
            "product_type_id" => $product_type_id,
            "email" => $email
        ];
        logs()->info($data);
        $response = Http::withHeaders([
            'api-key' => env('APIKEY'),
            'secret-key' => env('SECRET_KEY')
        ])->post(env('SANDBOX_URL').'pay', $data);

        logs()->info($response);
        return $response->json();
    }

    public static function requestId() {
        return Carbon::now()->addHour()->format('YmdHis').rand(100000,999999);
    }

    public static function getInternationalAirtimeCountries() {
        $response = Http::withHeaders([
            'api-key' => env('APIKEY'),
            'public-key' => env('PUBLICKEY')
        ])->get(env('SANDBOX_URL').'get-international-airtime-countries');
        logs()->info($response);
        return $response->json();
    }

    public static function getinternationalAirtimeProductTypes($code) {
        $response = Http::withHeaders([
            'api-key' => env('APIKEY'),
            'public-key' => env('PUBLICKEY')
        ])->get(env('SANDBOX_URL').'get-international-airtime-product-types?code='.$code);
        logs()->info($response);
        return $response->json();
    }

    public static function getinternationalAirtimeProduct($code, $product) {
        $response = Http::withHeaders([
            'api-key' => env('APIKEY'),
            'public-key' => env('PUBLICKEY')
        ])->get(env('SANDBOX_URL').'get-international-airtime-operators?code='.$code.'&product_type_id='.$product);
        logs()->info($response);
        return $response->json();
    }

    public static function getinternationalServiceVariation($operator_id, $product_type_id) {
        logs()->info(env('SANDBOX_URL').'service-variations?serviceID=foreign-airtime&operator_id='.$operator_id.'&product_type_id='.$product_type_id);
        $response = Http::withHeaders([
            'api-key' => env('APIKEY'),
            'public-key' => env('PUBLICKEY')
        ])->get(env('SANDBOX_URL').'service-variations?serviceID=foreign-airtime&operator_id='.$operator_id.'&product_type_id='.$product_type_id);
        logs()->info($response);
        return $response->json();
    }
}
