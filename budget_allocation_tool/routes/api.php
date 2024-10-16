<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\ForgotPasswordController;
use App\Mail\ResetPassword;
use Illuminate\Support\Facades\Mail;
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

Route::get('/', function () {
    return "aah staging api working fine";
});



//TODO will change the route name . 
Route::post('/import-excel', [ExcelController::class,'importExcel']);




Route::post('/register', [AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);


// user first triggers forgot password then , token / pin will be sent using that pin user reset the password . 
Route::post('/forgot-password', [ForgotPasswordController::class,'forgotPassword']);
// this endpoint is called after admin registers the user 
Route::post('/prompt-user-to-reset', [ForgotPasswordController::class,'forgotPassword']);
Route::post('/reset-password', [ForgotPasswordController::class,'resetPassword']);
Route::middleware('auth:sanctum')->post('/change-password', [ForgotPasswordController::class, 'changePassword']);

