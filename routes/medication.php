<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockManagementController;
use App\Http\Controllers\ConsumptionTrackingController;
use App\Http\Controllers\ReconciliationController;
use App\Http\Controllers\ReportingController;

/*
|--------------------------------------------------------------------------
| Medication Management Routes
|--------------------------------------------------------------------------
|
| Here are all routes related to medication management functionality
| including stock management, consumption tracking, reconciliation,
| and reporting.
|
*/

Route::prefix('medications')->name('medications.')->group(function () {
    
    // Dashboard & Overview
    Route::get('/', [StockManagementController::class, 'index'])->name('index');
    Route::get('/dashboard', [StockManagementController::class, 'dashboard'])->name('dashboard');

    // Goods Received Notes (GRN) Management
    Route::resource('goods-received-notes', App\Http\Controllers\Store\GoodsReceivedNoteController::class)
        ->names('stock.grn')
        ->parameters(['goods-received-notes' => 'grn']);
    Route::post('/goods-received-notes/{grn}/approve', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'approve'])
        ->name('stock.grn.approve');
    Route::post('/goods-received-notes/{grn}/reject', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'reject'])
        ->name('stock.grn.reject');
    Route::get('/goods-received-notes/api/pending', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'apiPending'])
        ->name('stock.grn.api.pending');
    Route::get('/goods-received-notes/api/approved', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'apiApproved'])
        ->name('stock.grn.api.approved');
    Route::get('/goods-received-notes/api/rejected', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'apiRejected'])
        ->name('stock.grn.api.rejected');
    Route::post('/goods-received-notes/{grn}/add-item', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'addItem'])
        ->name('stock.grn.add-item');
    Route::put('/goods-received-notes/{grn}/update-item/{item}', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'updateItem'])
        ->name('stock.grn.update-item');
    Route::delete('/goods-received-notes/{grn}/remove-item/{item}', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'removeItem'])
        ->name('stock.grn.remove-item');
    
    // GRN Items Management Routes
    Route::get('/goods-received-notes/{grn}/items', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'itemsIndex'])
        ->name('stock.grn.items.index');
    Route::get('/goods-received-notes/{grn}/items/create', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'itemsCreate'])
        ->name('stock.grn.items.create');
    Route::get('/goods-received-notes/{grn}/items/test', function(\App\Models\GoodsReceivedNote $grn) {
        $grn->load(['supplier']);
        return view('medications.stock.grn.grn_items.test', compact('grn'));
    })->name('stock.grn.items.test');
    Route::get('/goods-received-notes/{grn}/items/{item}', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'getItem'])
        ->name('stock.grn.items.get');
    Route::put('/goods-received-notes/{grn}/items/{item}', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'updateItem'])
        ->name('stock.grn.items.update');
    Route::delete('/goods-received-notes/{grn}/items/{item}', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'removeItem'])
        ->name('stock.grn.items.remove');
    
    // API Routes for item selection
    Route::get('/stock/items/medications', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'getMedications'])
        ->name('stock.items.medications');
    Route::get('/stock/items/{type}', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'getItemsByType'])
        ->name('stock.items.by-type');
    
    // API Routes for store units
    Route::get('/stock/units/store', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'getStoreUnits'])
        ->name('stock.units.store');
    Route::get('/stock/units/dispensing', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'getDispensingUnits'])
        ->name('stock.units.dispensing');
    
    Route::post('/goods-received-notes/{grn}/process', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'processGRN'])
        ->name('stock.grn.process');

    // Medication Ledger Management
    Route::prefix('stock/ledger')->name('stock.ledger.')->group(function () {
        Route::get('/', [App\Http\Controllers\Store\MedicationLedgerController::class, 'index'])
            ->name('index');
        Route::get('/stock-summary', [App\Http\Controllers\Store\MedicationLedgerController::class, 'stockSummary'])
            ->name('stock-summary');
        Route::get('/expiry-report', [App\Http\Controllers\Store\MedicationLedgerController::class, 'expiryReport'])
            ->name('expiry-report');
        Route::get('/export', [App\Http\Controllers\Store\MedicationLedgerController::class, 'export'])
            ->name('export');
        Route::get('/medication/{medication}/batches', [App\Http\Controllers\Store\MedicationLedgerController::class, 'getBatchDetails'])
            ->name('medication.batches');
        Route::get('/{ledger}', [App\Http\Controllers\Store\MedicationLedgerController::class, 'show'])
            ->name('show');
        Route::post('/{ledger}/update-status', [App\Http\Controllers\Store\MedicationLedgerController::class, 'updateStatus'])
            ->name('update-status');
        Route::post('/{ledger}/mark-unfit', [App\Http\Controllers\Store\MedicationLedgerController::class, 'markAsUnfit'])
            ->name('mark-unfit');
    });

    // Supplier Management
    Route::resource('suppliers', App\Http\Controllers\Store\StoreSupplierController::class)
        ->names('stock.suppliers');

    // Stock Management Routes
    Route::prefix('stock')->name('stock.')->group(function () {
        
        // Stock Transfers
        Route::get('/transfers', [StockManagementController::class, 'transfersIndex'])->name('transfers.index');
        Route::get('/transfers/create', [StockManagementController::class, 'createTransfer'])->name('transfers.create');
        Route::post('/transfers', [StockManagementController::class, 'storeTransfer'])->name('transfers.store');
        Route::post('/transfers/{id}/process', [StockManagementController::class, 'processTransfer'])->name('transfers.process');
        
        // Stock Adjustments
        Route::get('/adjustments', [StockManagementController::class, 'adjustmentsIndex'])->name('adjustments.index');
        Route::get('/adjustments/create', [StockManagementController::class, 'createAdjustment'])->name('adjustments.create');
        Route::post('/adjustments', [StockManagementController::class, 'storeAdjustment'])->name('adjustments.store');
        
        // Stock Disposal
        Route::get('/disposal', [StockManagementController::class, 'disposalIndex'])->name('disposal.index');
        Route::get('/disposal/{disposal}', [StockManagementController::class, 'getDisposalDetails'])->name('disposal.show');
        Route::post('/disposal/{disposal}/verify', [StockManagementController::class, 'verifyDisposal'])->name('disposal.verify');
        Route::post('/disposal/{disposal}/complete', [StockManagementController::class, 'completeDisposal'])->name('disposal.complete');
        Route::post('/disposal/{disposal}/cancel', [StockManagementController::class, 'cancelDisposal'])->name('disposal.cancel');
        Route::post('/disposal/unfit', [StockManagementController::class, 'disposeUnfitMedications'])->name('disposal.unfit');
        
        // Stock Levels & Alerts
        Route::get('/levels', [StockManagementController::class, 'stockLevels'])->name('levels');
        Route::get('/alerts', [StockManagementController::class, 'stockAlerts'])->name('alerts');
        Route::get('/availability/{medicationId}', [StockManagementController::class, 'checkAvailability'])->name('availability');
    });

    // Consumption Tracking Routes
    Route::prefix('consumption')->name('consumption.')->group(function () {
        Route::get('/', [ConsumptionTrackingController::class, 'index'])->name('index');
        
        // Prescription Dispensing
        Route::get('/prescriptions', [ConsumptionTrackingController::class, 'prescriptionsIndex'])->name('prescriptions.index');
        Route::get('/prescriptions/{id}', [ConsumptionTrackingController::class, 'showPrescription'])->name('prescriptions.show');
        Route::post('/prescriptions/{id}/dispense', [ConsumptionTrackingController::class, 'dispensePrescription'])->name('prescriptions.dispense');
        
        // Investigation Consumption
        Route::post('/investigations/{id}/record', [ConsumptionTrackingController::class, 'recordInvestigationConsumption'])->name('investigations.record');
        
        // Procedure Consumption
        Route::post('/procedures/{id}/record', [ConsumptionTrackingController::class, 'recordProcedureConsumption'])->name('procedures.record');
        
        // Analytics & Reports
        Route::get('/analytics', [ConsumptionTrackingController::class, 'consumptionAnalytics'])->name('analytics');
        Route::get('/patient/{id}/history', [ConsumptionTrackingController::class, 'patientConsumptionHistory'])->name('patient.history');
        Route::get('/export', [ConsumptionTrackingController::class, 'exportConsumptionData'])->name('export');
    });

    // Reconciliation Routes
    Route::prefix('reconciliation')->name('reconciliation.')->group(function () {
        Route::get('/', [ReconciliationController::class, 'index'])->name('index');
        
        // Integrity Checks
        Route::post('/integrity-check', [ReconciliationController::class, 'runIntegrityCheck'])->name('integrity.check');
        Route::post('/auto-correct', [ReconciliationController::class, 'autoCorrectDiscrepancies'])->name('auto.correct');
        
        // Discrepancy Management
        Route::get('/discrepancies', [ReconciliationController::class, 'showDiscrepancyReport'])->name('discrepancies');
        Route::get('/medications/{id}/validate', [ReconciliationController::class, 'validateMedicationBalance'])->name('medications.validate');
        
        // Manual Corrections
        Route::get('/corrections', [ReconciliationController::class, 'showStockCorrection'])->name('corrections.form');
        Route::post('/corrections', [ReconciliationController::class, 'processStockCorrection'])->name('corrections.process');
        
        // Audit Trail
        Route::get('/audit', [ReconciliationController::class, 'showAuditTrail'])->name('audit');
        
        // Stock Comparison
        Route::get('/comparison/{medicationId?}', [ReconciliationController::class, 'showStockComparison'])->name('comparison');
        
        // Reports
        Route::post('/export', [ReconciliationController::class, 'exportReport'])->name('export');
        Route::get('/metrics', [ReconciliationController::class, 'getDashboardMetrics'])->name('metrics');
    });

    // Reporting Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportingController::class, 'index'])->name('index');
        
        // Stock Reports
        Route::get('/stock-levels', [ReportingController::class, 'stockLevelReport'])->name('stock.levels');
        Route::get('/movements', [ReportingController::class, 'movementReport'])->name('movements');
        Route::get('/expiry', [ReportingController::class, 'expiryReport'])->name('expiry');
        
        // Consumption Reports
        Route::get('/consumption', [ReportingController::class, 'consumptionReport'])->name('consumption');
        
        // Analysis Reports
        Route::get('/abc-analysis', [ReportingController::class, 'abcAnalysis'])->name('abc.analysis');
        
        // Custom Reports
        Route::match(['GET', 'POST'], '/custom', [ReportingController::class, 'customReport'])->name('custom');
        
        // Export
        Route::post('/export', [ReportingController::class, 'exportReport'])->name('export');
    });

    // Medication Formulations Management
    Route::prefix('formulations')->name('formulations.')->group(function () {
        Route::get('/', [App\Http\Controllers\MedicationFormulationController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\MedicationFormulationController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\MedicationFormulationController::class, 'store'])->name('store');
        Route::get('/{formulation}', [App\Http\Controllers\MedicationFormulationController::class, 'show'])->name('show');
        Route::get('/{formulation}/edit', [App\Http\Controllers\MedicationFormulationController::class, 'edit'])->name('edit');
        Route::put('/{formulation}', [App\Http\Controllers\MedicationFormulationController::class, 'update'])->name('update');
        Route::delete('/{formulation}', [App\Http\Controllers\MedicationFormulationController::class, 'destroy'])->name('destroy');
        
        // API Routes
        Route::get('/api/active', [App\Http\Controllers\MedicationFormulationController::class, 'getActiveFormulations'])
            ->name('api.active');
    });
});
