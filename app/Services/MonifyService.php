<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
class MonifyService
{
    public static function login() {
        logs()->info("Monify Login");
        logs()->info(env('MONIFY_BASE_URL').'/api/v1/auth/login');
        logs()->info(env('MONIFY_API_KEY'));
        logs()->info(env('MONIFY_SECRET_KEY'));
        $response = Http::withBasicAuth(env('MONIFY_API_KEY'), env('MONIFY_SECRET_KEY'))->post(env('MONIFY_BASE_URL').'/api/v1/auth/login');
        logs()->info($response);
        return $response->json();
    }

    public static function resolveBankAccount($accountName, $customerEmail, $customerName, $accessToken) {
        logs()->info("Monify Resolve Bank Account");
        logs()->info(env('MONIFY_BASE_URL').'/api/v2/bank-transfer/reserved-accounts');
        $data = [
            "accountReference" => sha1(rand(100000,999999)),
            "accountName" => $accountName,
            "currencyCode" => "NGN",
            "contractCode" => env("MONIFY_CONTRACT_CODE"),
            "customerEmail" => $customerEmail,
            "customerName" => $customerName,
            "getAllAvailableBanks" => true
        ];
        logs()->info($data);
        $response = Http::withToken($accessToken)->post(env('MONIFY_BASE_URL').'/api/v2/bank-transfer/reserved-accounts', $data);
        logs()->info($response);
        return $response->json();
    }

    public static function paymentVerifaction($ref) {
        // api/v1/merchant/transactions/query?paymentReference=
        logs()->info("Monify paymentReference");
        logs()->info(env('MONIFY_BASE_URL').'/api/v1/merchant/transactions/query?paymentReference='.$ref);
        logs()->info(env('MONIFY_API_KEY'));
        logs()->info(env('MONIFY_SECRET_KEY'));
        $response = Http::withBasicAuth(env('MONIFY_API_KEY'), env('MONIFY_SECRET_KEY'))->get(env('MONIFY_BASE_URL').'/api/v1/merchant/transactions/query?paymentReference='.$ref);
        logs()->info($response);
        return $response->json();
    }
}
