<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayrollController;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/employees/{employee}/summary', [EmployeeController::class, 'summary'])
    ->name('employees.summary');

Route::resource('employees', EmployeeController::class);

Route::post('/employees/{employee}/documents', [EmployeeController::class, 'storeDocument'])
    ->name('employees.documents.store');

Route::delete('/employees/documents/{document}', [EmployeeController::class, 'destroyDocument'])
    ->name('employees.documents.destroy');

    Route::get('/payroll', [PayrollController::class, 'dashboard'])->name('payroll.dashboard');
Route::get('/payroll/runs/create', [PayrollController::class, 'createRun'])->name('payroll.runs.create');
Route::post('/payroll/runs', [PayrollController::class, 'storeRun'])->name('payroll.runs.store');
Route::get('/payroll/payslips/{payslip}', [PayrollController::class, 'showPayslip'])->name('payroll.payslips.show');

Route::post('/employees/{employee}/salary', [PayrollController::class, 'storeSalary'])->name('employees.salary.store');
