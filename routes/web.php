<?php

use App\Http\Controllers\CanvasOauthController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'canvas'], function(){
    Route::get('/code_exchange', [CanvasOauthController::class, 'codeExchange'])->name('canvasoauth.code_exchange');
});