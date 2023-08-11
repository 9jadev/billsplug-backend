<?php

namespace App\Http\Controllers;

use App\Models\ElectricityPurchase;
use Illuminate\Http\Request;
use App\Services\EletrictyPurchaseService;
use Carbon\Carbon;
class ElectricityPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $airtimes = ElectricityPurchase::whereNotNull("electricity_purchases.id")->where("customer_id", auth()->user()->id)->latest()->get();
        return response()->json(["message" => "successful", "data" => $airtimes], 200);
    }

    public function requestId() {
        return Carbon::now()->addHour()->format('YmdHis').rand(100000,999999);
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create(Request $request, EletrictyPurchaseService $eletrictyPurchaseService)
    {
        $request->validate([
            "amount" => "required|string",
            "serviceID" => "required|string",
            "billersCode" => "required",
            "variation_code" => "required"
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
        // return $buyAirtime["content"]["transactions"]["status"];
        if ($buyAirtime["content"]["transactions"]["status"] != "delivered") {
            auth()->user()->deposit($request->amount);
            return response()->json([
                "message" => $buyAirtime["response_description"],
                "status" => "error"
            ], 400);
        }
        $electricityPurchase = ElectricityPurchase::create([
            "amount" => $request->amount,
            "provider" => $request->serviceID,
            "phone" => $request->phone,
            "request_id" => $requestId,
            "token" => $buyAirtime["token"],
            "response" => json_encode($buyAirtime),
            "customer_id" => auth()->user()->id,
        ]);
        return response()->json([
            "message" => $buyAirtime["response_description"],
            "data" => $buyAirtime
        ], 200);
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
