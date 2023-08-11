<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PaystackFundingController;
use App\Http\Controllers\AirtimeController;
use App\Http\Controllers\DataPurchaseController;
use App\Http\Controllers\CabletvPurchaseController;
use App\Http\Controllers\ElectricityPurchaseController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum', 'type.customer')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    // Route::post('create', [CustomerController::class, 'create'])->middleware(['auth:sanctum', 'type.customer']);
    Route::prefix('customer')->group(function () {
        Route::get('profile', [CustomerController::class, 'profile'])->middleware(['auth:sanctum', 'type.customer']);
        Route::post('create', [CustomerController::class, 'create']);
        Route::post('login', [CustomerController::class, 'login']);

        Route::prefix('transactions')->group(function () {
            Route::get('list', [CustomerController::class, 'listTransactions'])->middleware(['auth:sanctum', 'type.customer']);
        });
        Route::prefix('airtime')->group(function () {
            Route::get('', [AirtimeController::class, 'index'])->middleware(['auth:sanctum', 'type.customer']);
            Route::post('mtn', [AirtimeController::class, 'buyAirTime'])->middleware(['auth:sanctum', 'type.customer']);
            Route::post('airtime-international', [AirtimeController::class, 'buyAirtimeInternational'])->middleware(['auth:sanctum', 'type.customer']);
            Route::get('get-international-airtime-countries', [AirtimeController::class, 'getInternationalAirtimeCountries'])->middleware(['auth:sanctum', 'type.customer']);
            Route::get('get-international-airtime-product-types/{code}', [AirtimeController::class, 'getinternationalAirtimeProductTypes'])->middleware(['auth:sanctum', 'type.customer']);
            Route::get('get-international-airtime-product-operators/{code}/{product}', [AirtimeController::class, 'getinternationalAirtimeOperator'])->middleware(['auth:sanctum', 'type.customer']);
            Route::get('get-international-airtime-service-variations/{operator_id}/{product_type_id}', [AirtimeController::class, 'getinternationalServiceVariation'])->middleware(['auth:sanctum', 'type.customer']);
        });

        Route::prefix('data')->group(function () {
            Route::get('', [DataPurchaseController::class, 'index'])->middleware(['auth:sanctum', 'type.customer']);
            Route::get('list-packages/{serviceID}', [DataPurchaseController::class, 'listPackages'])->middleware(['auth:sanctum', 'type.customer']);
            Route::post('buydata', [DataPurchaseController::class, 'create'])->middleware(['auth:sanctum', 'type.customer']);
        });

        Route::prefix('cabletv')->group(function () {
            Route::get('', [CabletvPurchaseController::class, 'index'])->middleware(['auth:sanctum', 'type.customer']);
            Route::get('', [CabletvPurchaseController::class, 'index'])->middleware(['auth:sanctum', 'type.customer']);
            Route::post('verify-card', [CabletvPurchaseController::class, 'verifyCard'])->middleware(['auth:sanctum', 'type.customer']);
            Route::post('subscribe-tv', [CabletvPurchaseController::class, 'create'])->middleware(['auth:sanctum', 'type.customer']);
        });

        Route::prefix('electricity')->group(function () {
            Route::get('', [ElectricityPurchaseController::class, 'index'])->middleware(['auth:sanctum', 'type.customer']);
            Route::post('buy', [ElectricityPurchaseController::class, 'create'])->middleware(['auth:sanctum', 'type.customer']);
        });

        Route::prefix('monify')->group(function () {
            Route::post('verify', [CustomerController::class, 'verifyPayment']);
        });

        Route::prefix('paystack')->group(function () {
            Route::post('generate', [PaystackFundingController::class, 'create'])->middleware(['auth:sanctum', 'type.customer']);
            Route::post('verify', [PaystackFundingController::class, 'verifyPayment'])->middleware(['auth:sanctum', 'type.customer']);
            Route::get('', [PaystackFundingController::class, 'index'])->middleware(['auth:sanctum', 'type.customer']);
        });
    });
});
