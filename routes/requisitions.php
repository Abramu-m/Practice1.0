<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Store\StoreRequisitionController;
use App\Http\Controllers\Store\StoreLocationStockController;
use App\Http\Controllers\StoreUnitController;

/*
|--------------------------------------------------------------------------
| Store Requisition Routes
|--------------------------------------------------------------------------
|
| Routes for managing store requisitions - the process of requesting
| items from the medication ledger to be transferred to store locations
|
*/

Route::prefix('store/requisitions')->name('store.requisitions.')->group(function () {
    // Basic CRUD operations
    Route::get('/', [StoreRequisitionController::class, 'index'])->name('index');
    Route::get('/create', [StoreRequisitionController::class, 'create'])->name('create');
    Route::post('/', [StoreRequisitionController::class, 'store'])->name('store');
    Route::get('/{requisition}', [StoreRequisitionController::class, 'show'])->name('show');
    Route::get('/{requisition}/edit', [StoreRequisitionController::class, 'edit'])->name('edit');
    Route::put('/{requisition}', [StoreRequisitionController::class, 'update'])->name('update');
    Route::delete('/{requisition}', [StoreRequisitionController::class, 'destroy'])->name('destroy');
    
    // Workflow actions
    Route::patch('/{requisition}/submit', [StoreRequisitionController::class, 'submit'])->name('submit');
    Route::patch('/{requisition}/verify', [StoreRequisitionController::class, 'verify'])->name('verify');
    Route::patch('/{requisition}/approve', [StoreRequisitionController::class, 'approve'])->name('approve');
    Route::patch('/{requisition}/reject', [StoreRequisitionController::class, 'reject'])->name('reject');
    Route::patch('/{requisition}/issue', [StoreRequisitionController::class, 'issue'])->name('issue');
    Route::patch('/{requisition}/cancel', [StoreRequisitionController::class, 'cancel'])->name('cancel');
    
    // API endpoints for AJAX requests
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/medications', [StoreRequisitionController::class, 'getAvailableMedications'])->name('medications');
        Route::get('/medications/{medication}/stock', [StoreRequisitionController::class, 'getMedicationStock'])->name('medication.stock');
    });
});

// Store Unit related routes
Route::resource('store-units', StoreUnitController::class)->names(['store-units']);
Route::post('store-units/{unit}/toggle-status', [StoreUnitController::class, 'toggleStatus'])->name('store-units.toggle-status');

// Store Location Stock management
Route::resource('store-locations-stock', StoreLocationStockController::class)->names(['store-locations-stock']);
