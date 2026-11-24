<?php

use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;




Route::redirect('/', '/invoice');

Route::prefix('invoice')->name('invoice.')->controller(InvoiceController::class)->group(function () {
    Route::get('/', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/preview', 'preview')->name('preview');
    Route::get('/download', 'download')->name('download')->middleware('throttle:invoice.download');
});

Route::prefix('expenses')->name('expenses.')->controller(ExpenseController::class)->group(function () {
    Route::get('/', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/preview', 'preview')->name('preview');
    Route::get('/download', 'download')->name('download');
});
