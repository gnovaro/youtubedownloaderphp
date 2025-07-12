<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'],'/', [App\Http\Controllers\HomeController::class, 'index']);

Auth::routes([
    'register' => (env('APP_ENV') != 'production') ? true : false, // Registration Routes...
]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
