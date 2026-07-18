<?php

use App\Http\Controllers\CanvasOAuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'canvas-oauth'], function () {
    Route::get('/callback', [CanvasOAuthController::class, 'handleCallback'])->name('canvas-oauth.callback');
});
