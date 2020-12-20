<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TestPaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
// $router->group(['middleware' => 'auth'], function() use ($router){
//     $router->get('/','AuthController@make');
//     $router->post('/test','TestPaymentController@testPayment');
// });

Route::get('/test', [TestPaymentController::class, 'testPayment']);
Route::post('/notification/push', [NotificationController::class, 'post']);
