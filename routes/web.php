<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/pending-approval', function () {
    return view('filament.pages.auth.pending-approval');
})->name('auth.pending-approval');
