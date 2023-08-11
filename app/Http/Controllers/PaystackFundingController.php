<?php

namespace App\Http\Controllers;

use App\Models\PaystackFunding;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Customer;
class PaystackFundingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paystackFundings = PaystackFunding::whereNotNull("paystack_fundings.id")->where("customer_id", auth()->user()->id)->latest()->get();
        return response()->json(["message" => "successful", "data" => $paystackFundings], 200);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $request->validate(["amount" => "required|string"]);
        $new = PaystackFunding::create([
            "amount" => $request->amount,
            "reference" => md5(Str::random(20)),
            "customer_id" => auth()->user()->id
        ]);
        return response()->json([
            "message" => "Proceed to payments",
            "data" => $new
        ], 200);
    }

    public function verifyPayment(Request $request) {
        $request->validate(["reference" => "required|string"]);
        $new = PaystackFunding::where("reference", request()->input("reference"))->first();
        if ($new->status == "verified") {
            return response()->json(["message" => "Verifyed before"], 400);
        }
        $new->update(["status" => "verified"]);
        $customer = Customer::where("id", $new->customer_id)->first();
        $customer->deposit($new->amount);
        return response()->json(["message" => "Verifyed successfully"], 200);
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
    public function show(PaystackFunding $paystackFunding)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaystackFunding $paystackFunding)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaystackFunding $paystackFunding)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaystackFunding $paystackFunding)
    {
        //
    }
}
