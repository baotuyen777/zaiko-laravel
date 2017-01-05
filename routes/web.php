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

Route::get('/', function () {
    return view('welcome');
});
//Route::resource('jokes', 'JokesController');
Route::group([ 'prefix' => 'api/v1'], function() {
    Route::resource('jokes', 'JokesController');
});
/** product */
Route::group([ 'prefix' => 'api/v1'], function() {
    Route::resource('product', 'ProductsController');
});
/** user */
Route::group([ 'prefix' => 'api/v1'], function() {
    Route::resource('user', 'UsersController');
});
/** order */
Route::group([ 'prefix' => 'api/v1'], function() {
    Route::resource('order', 'OrdersController');
});
Route::group([ 'prefix' => 'api/v1'], function() {
    Route::resource('authenticate', 'AuthenticateController', ['only' => ['index']]);
    Route::post('authenticate', 'AuthenticateController@authenticate');
    Route::get('authenticate/user', 'AuthenticateController@getAuthenticatedUser');
});
