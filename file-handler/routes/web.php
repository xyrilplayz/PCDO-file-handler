<?php

use App\Http\Controllers\CoopProgramChecklistcontroller;
use App\Http\Controllers\markPaid;
use App\Http\Controllers\NotificationController;
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
use App\Http\Controllers\CoopProgramProgressController;
use App\Http\Controllers\ResolvedController;



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
    Route::post('/createcooperative', [Cooperatives::class, 'store'])->name('cooperatives.post');

    //cooperative details
    Route::get('/cooperatives/{id}', [Cooperative::class, 'index'])
        ->name('cooperatives.show');
    Route::get('/cooperatives/{id}/details', [CoopDetailsController::class, 'index'])
        ->name('cooperatives.details');
    Route::get('/cooperatives/{id}', [Cooperatives::class, 'show'])->name('cooperative.show');
    Route::get('/cooperative/details/{id}', [Cooperatives::class, 'getDetails']);
    Route::post('/programs/{id}/archive', [CoopProgramController::class, 'archiveFinishedProgram'])
        ->name('programs.archive');


    //creating programs for cooperatives
    Route::get('/coop-program/{coopProgram}', [CoopProgramController::class, 'show'])->name('coop_program.show');

    Route::get('/program/create', [CoopProgramController::class, 'create'])->name('program.create');
    Route::post('/program', [CoopProgramController::class, 'store'])->name('program.store');
    Route::get('/checklist/{coopProgramid}', [CoopProgramChecklistcontroller::class, 'show'])->name('checklists.show');
    Route::post('/program/{coopProgram}/finalize', [CoopProgramController::class, 'finalizeLoan'])->name('program.finalizeLoan');

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

    Route::get('/amortization/{loan}/download', [AmmortizationScheduleController::class, 'downloadPdf'])->name('amortization.download');
    Route::post('/amortization/{loan}/incomplete', [AmmortizationScheduleController::class, 'markIncomplete'])->name('loan.incomplete');
    




    Route::get('/old', [OldCSVController::class, 'index'])->name('old.index');
    Route::get('/old/{coopProgram}', [OldCSVController::class, 'show'])->name('old.show');
    Route::get('/old/file/{id}/view', [OldCSVController::class, 'view'])->name('old.view');
    Route::get('/old/file/{id}/download', [OldCSVController::class, 'downloadPdf'])->name('old.download');

    //notifications
    // routes/web.php
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}', [NotificationController::class, 'show'])->name('notifications.show');


    Route::get('/programs/{program}/documents/create', [CoopProgramProgressController::class, 'create'])->name('progress.index');
    Route::post('/programs/{program}/documents', [CoopProgramProgressController::class, 'store'])->name('progress.store');
    Route::get('/progress-reports/{report}/download', [CoopProgramProgressController::class, 'download'])->name('progress.download');
    Route::get('/progress-reports/{report}/show', [CoopProgramProgressController::class, 'show'])
        ->name('progress.show');


    //amount edits
    Route::put('/loans/{loan}/update-amount', [AmmortizationScheduleController::class, 'updateAmount'])->name('loans.updateAmount');
    Route::post('/schedules/{schedule}/note-payment', [AmmortizationScheduleController::class, 'notePayment'])->name('schedules.post');

    Route::post('/loans/schedules/{schedule}/penalty', [AmmortizationScheduleController::class, 'penalty'])
        ->name('schedules.penalty');


    //resolved
    Route::get('/resolved/{coopProgram}/upload', [ResolvedController::class, 'create'])->name('resolved.create');
    Route::post('/resolved/{coopProgram}', [ResolvedController::class, 'store'])->name('resolved.store');
    Route::get('/resolved/download/{id}', [ResolvedController::class, 'download'])->name('resolved.download');

});
