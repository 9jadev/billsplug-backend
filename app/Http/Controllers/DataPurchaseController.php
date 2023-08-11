<?php

namespace App\Http\Controllers;

use App\Models\DataPurchase;
use App\Services\DataService;
use Illuminate\Http\Request;
use Carbon\Carbon;
class DataPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $airtimes = DataPurchase::whereNotNull("data_purchases.id")->where("customer_id", auth()->user()->id)->latest();
        request()->query("provider") ? $airtimes->where("provider",  request()->query("provider")) : $airtimes;
        $data =  request()->query("page_number") ? $airtimes->paginate(request()->query("page_number")) : $airtimes->get();
        return response()->json(["message" => "successful", "data" => $data], 200);
    }

    public function requestId() {
        return Carbon::now()->addHour()->format('YmdHis').rand(100000,999999);
    }

    public function listPackages(DataService $dataService,$serviceID) {
        $listPackages = $dataService->listPackages($serviceID);
        return response()->json(["message" => " Fetched Successfully.", "data" => $listPackages], 200);
        // return Carbon::now()->addHour()->format('YmdHis').rand(100000,999999);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, DataService $dataService)
    {
        $request->validate([
            "amount" => "required",
            "serviceID" => "required|string",
            "phone" => "required|string|min:11",
            "billersCode" => "required",
            "variation_code" => "required"
        ]);
        if ($request->amount > auth()->user()->balance) {
            return response()->json(["message" => "Low Balance."], 400);
        }
        $requestId = $this->requestId();
        auth()->user()->withdraw((int) $request->amount,[
            "request_id" => $requestId,
            "type" => $request->serialId,
            "description" => "Purchase of ".strtoupper($request->serviceID). " DATA"]);

        $buyAirtime = $dataService->buyData($requestId, $request->serviceID,$request->amount, $request->phone,1,$request->variation_code);
        if ($buyAirtime == null) {
            auth()->user()->deposit($request->amount);
            return response()->json([
                "message" => "error happend",
                "status" => "error"
            ], 400);
        }
        // return $buyAirtime;
        if ($buyAirtime["code"] != "000") {
            // auth()->user()->deposit($request->amount);
            return response()->json([
                "message" => $buyAirtime["response_description"],
                "status" => "error"
            ], 400);
        }

        $dataPurchage = DataPurchase::create([
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

    }

    /**
     * Display the specified resource.
     */
    public function show(DataPurchase $dataPurchase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DataPurchase $dataPurchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DataPurchase $dataPurchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DataPurchase $dataPurchase)
    {
        //
    }
}
