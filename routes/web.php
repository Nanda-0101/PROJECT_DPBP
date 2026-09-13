<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});
Route::view('/',            'index')->name('home');
Route::view('/login',       'login')->name('login');
Route::view('/marketplace', 'marketplace')->name('marketplace');
Route::view('/booking',     'booking')->name('booking');
Route::view('/dashboard',   'dashboard')->name('dashboard');
Route::view('/mitra',       'mitra')->name('mitra');
Route::view('/cs',          'cs')->name('cs');