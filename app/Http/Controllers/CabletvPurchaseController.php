<?php

namespace App\Http\Controllers;

use App\Models\CabletvPurchase;
use Illuminate\Http\Request;
use App\Services\CabletvService;
use Carbon\Carbon;

class CabletvPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $airtimes = CabletvPurchase::whereNotNull("cabletv_purchases.id")->where("customer_id", auth()->user()->id)->latest();
        request()->query("provider") ? $airtimes->where("provider",  request()->query("provider")) : $airtimes;
        $data = request()->query("page_number") ? $airtimes->paginate(request()->query("page_number")) : $airtimes->get();
        return response()->json(["message" => "successful", "data" => $data], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, CabletvService $cabletvService)
    {
        $request->validate([
            "amount" => "required|string",
            "serviceID" => "required|string",
            "phone" => "required|string",
            "billersCode" => "required",
            "variation_code" => "required",
            "subscription_type" => "required"
        ]);
        if ($request->serviceID == "showmax") {
            return $this->payforTv($request, $cabletvService);
        }

        $verifySmartCard = $cabletvService->verifyCard($request->billersCode,$request->serviceID);
        if ($verifySmartCard == null) {
            return response()->json([
                "message" => "error happend",
                "status" => "error"
            ], 400);
        }
        if ($verifySmartCard["code"] != "000") {
            return response()->json([
                "message" => $verifySmartCard["content"]["error"],
                "status" => "error"
            ], 400);
        }
        return $this->payforTv($request, $cabletvService);
    }

    public function verifyCard(Request $request, CabletvService $cabletvService) {
        $request->validate([
            "serviceID" => "required|string",
            "billersCode" => "required",
        ]);
        $verifySmartCard = $cabletvService->verifyCard($request->billersCode,$request->serviceID);
        return $verifySmartCard;
        // if ($verifySmartCard == null) {
        //     return response()->json([
        //         "message" => "error happend",
        //         "status" => "error"
        //     ], 400);
        // }
        // return response()->json([
        //     "message" => "error happend",
        //     "status" => "error"
        // ], 200);
    }

    public function requestId() {
        return Carbon::now()->addHour()->format('YmdHis').rand(100000,999999);
    }

    public function payforTv(Request $request, CabletvService $cabletvService)
    {
        $request->validate(["amount" => "required|string", "serviceID" => "required|string", "phone" => "required|string|min:11"]);

        $requestId = $this->requestId();
        if ($request->amount > auth()->user()->balance) {
            return response()->json(["message" => "Low Balance."], 400);
        }
        auth()->user()->withdraw((int) $request->amount,[
            "request_id" => $requestId,
            "type" => $request->serialId,
            "description" => "Purchase of ".strtoupper($request->serviceID). " CABLE TV"]);
        $buyAirtime = $cabletvService->buyCableTv($requestId,$request->subscription_type,$request->serviceID,$request->amount, $request->phone,1,$request->variation_code);
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
            // auth()->user()->deposit($request->amount);
        $dataPurchage = CabletvPurchase::create([
            "amount" => $request->amount,
            "provider" => $request->serviceID,
            "phone" => $request->phone,
            "request_id" => $requestId,
            "response" => json_encode($buyAirtime),
            "customer_id" => auth()->user()->id,
        ]);
        return response()->json([
            "message" => $buyAirtime["response_description"],
            "data" => $buyAirtime
        ], 200);
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
    public function show(CabletvPurchase $cabletvPurchase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CabletvPurchase $cabletvPurchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CabletvPurchase $cabletvPurchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CabletvPurchase $cabletvPurchase)
    {
        //
    }
}
