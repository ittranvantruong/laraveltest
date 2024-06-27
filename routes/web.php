<?php

use App\Http\Controllers\SheetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sheet', [SheetController::class, 'handle']);
