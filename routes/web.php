<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SecurityDeskController;
use App\Http\Controllers\CandidateVisitorController;

Route::get('/', [SecurityDeskController::class, 'index'])->name('desk');

Route::get('/visit', [CandidateVisitorController::class, 'create'])->name('candidate.create');
Route::post('/visit', [CandidateVisitorController::class, 'store'])->name('candidate.store');
