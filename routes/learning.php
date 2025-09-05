<?php
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/learning/ajax', [App\Http\Controllers\LearningController::class, 'ajax'])->name('learn.ajax');
    Route::get('/learning/ajax/fetch-data', [App\Http\Controllers\LearningController::class, 'fetchUserData'])->name('learn.ajax.fetchData');

    Route::get('/learning/searchable-dropdown', [App\Http\Controllers\LearningController::class, 'dropdown'])->name('learn.dropdown');
    Route::get('/learning/searchable-dropdown/fetch-names', [App\Http\Controllers\LearningController::class, 'fetchNames'])->name('learn.user_search');

});

