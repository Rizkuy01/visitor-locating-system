<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SecurityDeskController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\CardApiController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// cards feed untuk dashboard
Route::get('/cards', [SecurityDeskController::class, 'cards'])->name('api.cards');

// visitors
Route::get('/visitors/active', [VisitorController::class, 'active'])->name('api.visitors.active');
Route::get('/visitors/history', [VisitorController::class, 'history'])->name('api.visitors.history');
Route::get('/visitors/history/export', [VisitorController::class, 'exportHistoryCsv'])->name('api.visitors.history.export');

Route::post('/visitors', [VisitorController::class, 'store'])->name('api.visitors.store');

// checkin + checkout
Route::post('/checkin', [CheckinController::class, 'checkin'])->name('api.checkin');
Route::post('/cards/{card}/checkout', [CardApiController::class, 'checkout'])->name('api.cards.checkout');
