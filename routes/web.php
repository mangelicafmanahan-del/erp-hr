<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\RecruitmentController;

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

Route::post('/employees/{employee}/history', [EmployeeController::class, 'storeEmploymentHistory'])
    ->name('employees.history.store');

Route::delete('/employees/history/{history}', [EmployeeController::class, 'destroyEmploymentHistory'])
    ->name('employees.history.destroy');

    Route::get('/payroll', [PayrollController::class, 'dashboard'])->name('payroll.dashboard');
Route::get('/payroll/runs/create', [PayrollController::class, 'createRun'])->name('payroll.runs.create');
Route::post('/payroll/runs', [PayrollController::class, 'storeRun'])->name('payroll.runs.store');
Route::get('/payroll/payslips/{payslip}', [PayrollController::class, 'showPayslip'])->name('payroll.payslips.show');

Route::post('/employees/{employee}/salary', [PayrollController::class, 'storeSalary'])->name('employees.salary.store');

Route::get('/recruitment', [RecruitmentController::class, 'dashboard'])->name('recruitment.dashboard');

Route::get('/recruitment/vacancies', [RecruitmentController::class, 'vacancies'])->name('recruitment.vacancies');
Route::post('/recruitment/vacancies', [RecruitmentController::class, 'storeVacancy'])->name('recruitment.vacancies.store');
Route::post('/recruitment/vacancies/{vacancy}/close', [RecruitmentController::class, 'closeVacancy'])->name('recruitment.vacancies.close');

Route::get('/recruitment/applicants', [RecruitmentController::class, 'applicants'])->name('recruitment.applicants');
Route::get('/recruitment/applicants/create', [RecruitmentController::class, 'createApplicant'])->name('recruitment.applicants.create');
Route::post('/recruitment/applicants', [RecruitmentController::class, 'storeApplicant'])->name('recruitment.applicants.store');
Route::get('/recruitment/applicants/{applicant}', [RecruitmentController::class, 'showApplicant'])->name('recruitment.applicants.show');
Route::post('/recruitment/applicants/{applicant}/status', [RecruitmentController::class, 'updateApplicantStatus'])->name('recruitment.applicants.status');
Route::post('/recruitment/applicants/{applicant}/convert', [RecruitmentController::class, 'convertToEmployee'])->name('recruitment.applicants.convert');

Route::post('/recruitment/applicants/{applicant}/interviews', [RecruitmentController::class, 'storeInterview'])->name('recruitment.applicants.interviews.store');
Route::get('/recruitment/interviews', [RecruitmentController::class, 'interviewsIndex'])->name('recruitment.interviews');

Route::post('/recruitment/applicants/{applicant}/offer', [RecruitmentController::class, 'storeOffer'])->name('recruitment.applicants.offer.store');
Route::post('/recruitment/offers/{offer}/status', [RecruitmentController::class, 'updateOfferStatus'])->name('recruitment.offers.status');
Route::get('/recruitment/offers', [RecruitmentController::class, 'offersIndex'])->name('recruitment.offers');

Route::post('/recruitment/onboarding/{task}/toggle', [RecruitmentController::class, 'toggleOnboardingTask'])->name('recruitment.onboarding.toggle');
