// Print results routes
    Route::get('/patient-visits/{visit}/print-results', [PatientVisitController::class, 'printResults'])->name('patient_visits.print_results');
    Route::get('/patient-visits/{visit}/results-details', [PatientVisitController::class, 'resultsDetails'])->name('patient_visits.results_details');
    Route::post('/print-multiple-results', [PatientVisitController::class, 'printMultipleResults'])->name('print_multiple_results');



    // NHIF Integration