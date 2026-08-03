<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
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

        Route::post(
            '/projects/{project}/archive',
            [ProjectController::class, 'archive']
        )->name('projects.archive');

        Route::post(
            '/projects/{project}/restore',
            [ProjectController::class, 'restore']
        )->name('projects.restore');

        Route::post(
            '/projects/{project}/status',
            [ProjectController::class, 'changeStatus']
        )->name('projects.change-status');

        Route::post(
            '/projects/{project}/comments',
            [CommentController::class, 'storeProjectComment']
        )->name('projects.comments.store');

        Route::post(
            '/comments/{comment}/replies',
            [CommentController::class, 'storeReply']
        )->name('comments.replies.store');

        Route::patch(
            '/comments/{comment}',
            [CommentController::class, 'update']
        )->name('comments.update');

        Route::delete(
            '/comments/{comment}',
            [CommentController::class, 'destroy']
        )->name('comments.destroy');

        Route::post(
            '/projects/{project}/attachments',
            [AttachmentController::class, 'storeProjectAttachment']
        )->name('projects.attachments.store');

        Route::get(
            '/attachments/{attachment}/download',
            [AttachmentController::class, 'download']
        )->name('attachments.download');

        Route::post(
            '/attachments/{attachment}/replace',
            [AttachmentController::class, 'replace']
        )->name('attachments.replace');

        Route::delete(
            '/attachments/{attachment}',
            [AttachmentController::class, 'destroy']
        )->name('attachments.destroy');

        Route::delete(
            '/projects/{project}/attachments/{attachment}',
            [AttachmentController::class, 'destroy']
        )->name('projects.attachments.destroy');

        Route::get(
            '/notifications',
            [NotificationController::class, 'index']
        )->name('notifications.index');

        Route::post(
            '/notifications/{notification}/read',
            [NotificationController::class, 'markAsRead']
        )->name('notifications.read');

        Route::post(
            '/notifications/read-all',
            [NotificationController::class, 'markAllAsRead']
        )->name('notifications.read-all');

        Route::delete(
            '/notifications/{notification}',
            [NotificationController::class, 'destroy']
        )->name('notifications.destroy');

        Route::get(
            '/calendar',
            [CalendarController::class, 'index']
        )->name('calendar.index');

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
            '/teams/{team}/members/{member}/role',
            [TeamMemberController::class, 'updateRole']
        )->name('teams.members.update-role');

        Route::post(
            '/teams/{team}/transfer-ownership',
            [TeamMemberController::class, 'transferOwnership']
        )->name('teams.transfer-ownership');

        Route::post(
            '/teams/{team}/projects/{project}',
            [TeamProjectController::class, 'attach']
        )->name('teams.projects.attach');

        Route::delete(
            '/teams/{team}/projects/{project}',
            [TeamProjectController::class, 'detach']
        )->name('teams.projects.detach');

        Route::get(
            '/invitations',
            [TeamInvitationController::class, 'index']
        )->name('invitations.index');

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
