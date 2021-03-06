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

Route::get('/store', 'WebhookController@store');
Route::get('/', 'WebhookController@index');
Route::get('/test', 'WebhookController@test');
Route::get('/signup', 'AuthController@signup');
Route::get('/huobi', 'WebhookController@huobi');
Route::get('/enigma', 'WebhookController@enigma');
Route::get('/fees', 'WebhookController@fees');
