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
->as('admin.')
->namespace('Admin')
->group(function() {
    Route::prefix('media/api')->as('media.')->group(function() {
        Route::post('list', 'MediaController@list')->name('list');
        Route::post('upload', 'MediaController@upload')->name('upload');
    });
});