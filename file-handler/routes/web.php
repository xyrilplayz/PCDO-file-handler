<?php

use App\Http\Controllers\CoopProgramChecklistcontroller;
use App\Http\Controllers\markPaid;
use App\Http\Controllers\Programs;
use App\Http\Controllers\xy;
use App\Http\Controllers\Cooperatives;
use App\Http\Controllers\CoopProgramController;
use App\Models\Program;
use App\Models\Cooperative;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Checklist;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PaymentScheduleController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\CoopDetailsController;
use App\Http\Controllers\AmmortizationScheduleController;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LoanOverdueNotification;
use App\Http\Controllers\OldCsvController;



Route::get('/', function () {
    $cooperatives = Cooperative::all();
    return view('welcome', compact('cooperatives'));
})->name('home');

//login
Route::get('/login', [xy::class, 'login'])->name('login');
Route::post('/login', [xy::class, 'loginPost'])->name('login.post');
//registration
Route::get('/registration', [xy::class, 'registration'])->name('registration');
Route::post('/registration', [xy::class, 'registrationPost'])->name('registration.post');
//logout
Route::get('/logout', [xy::class, 'logout'])->name('logout');

Route::group(['middleware' => 'auth'], function () {
    // Cooperatives routes create
    Route::get('/createcooperative', [Cooperatives::class, 'coop'])->name('cooperatives.create');
    Route::post('/createcooperative', [Cooperatives::class, 'creatcoopPost'])->name('cooperatives.post');

    //cooperative details
    Route::get('/cooperatives/{id}', [CoopDetailsController::class, 'index'])
        ->name('cooperatives.show');

    //creating programs for cooperatives
    Route::get('/program', [CoopProgramController::class, 'index'])->name('program.index');
    Route::get('/program/create', [CoopProgramController::class, 'create'])->name('program.create');
    Route::post('/program', [CoopProgramController::class, 'store'])->name('program.store');
    Route::get('/checklist/{coopProgramid}', [CoopProgramChecklistcontroller::class, 'show'])->name('checklists.show');


    //search upload
    Route::post('/checklist/{cooperative}/upload', [CoopProgramChecklistcontroller::class, 'upload'])->name('checklist.upload');
    Route::get('/checklist/download/{id}', [CoopProgramChecklistcontroller::class, 'download'])->name('checklist.download');
    Route::get('/uploads/search', [Checklist::class, 'searchUploads'])->name('uploads.search');
    Route::delete('/uploads/delete/{id}', [Checklist::class, 'delete'])->name('checklist.delete');

    //loan
    Route::POST('Ammortization_Schedule/{id}', [AmmortizationScheduleController::class, 'generateSchedule'])->name('generate.create');
    Route::get('/loan-tracker/{coopProgram}', [AmmortizationScheduleController::class, 'show'])
        ->name('loan.tracker.show');
    Route::post('/schedules/{schedule}/mark-paid', [AmmortizationScheduleController::class, 'markPaid'])->name('schedules.markPaid');
    Route::post(
        '/schedules/{schedule}/send-overdue',
        [AmmortizationScheduleController::class, 'sendOverdueEmail']
    )->name('schedules.sendOverdueEmail');


    Route::get('/old', [OldCsvController::class, 'index'])->name('old.index');
    Route::get('/old/view/{id}', [OldCsvController::class, 'view'])->name('old.view');
    Route::get('/old/download/{id}', [OldCsvController::class, 'download'])->name('old.download');


    Route::resource('loans', LoanController::class);


    //amount edits
    Route::put('/loans/{loan}/update-amount', [AmmortizationScheduleController::class, 'updateAmount'])->name('loans.updateAmount');
    Route::post('/schedules/{schedule}/note-payment', [AmmortizationScheduleController::class, 'notePayment'])->name('schedules.post');

    Route::post('/loans/schedules/{schedule}/penalty', [AmmortizationScheduleController::class, 'penalty'])
        ->name('schedules.penalty');


});
