<?php 

use Illuminate\Support\Facades\Route;
use xcesaralejandro\canvas_oauth_for_laravel\Http\Controllers\{
    CanvasOauthController
};

Route::get('/hola', [CanvasOauthController::class, 'hola']);
