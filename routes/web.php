<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '(teacher|student|institute|hostlive|course|exam|game|discover|profile|.*\.[^/]+).*')->name('spa');

Route::fallback(function () {
    return view('app');
});
