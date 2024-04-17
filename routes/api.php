<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\MessageController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [UserController::class, 'profile']);
    Route::put('user/update', [UserController::class, 'update']);
    Route::post('logout', [UserController::class, 'logout']);
});

Route::get('storage/{filename}', function ($filename) {
    $path = storage_path('app/public/' . $filename);

    if (!Storage::disk('public')->exists($filename)) {
        abort(404);
    }

    return response()->file($path);
})->where('filename', '.*');

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('products', [ProductController::class, 'store']);
Route::get('products', [ProductController::class, 'getProductos']);
Route::post('buy-coins', [TransactionController::class, 'buyCoins']);
Route::post('get-Balance', [TransactionController::class, 'getBalance']);
Route::get('users/approval-status', [UserController::class, 'getUsersByApprovalStatus']);
Route::post('services', [ServiceController::class, 'store']);
Route::get('services', [ServiceController::class, 'getServices']);
// Para ProductController
Route::post('productos/{id}/report', [ProductController::class, 'reportProduct']);
Route::get('products/reported', [ProductController::class, 'getReportedProducts']);
Route::put('products/{id}/update-status', [ProductController::class, 'updateReportedProductStatus']);
Route::delete('products/{id}', [ProductController::class, 'deleteProduct']);
// Para ServiceController
Route::post('/servicios/{id}/report', [ServiceController::class, 'reportService']);
Route::get('/services/reported', [ServiceController::class, 'getReportedServices']);
Route::put('/services/{id}/update-status', [ServiceController::class, 'updateReportedServiceStatus']);
Route::delete('/services/{id}', [ServiceController::class, 'deleteService']);
// Para UserController
Route::put('/users/{id}/approval-status', [UserController::class, 'updateApprovalStatus']);
// Para productos
Route::put('/products/{id}/approve', [ProductController::class, 'approveProduct']);
Route::put('/products/{id}/reject', [ProductController::class, 'rejectProduct']);

// Para servicios
Route::put('/services/{id}/approve', [ServiceController::class, 'approveService']);
Route::put('/services/{id}/reject',  [ServiceController::class, 'rejectService']);

// obtener productos de un usuario
Route::get('/users/{userName}/products', [ProductController::class, 'getUserProducts']);
// obtener servicios de un usuario
Route::get('/users/{userName}/services', [ServiceController::class, 'getUserServices']);

// modificar productos
Route::put('products/update/{id}', [ProductController::class, 'updateProduct']);
// modificar servicios
Route::put('services/update/{id}', [ServiceController::class, 'updateService']);

// chat
Route::post('/send-message', [MessageController::class, 'sendMessage']);
Route::get('/get-messages', [MessageController::class, 'getMessages']);Route::get('/messages/user', [MessageController::class, 'getAllUserMessages']);
Route::get('/messages/user', [MessageController::class, 'getAllUserMessages']);

Route::post('/products/{id}/buy', [ProductController::class, 'buyProduct']);
Route::get('/users/balance', [UserController::class, 'getBalance']);
Route::post('/users/withdraw', [UserController::class, 'withdrawFunds']);
