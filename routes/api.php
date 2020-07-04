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
    return 123;
    //    return $request->user();
});
Route::post('/login', 'APILoginController@login');
Route::post('/register','APILoginController@register');
Route::post('/logout',"APILoginController@logout");
Route::middleware('auth:api')->post('/profile',"APILoginController@profile");
Route::middleware('auth:api')->get('/profile',"APILoginController@getProfile");

Route::post('/password/email', 'ForgotPasswordController@getResetToken');
Route::post('/password/reset', 'ResetPasswordController@reset');
