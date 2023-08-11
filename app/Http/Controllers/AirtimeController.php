<?php

namespace App\Http\Controllers;

use App\Models\Airtime;
use App\Services\AirtimeService;
use Illuminate\Http\Request;
use Carbon\Carbon;
class AirtimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function requestId() {
        return Carbon::now()->addHour()->format('YmdHis').rand(100000,999999);
    }
    public function index()
    {
        $airtimes = Airtime::whereNotNull("airtimes.id")->where("customer_id", auth()->user()->id)->latest();
        request()->query("provider") ? $airtimes->where("provider",  request()->query("provider")) : $airtimes;
        $data = request()->query("page_number") ? $airtimes->paginate(request()->query("page_number")) : $airtimes->get();
        return response()->json(["message" => "successful", "data" => $data], 200);
    }

    public function getInternationalAirtimeCountries(AirtimeService $airtimeService) {
        $getInternationalAirtimeCountries = $airtimeService->getInternationalAirtimeCountries();
        if ($getInternationalAirtimeCountries == null) {
            return response()->json([
                "message" => "error happend",
                "status" => "error"
            ], 400);
        }

        return response()->json([
            "message" => $getInternationalAirtimeCountries["response_description"],
            "data" => $getInternationalAirtimeCountries["content"]["countries"]
        ], 200);
    }

    public function getinternationalAirtimeProductTypes($code,AirtimeService $airtimeService) {
        $getinternationalAirtimeProductTypes = $airtimeService->getinternationalAirtimeProductTypes($code);
        if ($getinternationalAirtimeProductTypes == null) {
            return response()->json([
                "message" => "error happend",
                "status" => "error"
            ], 400);
        }

        return response()->json([
            "message" => $getinternationalAirtimeProductTypes["response_description"],
            "data" => $getinternationalAirtimeProductTypes["content"]
        ], 200);
    }

    public function getinternationalAirtimeOperator($code,$product,AirtimeService $airtimeService) {
        $getinternationalAirtimeProductTypes = $airtimeService->getinternationalAirtimeProduct($code, $product);
        if ($getinternationalAirtimeProductTypes == null) {
            return response()->json([
                "message" => "error happend",
                "status" => "error"
            ], 400);
        }

        return response()->json([
            "message" => $getinternationalAirtimeProductTypes["response_description"],
            "data" => $getinternationalAirtimeProductTypes["content"]
        ], 200);
    }

    public function getinternationalServiceVariation($operator_id, $product_type_id, AirtimeService $airtimeService) {
        $getinternationalAirtimeServiceVariation = $airtimeService->getinternationalServiceVariation($operator_id, $product_type_id);
        if ($getinternationalAirtimeServiceVariation == null) {
            return response()->json([
                "message" => "error happend",
                "status" => "error"
            ], 400);
        }

        return response()->json([
            "message" => $getinternationalAirtimeServiceVariation["response_description"],
            "data" => $getinternationalAirtimeServiceVariation
        ], 200);
    }

    public function buyAirTime(Request $request, AirtimeService $airtimeService) {
        $request->validate(["amount" => "required|string", "serialId" => "required|string", "phone" => "required|string|min:11"]);

        $requestId = $this->requestId();

        if ($request->amount > auth()->user()->balance) {
            return response()->json(["message" => "Low Balance."], 400);
        }
        auth()->user()->withdraw($request->amount, [
            "request_id" => $requestId,
            "type" => $request->serialId,
            "description" => "Purchase of ".strtoupper($request->serialId). " AIRTIME"]
        );
        $buyAirtime = $airtimeService->buyAirtime($requestId, $request->serialId,$request->amount, $request->phone);
        if ($buyAirtime == null) {
            auth()->user()->deposit($request->amount);
            return response()->json([
                "message" => "error happend",
                "status" => "error"
            ], 400);
        }
        // if ($buyAirtime["content"]["transactions"]["status"] != "delivered") {
        //     auth()->user()->deposit($request->amount);
        //     return response()->json([
        //         "message" => $buyAirtime["response_description"],
        //         "status" => "error"
        //     ], 400);
        // }
        if ($buyAirtime["code"] != "000") {
            // auth()->user()->deposit($request->amount);
            return response()->json([
                "message" => $buyAirtime["response_description"],
                "status" => "error"
            ], 400);
        }
        $airtime = Airtime::create([
            "customer_id" => auth()->user()->id,
            "amount" => $request->amount,
            "request_id" => $requestId,
            "phone" => $request->phone,
            "provider" => $request->serialId,
            "response" =>  json_encode($buyAirtime)
        ]);

        return response()->json([
            "message" => $buyAirtime["response_description"],
            "data" => $airtime
        ], 200);
    }

    public function buyAirtimeInternational(Request $request, AirtimeService $airtimeService) {
        $request->validate([
            "amount" => "required|string",
            "email" => "required|email",
            "phone" => "required|string",
            "country" =>  "required|string",
            "operator_id" =>  "required|string",
            "product_type_id" =>  "required",
            "variation_code" =>  "required|string",
        ]);

        auth()->user()->withdraw($request->amount);
        $requestId = $this->requestId();
        $buyAirtime = $airtimeService->buyAirtimeInternational(
            $requestId,
            "foreign-airtime",
            $request->phone,
            $request->variation_code,
            $request->amount,
            $request->phone,
            $request->operator_id,
            $request->country,
            $request->product_type_id,
            $request->email
        );
        if ($buyAirtime == null) {
            auth()->user()->deposit($request->amount);
            return response()->json([
                "message" => "error happend",
                "status" => "error"
            ], 400);
        }
        if ($buyAirtime["code"] != "000") {
            auth()->user()->deposit($request->amount);
            return response()->json([
                "message" => $buyAirtime["response_description"],
                "status" => "error"
            ], 400);
        }
        $airtime = Airtime::create([
            "customer_id" => auth()->user()->id,
            "amount" => $request->amount,
            "request_id" => $requestId,
            "phone" => $request->phone,
            "provider" => "foreign-airtime",
            "response" =>  json_encode($buyAirtime)
        ]);

        return response()->json([
            "message" => $buyAirtime["response_description"],
            "data" => $airtime
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(Airtime $airtime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Airtime $airtime)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Airtime $airtime)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Airtime $airtime)
    {
        //
    }
}
