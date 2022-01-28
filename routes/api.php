<?php

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/calendar/',        'CalendarController@apiIndex');
    Route::get('/posts/',           'PostController@apiIndex');
    Route::get('/stories/',         'StoryController@apiIndex');

    Route::get('/persons/',         'PersonController@apiIndex');
    Route::post('/persons/',        'PersonController@apiStore');
    Route::get('/persons/{id}/',    'PersonController@apiView');
    Route::post('/persons/{id}/',   'PersonController@apiSave');

    Route::get('/sources/',         'SourceController@apiIndex');
    Route::post('/sources/',        'SourceController@apiStore');
    Route::get('/sources/{id}/',    'SourceController@apiView');
    Route::post('/sources/{id}/',   'SourceController@apiSave');

    Route::get('/trends/',          'TrendController@apiIndex');

    Route::get('/articles/',                'ArticleController@apiIndex');
    Route::get('/articles/text/{id}/',      'ArticleController@apiText');
    Route::get('/articles/translate/{id}/', 'ArticleController@translate');
});
