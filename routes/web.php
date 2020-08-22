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


Route::get('/platforms/list',       'PlatformController@list');
Route::get('/platforms/alphabet',   'PlatformController@alphabet');


Route::get('/persons/',                 'PersonController@index');
Route::get('/persons/delete/{id}/',   'PersonController@delete');


Route::get('/',                 'PostController@index');