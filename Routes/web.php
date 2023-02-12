<?php

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

Route::prefix(config('core.admin_prefix'))
->middleware('auth:admins')
->as('admin.media.')
->namespace('Admin')
->group(function() {
    Route::get('media', 'MediaController@index')->name('index');
    Route::prefix('media/api')->group(function() {
        Route::post('list', 'MediaController@list')->name('list');
        Route::post('upload', 'MediaController@upload')->name('upload');
        Route::post('create-folder', 'MediaController@createFolder')->name('create-folder');
        Route::post('move', 'MediaController@move')->name('move');
        Route::post('delete', 'MediaController@destroy')->name('delete');
    });
});