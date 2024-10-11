<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelController;
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
    return "add api please";
});


// Route::get('/upload', function () {
//     return view('welcome');
// });

// Route::post('/import-excel', [ExcelController::class,'importExcel']);
