<?php

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

Route::get('/run-sockets', function () {
    dd(\Artisan::call('websockets:serve'));
});

Route::get('saad',function () {
    dd("Saad"); 
 });
 
Route::get('/', function () {
    return view('welcome');
});

Route::get('/chat', function () {
    return view('chat-app');
});

include 'upgrade.php';
