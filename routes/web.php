<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ProjectTeamController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\TeamProjectController;
use App\Http\Controllers\Auth\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'onboarded'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'index'])
        ->name('onboarding');

    Route::post('/onboarding', [OnboardingController::class, 'store'])
        ->name('onboarding.store');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::middleware('onboarded')->group(function () {
        Route::resource('projects', ProjectController::class);

        Route::resource('projects.tasks', TaskController::class)
            ->scoped()
            ->names('projects.tasks');

        Route::post(
            '/projects/{project}/teams',
            [ProjectTeamController::class, 'store']
        )->name('projects.teams.store');

        Route::delete(
            '/projects/{project}/teams/{team}',
            [ProjectTeamController::class, 'destroy']
        )->name('projects.teams.destroy');

        Route::post(
            '/projects/{project}/attachments',
            [AttachmentController::class, 'store']
        )->name('projects.attachments.store');

        Route::delete(
            '/projects/{project}/attachments/{attachment}',
            [AttachmentController::class, 'destroy']
        )->name('projects.attachments.destroy');

        Route::resource('projects.reviews', ReviewController::class)
            ->scoped()
            ->names('projects.reviews');

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

        Route::post(
            '/teams/{team}/projects/{project}',
            [TeamProjectController::class, 'attach']
        )->name('teams.projects.attach');

        Route::delete(
            '/teams/{team}/projects/{project}',
            [TeamProjectController::class, 'detach']
        )->name('teams.projects.detach');

        Route::post(
            '/teams/{team}/invitations',
            [TeamInvitationController::class, 'store']
        )->name('teams.invitations.store');

        Route::post(
            '/invitations/{invitation}/accept',
            [TeamInvitationController::class, 'accept']
        )->name('invitations.accept');

        Route::post(
            '/invitations/{invitation}/reject',
            [TeamInvitationController::class, 'reject']
        )->name('invitations.reject');

        Route::post(
            '/invitations/{invitation}/cancel',
            [TeamInvitationController::class, 'cancel']
        )->name('invitations.cancel');
    });
});

require __DIR__.'/auth.php';
