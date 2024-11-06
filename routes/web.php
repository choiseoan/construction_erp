<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

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

// 메인
Route::get('/', function () {
    return view('index');
});

// 로그인
Route::group(['prefix' => 'auth'], function () {
    Route::post('login',  'Auth\LoginController@login');
    Route::get ('logout', 'Auth\LoginController@logout');
});

// 캐시클리어
Route::get('/clear-cache', function () {
    Artisan::call('config:cache');
    return "CLEARCACHE";
 });

# 인트라넷
include 'web/intranet.php';

# 현장관리
include 'web/field.php';

# 환경설정
include 'web/config.php';
