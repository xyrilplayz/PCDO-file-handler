<?php

use App\Http\Controllers\markPaid;
use App\Http\Controllers\Programs;
use App\Http\Controllers\xy;
use App\Http\Controllers\Cooperatives;
use App\Http\Controllers\CooperativeUpload;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Checklist;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PaymentScheduleController;
use App\Http\Controllers\TestController;



Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login', [xy::class, 'login'])->name('login');
Route::post('/login', [xy::class, 'loginPost'])->name('login.post');

Route::get('/registration', [xy::class, 'registration'])->name('registration');
Route::post('/registration', [xy::class, 'registrationPost'])->name('registration.post');

Route::get('/logout', [xy::class, 'logout'])->name('logout');

Route::group(['middleware' => 'auth'], function () {
    // Cooperatives routes
    Route::get('/createcooperative', [Cooperatives::class, 'coop'])->name('cooperatives.create');
    Route::post('/createcooperative_details', [Cooperatives::class, 'creatcoopPost'])->name('cooperatives.post');
    Route::get('/checklist/{cooperative}', [Checklist::class, 'show'])->name('checklist.show');
    Route::post('/checklist/{cooperative}/upload', [Checklist::class, 'upload'])->name('checklist.upload');
    Route::get('/checklist/download/{id}', [Checklist::class, 'download'])->name('checklist.download');
    Route::get('/uploads/search', [Checklist::class, 'searchUploads'])->name('uploads.search');
    Route::delete('/uploads/delete/{id}', [Checklist::class, 'delete'])->name('checklist.delete');
    Route::resource('loans', LoanController::class);
    Route::get('loans/{loan}/schedules', [PaymentScheduleController::class, 'index'])->name('schedules.show');
    Route::put('/loans/{loan}/update-amount', [LoanController::class, 'updateAmount'])->name('loans.updateAmount');
    Route::post('/schedules/{schedule}/note-payment', [markPaid::class, 'notePayment'])->name('schedules.post');
    Route::post('/schedules/{schedule}/mark-paid', [markPaid::class, 'markPaid'])->name('schedules.markPaid');
    Route::post('/loans/{loan}/send-overdue-email', [LoanController::class, 'sendOverdueEmail'])
        ->name('loans.sendOverdueEmail');
    Route::post('/loans/schedules/{schedule}/penalty', [LoanController::class, 'penalty'])
        ->name('schedules.penalty');


});
