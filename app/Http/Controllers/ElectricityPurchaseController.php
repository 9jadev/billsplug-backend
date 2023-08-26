<?php

namespace App\Http\Controllers;

use App\Models\ElectricityPurchase;
use Illuminate\Http\Request;
use App\Services\EletrictyPurchaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
class ElectricityPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $airtimes = ElectricityPurchase::whereNotNull("electricity_purchases.id")->where("customer_id", auth()->user()->id)->latest()->get();
        // return response()->json(["message" => "successful", "data" => $airtimes], 200);

        $airtimes = ElectricityPurchase::whereNotNull("electricity_purchases.id")->where("customer_id", auth()->user()->id)->latest();
        request()->query("provider") ? $airtimes->where("provider",  request()->query("provider")) : $airtimes;
        $data = request()->query("page_number") ? $airtimes->paginate(request()->query("page_number")) : $airtimes->get();
        return response()->json(["message" => "successful", "data" => $data], 200);
    }

    public function requestId() {
        return Carbon::now()->addHour()->format('YmdHis').rand(100000,999999);
    }

    /**
     * Show the form for creating a new resource.
     */

     public function searchElectricity(Request $request, EletrictyPurchaseService $eletrictyPurchaseService) {
        $request->validate([
            "serviceID" => "required|string",
            "billersCode" => "required",
            "type" => "required"
        ]);
        $verify_eletrictyPurchaseService = $eletrictyPurchaseService->verifyCard(
            $request->billersCode,
            $request->serviceID,
            $request->type
        );
        return response()->json(["message" => "Success", "data" => $verify_eletrictyPurchaseService], 200);
     }

    public function create(Request $request, EletrictyPurchaseService $eletrictyPurchaseService)
    {
        $request->validate([
            "amount" => "required|string",
            "serviceID" => "required|string",
            "billersCode" => "required",
            "variation_code" => "required",
            "fullfee" => "required"
        ]);
        $verify_eletrictyPurchaseService = $eletrictyPurchaseService->verifyCard(
            $request->billersCode,
            $request->serviceID,
            $request->variation_code
        );
        if ($verify_eletrictyPurchaseService == null) {
            return response()->json([
                "message" => "error happend",
                "status" => "error"
            ], 400);
        }
        if (isset($verify_eletrictyPurchaseService["content"]["error"])) {
            return response()->json([
                "message" => $verify_eletrictyPurchaseService["content"]["error"],
                "status" => "error"
            ], 400);
        }
        return $this->payforTv($request, $eletrictyPurchaseService);
    }

    public function payforTv(Request $request, EletrictyPurchaseService $eletrictyPurchaseService)
    {
        $requestId = $this->requestId();
        if ($request->amount+100 > auth()->user()->balance) {
            return response()->json(["message" => "Low Balance."], 400);
        }
        auth()->user()->withdraw((int) $request->amount+100,[
            "request_id" => $requestId,
            "type" => $request->serialId,
            "description" => "Purchase of ".strtoupper($request->serviceID). " ELECTRICITY"
        ]);
        $buyAirtime = $eletrictyPurchaseService->buyEletricity(
            $requestId,
            $request->serviceID,
            $request->amount,
            $request->phone,
            $request->billersCode,
            $request->variation_code
        );
        if ($buyAirtime == null) {
            auth()->user()->deposit($request->amount);
            return response()->json([
                "message" => "error happend",
                "status" => "error"
            ], 400);
        }
        if ($buyAirtime["code"] != "000") {
            return response()->json([
                "message" => $buyAirtime["response_description"],
                "status" => "error"
            ], 400);
        }
        // return $$buyAirtime;
        $electricityPurchase = ElectricityPurchase::create([
            "amount" => $request->amount,
            "provider" => $request->serviceID,
            "phone" => $request->phone,
            "request_id" => $requestId,
            "token" =>  isset($buyAirtime["token"]) ? $buyAirtime["token"] : "",
            "response" => json_encode($buyAirtime),
            "customer_id" => auth()->user()->id,
        ]);
        if (isset($buyAirtime["token"])) {
            $this->handleSendToken($request->phone, $buyAirtime["token"]);
        }

        return response()->json([
            "message" => " Your Token ".$buyAirtime["response_description"],
            "data" => $buyAirtime
        ], 200);
    }



    public function handleSendToken($phone, $data)
    {
        try {
            // $client = new \GuzzleHttp\Client();
            // $response = $client->request('POST', 'https://api.sendchamp.com/api/v1/sms/send', [
            //     'body' => '{"to":["'.$phone.'"],"sender_name":"Alertapay","message":"'.$data.'","route":"dnd"}',
                // 'headers' => [
                    // 'Accept' => 'application/json',
                    // 'Authorization' => 'Bearer '.env('SENDCHAMP'),
                    // 'Content-Type' => 'application/json',
                // ],
            // ]);
            // $vom = $response->getBody();
            $data = [
                "to" => [Str::of($phone)->replaceFirst('0', '+234')],
                "sender_name" => "Alertapay",
                "message" => $data,
                "route" => "dnd"
            ];

            logs()->info($data);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.env('SENDCHAMP'),
                'Content-Type' => 'application/json',
            ])->post('https://api.sendchamp.com/api/v1/sms/send', $data);
            logs()->info($response);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function payElectricityBill() {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ElectricityPurchase $electricityPurchase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ElectricityPurchase $electricityPurchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ElectricityPurchase $electricityPurchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ElectricityPurchase $electricityPurchase)
    {
        //
    }
}
