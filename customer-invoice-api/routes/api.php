<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\InvoiceItemsController;

Route::get('/health/live', function () {
    return response()->json(['status' => 'ok'], 200);
});

Route::get('/health/ready', function () {
    try {
        \Illuminate\Support\Facades\DB::select('select 1');
        return response()->json(['status' => 'ok', 'checks' => ['database' => 'ok']], 200);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'degraded', 'checks' => ['database' => 'unavailable']], 503);
    }
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::apiResource('invoiceitems', InvoiceItemsController::class)
        ->only(['index', 'store', 'show']);
});
