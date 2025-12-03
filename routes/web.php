<?php
use App\Http\Controllers\SecurityDeskController;
use App\Http\Controllers\VisitorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SecurityDeskController::class, 'index'])->name('desk');

Route::get('/api/cards', [SecurityDeskController::class, 'cards'])->name('cards');

Route::get('/api/visitors/active', [VisitorController::class, 'active'])->name('visitors.active');
Route::get('/api/visitors/history', [VisitorController::class, 'history'])->name('visitors.history');
Route::get('/api/visitors/history/export', [VisitorController::class, 'exportHistoryCsv'])->name('visitors.history.export');

Route::post('/api/visitors', [VisitorController::class, 'store'])->name('visitors.store');
Route::post('/api/cards/{card}/checkout', [VisitorController::class, 'checkout'])->name('cards.checkout');
