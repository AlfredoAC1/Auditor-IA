<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/analisis/arr-001', function () {
    return view('analysis-detail');
});

Route::get('/analisis/arr-001/clausulas', function () {
    return view('analysis-clauses');
});

