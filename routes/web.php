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
    Route::get('/stories/',             'StoryController@index');
    Route::get('/sources/',             'SourceController@index');
    Route::get('/persons/',             'PersonController@index');
});

Route::middleware(['auth', 'can:post-editor'])->group(function () {
    Route::get('/persons/hide/{id}/',   'PersonController@hide');
    Route::get('/persons/show/{id}/',   'PersonController@show');
    Route::get('/persons/create/',      'PersonController@create');
    Route::post('/persons/create/',     'PersonController@store');
    Route::get('/persons/edit/{id}/',   'PersonController@edit');
    Route::post('/persons/edit/{id}/',  'PersonController@save');

    Route::get('/sources/activate/{id}/',   'SourceController@activate');
    Route::get('/sources/deactivate/{id}/', 'SourceController@deactivate');
    Route::get('/sources/edit/{id}/',       'SourceController@edit');
    Route::post('/sources/edit/{id}/',      'SourceController@save');
    Route::get('/sources/create/',          'SourceController@create');
    Route::post('/sources/create/',         'SourceController@store');
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
    Route::get( '/instagram/show-lost-posts/',  'InstagramController@showLostPosts');
    Route::get( '/instagram/check-lost-posts/', 'InstagramController@checkLostPosts');

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

Route::get('/calendar/',                'CalendarController@index');

Auth::routes(['register' => false,'reset' => false]);
