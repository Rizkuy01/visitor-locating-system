<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SecurityDeskController;
use App\Http\Controllers\CandidateVisitorController;
use App\Http\Controllers\VisitorTableController;
use App\Http\Controllers\VisitorController;

// Security Desk Dashboard
Route::get('/', [SecurityDeskController::class, 'index'])->name('desk');

// Candidate Visitor Form
Route::get('/visitor_form', [CandidateVisitorController::class, 'create'])->name('candidate.create');
Route::post('/visitor_form', [CandidateVisitorController::class, 'store'])->name('candidate.store');

// Visitor Table
Route::get('/visitors', [VisitorTableController::class, 'index'])->name('visitors.index');
Route::get('/api/visitors/table', [VisitorTableController::class, 'data'])->name('visitors.table.data');

// Visitor Scan and Check-in
Route::get('/visitor-scan', [VisitorController::class, 'scanPage'])->name('visitors.scan.page');
Route::get('/api/visitors/scan', [VisitorController::class, 'scanLookup'])->name('visitors.scan.lookup');
Route::post('/api/visitors/scan/confirm', [VisitorController::class, 'scanConfirm'])->name('visitors.scan.confirm');