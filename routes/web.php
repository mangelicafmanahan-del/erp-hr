<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::resource('employees', EmployeeController::class);

Route::post('/employees/{employee}/documents', [EmployeeController::class, 'storeDocument'])
    ->name('employees.documents.store');

Route::delete('/employees/documents/{document}', [EmployeeController::class, 'destroyDocument'])
    ->name('employees.documents.destroy');
