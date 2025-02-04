<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayloadController;

Route::post('/payloads/receive', [PayloadController::class, 'receivePayload']);
Route::get('/payloads/compare', [PayloadController::class, 'comparePayloads']);
Route::delete('/payloads/reset', [PayloadController::class, 'resetCache']);

