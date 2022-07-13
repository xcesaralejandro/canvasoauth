<?php 

use Illuminate\Support\Facades\Route;
use xcesaralejandro\canvasoauth\Http\Controllers\CanvasOauthController;


Route::group(['prefix' => 'canvas'], function(){
    Route::get('/code_exchange', [CanvasOauthController::class, 'codeExchange'])->name('canvasoauth.code_exchange');
});