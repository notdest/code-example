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

Route::middleware(['auth', 'can:post-viewer'])->group(function () {
    Route::get('/posts/',               'PostController@index');

    Route::get('/platforms/list',       'PlatformController@list');
    Route::get('/platforms/alphabet',   'PlatformController@alphabet');

    Route::get('/persons/',             'PersonController@index');
});

Route::middleware(['auth', 'can:post-editor'])->group(function () {
    Route::get('/persons/delete/{id}/', 'PersonController@delete');
});

Route::middleware(['auth', 'can:article-viewer'])->group(function () {
    Route::get('/articles/',            'ArticleController@index');
    Route::get('/articles/download/',   'ArticleController@download');
});

Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/users/',               'UserController@index');
    Route::get('/users/create/',        'UserController@create');
    Route::post('/users/create/',       'UserController@store');
    Route::get('/users/edit/{id}/',     'UserController@edit');
    Route::post('/users/edit/{id}/',    'UserController@save');

    Route::get( '/instagram/edit/',             'InstagramController@edit');
    Route::post('/instagram/edit/',             'InstagramController@save');
    Route::get( '/instagram/check-email/',      'InstagramController@checkEmail');
    Route::get( '/instagram/check-subscribed/', 'InstagramController@checkSubscribed');

    Route::get( '/rss-category/',               'RssCategoryController@index');
    Route::get( '/rss-category/delete/{id}/',   'RssCategoryController@delete');
    Route::post('/rss-category/write/',         'RssCategoryController@write');

    Route::get( '/rss-sources/',                'RssSourceController@index');
    Route::get( '/rss-sources/create/',         'RssSourceController@create');
    Route::post('/rss-sources/create/',         'RssSourceController@store');
    Route::get( '/rss-sources/edit/{id}/',      'RssSourceController@edit');
    Route::post('/rss-sources/edit/{id}/',      'RssSourceController@save');
});

Route::get('/',                         'UserController@defaultPage')->middleware('auth');
Route::get('/trends/',                  'TrendController@index');
Route::get('/trends/download/',         'TrendController@download');

Auth::routes(['register' => false,'reset' => false]);
