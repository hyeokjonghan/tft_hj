<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\TFT\TFTVersionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 로그인 안하고 사용 할 수 있는 API
Route::prefix('user')->group(function() {

    // 일반 로그인.. SNS 관련 처리 할 때 추가 편집 해줘야 함
    Route::post('/login', [ApiAuthController::class, 'createToken']);
    Route::post('/token/refersh', [ApiAuthController::class, 'tokenRefresh']);

    // 일반 회원가입
    Route::post('/register', [ApiAuthController::class, 'createUser']);
});

Route::prefix('upload')->group(function() {
    Route::post('/{uploadDivision}',[UploadController::class,'fileUpload']);
});

Route::get('/version/test',[TFTVersionController::class, 'autoSetVersion']);

// 로그인 하고 사용 할 수 있는 API
Route::middleware('auth:api')->group(function() {
    // 관리자만 사용 할 수 있는 API
    
    // 일반 유저도 사용 할 수 있는 API

});
