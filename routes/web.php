<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::get('/admin', function () {
    return view('admin_login');
});

Route::get('/admin/dashboard', function () {
    return view('admin');
});

Route::get('/groupe', function () {
    return view('groupe');
});

Route::get('/inscription', function () {
    return view('inscription');
});

Route::get('/login', function () {
    return view('login');
});
Route::get('/visiteur', function () {
    return view('visiteur');
});
Route::get('/admin_login', function () {
    return view('admin_login');
});

