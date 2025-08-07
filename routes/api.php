<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Invoice;
use App\Http\Controllers\Api\PagoApiController;
use App\Http\Controllers\ClientController;

Route::get('/user', function (Request $request) {
    $user = Auth::guard('api')->user();
    abort_unless($user !== null, 401);
    return User::all();
})->middleware('auth:sanctum');

Route::get('/invoice', function (Request $request) {
    $client = Auth::guard('client')->user();
    abort_unless($client !== null, 401);
    return Invoice::where('client_id', $client->id)
        ->latest()
        ->get();
})->middleware('auth:sanctum');

// Ruta para generar tokens de clientes
Route::post('/clients/tokens', [ClientController::class, 'createToken']);

// API Routes para clientes autenticados - Registro de pagos
Route::middleware('auth:sanctum')->group(function () {
    // Registrar pago de una factura
    Route::post('/pagos', [PagoApiController::class, 'registerPayment']);
});
