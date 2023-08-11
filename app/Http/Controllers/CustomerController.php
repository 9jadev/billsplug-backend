<?php

namespace App\Http\Controllers;

use App\Models\Airtime;
use App\Models\Customer;
use App\Models\MonifyTransaction;
use App\Services\AirtimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\MonifyService;
use App\Models\Transaction;
class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function listTransactions()
    {
        $transactions = Transaction::whereNotNull("transactions.id")->where("payable_id", auth()->user()->id)->where("payable_type", "App\Models\Customer")->latest();
        $data = request()->query("page_number") ? $transactions->paginate(request()->query("page_number")) : $transactions->get();
        return response()->json(["message" => "successful", "data" => $data], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, MonifyService $monifyService)
    {

        $request->validate([
            "name" => "required|string",
            "email" =>  "required|email|unique:customers,email,except,id",
            "phone" => "required|string|unique:customers,phone,except,id",
            "password" => "required|string"
        ]);

        $monifyToken = $monifyService->login();
        if ($monifyToken == null) {
            return response()->json(["message" => "Bank error occured."], 400);
        }
        $monifyResolve = $monifyService->resolveBankAccount(env('APP_NAME').$request->APP_NAME, $request->email, $request->name, $monifyToken["responseBody"]["accessToken"]);
        if ($monifyResolve == null) {
            return response()->json(["message" => "Bank resolution error occured."], 400);
        }
        if ($monifyResolve["responseMessage"] != "success") {
            return response()->json(["message" => "Bank resolution error occured."], 400);
        }
        $customer = Customer::create([
            "name" => $request->name,
            "email" =>  $request->email,
            "phone" => $request->phone,
            "password" => bcrypt($request->password),
            "account_reference" => $monifyResolve["responseBody"]["accountReference"],
            "monify_response_body" => json_encode($monifyResolve["responseBody"]),
        ]);
        return response()->json(["message" => "Customer Created Successfully", "customer" => $customer], 200);
    }

    /**
     * Store a newly created resource in storage.
     */

     public function login(Request $request) {
        $request->validate([
            "email" => "required|email",
            "password" => "required|string"
        ]);
        $customer = Customer::where("email", $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'customer' => $customer,
            "message" => "Customer created successful.",
            'token' => $customer->createToken('webapp', ['role:customer'])->plainTextToken,
        ]);
    }

    public function verifyPayment(Request $request, MonifyService $monifyService) {

        $customer_reference = $request["eventData"]["product"]["reference"];
        $customer = Customer::where("account_reference", $customer_reference)->first();
        logs()->info("customer");
        logs()->info($customer);

        // logs()->info("request");
        // logs()->info($request);

        $checktrack = MonifyTransaction::where("paymentReference", $request["eventData"]["paymentReference"])->first();
        if ($checktrack != null) {
            return response()->json(["message" => "Already Verification"], 400);
        }
        $track = MonifyTransaction::updateOrCreate(
            ["customer_id" => $customer->id,"paymentReference" => $request["eventData"]["paymentReference"]],
            [
                "customer_id" => $customer?->id,
                "paymentReference" => $request["eventData"]["paymentReference"],
                "webhook_data" => json_encode($request->all())
            ],
        );
        $payment_verification =  $monifyService->paymentVerifaction($request["eventData"]["paymentReference"]);
        logs()->info("payment_verification");
        logs()->info($payment_verification);
        if ($payment_verification["responseMessage"] != "success") {
            return response()->json(["message" => "Failed Verification"], 400);
        }

        if ($customer) {
            $customer->deposit($payment_verification["responseBody"]["amount"]);
            $track->update([
                "amount" => $payment_verification["responseBody"]["amount"],
                "status" => MonifyTransaction::verify,
                "verify_data" => json_encode($payment_verification),
            ]);
        }

        return response()->json(["message" => "Funded successfully"], 200);
    }

     public function profile() {
        $customer = Customer::where("id", auth()->user()->id)->first();
        $balance = $customer->balance;
        $customer->balance = $balance;
        return response()->json([
            'customer' => $customer,
            'balance' => $balance,
            "message" => "Customer fetched successful.",
        ]);
    }


    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        //
    }
}
