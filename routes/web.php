<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\ProjectOwnerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LogtimeController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('role:manager,leader');

    Route::get('/user', [UserController::class, 'index'])->name('user.list')->middleware('role:manager,leader');
    Route::post('/user', [UserController::class, 'store'])->name('user.store')->middleware('role:manager,leader');
    Route::put('/user/{id}', [UserController::class, 'update'])->name('user.update')->middleware('role:manager,leader');
    Route::patch('/user/{user}/wfa', [UserController::class, 'toggleWfa'])->name('user.toggleWfa')->middleware('role:manager,leader');
    Route::post('/user/{user}', [UserController::class, 'changeIsActive'])->name('user.changeIsActive')->middleware('role:manager,leader');

    Route::get('/skill', [SkillController::class, 'index'])->name('skill.list');
    Route::post('/skill', [SkillController::class, 'store'])->name('skill.store');
    Route::delete('/skill/{skill}', [SkillController::class, 'destroy'])->name('skill.destroy');
    Route::get('/skill/export', [SkillController::class, 'export'])->name('skill.export')->middleware('role:manager,communicator');

    Route::get('/project-owner', [ProjectOwnerController::class, 'index'])->name('projectOwner.list')->middleware('role:manager,leader,communicator');
    Route::post('/project-owner', [ProjectOwnerController::class, 'store'])->name('projectOwner.store')->middleware('role:manager,leader,communicator');
    Route::put('/project-owner/{id}', [ProjectOwnerController::class, 'update'])->name('projectOwner.update')->middleware('role:manager,leader,communicator');
    Route::post('/project-owner/{projectOwner}', [ProjectOwnerController::class, 'changeIsActive'])->name('projectOwner.changeIsActive')->middleware('role:manager,leader,communicator');

    Route::get('/project', [ProjectController::class, 'index'])->name('project.list');
    Route::post('/project', [ProjectController::class, 'store'])->name('project.store')->middleware('role:manager,leader,communicator');
    Route::put('/project/{id}', [ProjectController::class, 'update'])->name('project.update')->middleware('role:manager,leader,communicator');
    Route::post('/project/{project}', [ProjectController::class, 'changeIsActive'])->name('project.changeIsActive')->middleware('role:manager,leader,communicator');

    Route::get('/task', [TaskController::class, 'index'])->name('task.list');
    Route::get('/task/{task}', [TaskController::class, 'show'])->name('task.show');
    Route::post('/task', [TaskController::class, 'store'])->name('task.store')->middleware('role:manager,leader,communicator');
    Route::post('/task/{task}', [TaskController::class, 'changeIsActive'])->name('task.changeIsActive')->middleware('role:manager,leader,communicator');
    Route::post('/task/{pullRequest}/reply', [TaskController::class, 'replyTask'])->name('task.replyTask');
    Route::post('/task/{task}/comment', [TaskController::class, 'commentTask'])->name('task.commentTask');
    Route::put('/task/{task}/edit', [TaskController::class, 'update'])->name('task.update')->middleware('role:manager,leader,communicator');
    Route::put('/task/{task}/assign', [TaskController::class, 'assignTask'])->name('task.assignTask')->middleware('role:manager,leader');
    Route::put('/task/{task}/pr', [TaskController::class, 'prTask'])->name('task.prTask');
    Route::put('/task/{task}/close', [TaskController::class, 'close'])->name('task.close')->middleware('role:manager,leader,communicator');
    Route::put('/task/{task}/review/{reviewerId}/complete', [TaskController::class, 'markReviewComplete'])->name('task.review.complete')->middleware('auth');
    
    Route::get('/logtime', [LogtimeController::class, 'index'])->name('logtime.list');
    Route::get('/logtime/export', [LogtimeController::class, 'export'])->name('logtime.export')->middleware('role:manager,communicator');
    Route::post('/logtime', [LogtimeController::class, 'store'])->name('logtime.store');
    Route::delete('/logtime/{id}', [LogtimeController::class, 'destroy'])->name('logtime.destroy')->middleware('role:manager,communicator');

    Route::get('/log', [LogController::class, 'index'])->name('log.list')->middleware('role:leader');

    Route::get('/import', [ImportController::class, 'index'])->name('import.index')->middleware('role:manager,communicator');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store')->middleware('role:manager,communicator');
    Route::get('/import/template', [ImportController::class, 'template'])->name('import.template')->middleware('role:manager,communicator');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance')->middleware(['role:manager', 'check.attendance']);
    Route::get('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export')->middleware(['role:manager', 'check.attendance']);
});

Route::get('/recognize', function () {
    return Inertia::render('recognize/Index');
})->name('attendance.recognize')->middleware('check.attendance');

Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store')->middleware('check.attendance');

Route::post('/attendance/toggle', [AttendanceController::class, 'toggleStatus'])->name('attendance.toggle');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'Index'])->name('profile.Index');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/skills', [ProfileController::class, 'updateSkills'])->name('profile.skills.update');
});

require __DIR__.'/auth.php';