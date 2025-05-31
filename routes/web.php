<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FonnteWebhookController;

Route::get('/', function () {
    return view('welcome');
});


Route::post('/webhook/fonnte', [FonnteWebhookController::class, 'handle']);

