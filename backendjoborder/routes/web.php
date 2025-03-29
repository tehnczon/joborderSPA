<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobOrderController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});


Route::get('/job-orders/create', [JobOrderController::class, 'create'])->name('job-orders.create');
Route::post('/job-orders', [JobOrderController::class, 'store'])->name('job-orders.store');

require __DIR__.'/auth.php';
