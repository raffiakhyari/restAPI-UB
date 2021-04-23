<?php

use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:sanctum')->group(function(){
   Route::get('user', [UserController::class, 'fetch']);
   Route::post('user', [UserController::class, 'updateProfile']);
   Route::post('logout', [UserController::class, 'logout']);
});

Route::post('login', [UserController::class, 'login']);

Route::get('product', [ProductController::class, 'all']);