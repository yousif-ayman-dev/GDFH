<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::resource('projects', ProjectController::class);

    Route::post(
        '/projects/{project}/members',
        [ProjectMemberController::class, 'store']
    )->name('projects.members.store');

    Route::patch(
        '/projects/{project}/members/{member}',
        [ProjectMemberController::class, 'update']
    )->name('projects.members.update');

    Route::delete(
        '/projects/{project}/members/{member}',
        [ProjectMemberController::class, 'destroy']
    )->name('projects.members.destroy');

    Route::resource('teams', TeamController::class);

    Route::post(
        '/teams/{team}/members',
        [TeamMemberController::class, 'store']
    )->name('teams.members.store');

    Route::patch(
        '/teams/{team}/members/{member}',
        [TeamMemberController::class, 'update']
    )->name('teams.members.update');

    Route::delete(
        '/teams/{team}/members/{member}',
        [TeamMemberController::class, 'destroy']
    )->name('teams.members.destroy');
});

require __DIR__.'/auth.php';
