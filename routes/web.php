<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GanttController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MessagingController;
use App\Http\Controllers\TimeTrackingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ProjectTeamController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\TeamProjectController;
use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\AIFeatureController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
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

    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])
        ->name('profile.avatar.destroy');

    Route::get('/settings', [SettingsController::class, 'index'])
        ->name('settings.index');

    Route::patch('/settings/notifications', [SettingsController::class, 'updateNotifications'])
        ->name('settings.notifications.update');

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
            '/notifications/poll',
            [NotificationController::class, 'poll']
        )->name('notifications.poll');

        Route::post(
            '/notifications/{notification}/read-json',
            [NotificationController::class, 'markAsReadJson']
        )->name('notifications.read-json');

        Route::post(
            '/notifications/read-all-json',
            [NotificationController::class, 'markAllAsReadJson']
        )->name('notifications.read-all-json');

        Route::get(
            '/calendar',
            [CalendarController::class, 'index']
        )->name('calendar.index');

        Route::post(
            '/calendar/events',
            [CalendarController::class, 'storeEvent']
        )->name('calendar.events.store');

        Route::put(
            '/calendar/events/{event}',
            [CalendarController::class, 'updateEvent']
        )->name('calendar.events.update');

        Route::delete(
            '/calendar/events/{event}',
            [CalendarController::class, 'destroyEvent']
        )->name('calendar.events.destroy');

        Route::get(
            '/tasks',
            [TaskController::class, 'all']
        )->name('tasks.index');

        Route::get(
            '/kanban',
            [KanbanController::class, 'index']
        )->name('kanban.index');

        Route::post(
            '/kanban/tasks/{task}/status',
            [KanbanController::class, 'updateStatus']
        )->name('kanban.tasks.update-status');

        Route::get(
            '/gantt',
            [GanttController::class, 'index']
        )->name('gantt.index');

        Route::get(
            '/time-tracking',
            [TimeTrackingController::class, 'index']
        )->name('time-tracking.index');

        Route::post(
            '/time-tracking/start',
            [TimeTrackingController::class, 'start']
        )->name('time-tracking.start');

        Route::post(
            '/time-tracking/{worklog}/pause',
            [TimeTrackingController::class, 'pause']
        )->name('time-tracking.pause');

        Route::post(
            '/time-tracking/{worklog}/resume',
            [TimeTrackingController::class, 'resume']
        )->name('time-tracking.resume');

        Route::post(
            '/time-tracking/{worklog}/stop',
            [TimeTrackingController::class, 'stop']
        )->name('time-tracking.stop');

        Route::post(
            '/time-tracking/manual',
            [TimeTrackingController::class, 'storeManual']
        )->name('time-tracking.manual');

        Route::delete(
            '/time-tracking/{worklog}',
            [TimeTrackingController::class, 'destroy']
        )->name('time-tracking.destroy');

        Route::get(
            '/reports',
            [ReportsController::class, 'index']
        )->name('reports.index');

        Route::get(
            '/reports/export/csv',
            [ReportsController::class, 'exportCsv']
        )->name('reports.export.csv');

        Route::get(
            '/reports/export/pdf',
            [ReportsController::class, 'exportPdf']
        )->name('reports.export.pdf');

        Route::get(
            '/ai',
            [AIController::class, 'index']
        )->name('ai.index');

        Route::post(
            '/ai/conversations',
            [AIController::class, 'storeConversation']
        )->name('ai.conversations.store');

        Route::post(
            '/ai/conversations/{conversation}/messages',
            [AIController::class, 'sendMessage']
        )->name('ai.conversations.messages.store');

        Route::delete(
            '/ai/conversations/{conversation}',
            [AIController::class, 'destroyConversation']
        )->name('ai.conversations.destroy');

        Route::get(
            '/search',
            [SearchController::class, 'index']
        )->name('search.index');

        Route::get(
            '/marketplace',
            [MarketplaceController::class, 'index']
        )->name('marketplace.index');

        Route::get(
            '/marketplace/services/create',
            [MarketplaceController::class, 'createService']
        )->name('marketplace.services.create');

        Route::post(
            '/marketplace/services',
            [MarketplaceController::class, 'storeService']
        )->name('marketplace.services.store');

        Route::get(
            '/marketplace/services/{service}',
            [MarketplaceController::class, 'showService']
        )->name('marketplace.services.show');

        Route::get(
            '/marketplace/services/{service}/edit',
            [MarketplaceController::class, 'editService']
        )->name('marketplace.services.edit');

        Route::put(
            '/marketplace/services/{service}',
            [MarketplaceController::class, 'updateService']
        )->name('marketplace.services.update');

        Route::delete(
            '/marketplace/services/{service}',
            [MarketplaceController::class, 'destroyService']
        )->name('marketplace.services.destroy');

        Route::post(
            '/marketplace/services/{service}/order',
            [MarketplaceController::class, 'orderService']
        )->name('marketplace.services.order');

        Route::get(
            '/marketplace/freelancer/profile/edit',
            [MarketplaceController::class, 'editFreelancerProfile']
        )->name('marketplace.freelancers.profile.edit');

        Route::put(
            '/marketplace/freelancer/profile',
            [MarketplaceController::class, 'updateFreelancerProfile']
        )->name('marketplace.freelancers.profile.update');

        Route::get(
            '/marketplace/freelancers/{user}',
            [MarketplaceController::class, 'showFreelancer']
        )->name('marketplace.freelancers.show');

        Route::post(
            '/projects/{project}/proposals',
            [ProposalController::class, 'store']
        )->name('projects.proposals.store');

        Route::post(
            '/proposals/{proposal}/accept',
            [ProposalController::class, 'accept']
        )->name('proposals.accept');

        Route::post(
            '/proposals/{proposal}/reject',
            [ProposalController::class, 'reject']
        )->name('proposals.reject');

        Route::post(
            '/proposals/{proposal}/withdraw',
            [ProposalController::class, 'withdraw']
        )->name('proposals.withdraw');

        Route::get(
            '/contracts',
            [ContractController::class, 'index']
        )->name('contracts.index');

        Route::get(
            '/contracts/{contract}',
            [ContractController::class, 'show']
        )->name('contracts.show');

        Route::post(
            '/contracts/{contract}/complete',
            [ContractController::class, 'complete']
        )->name('contracts.complete');

        Route::get(
            '/chat',
            [MessagingController::class, 'index']
        )->name('messaging.index');

        Route::post(
            '/chat/start/{user}',
            [MessagingController::class, 'startConversation']
        )->name('messaging.start');

        Route::post(
            '/chat/{conversation}/messages',
            [MessagingController::class, 'sendMessage']
        )->name('messaging.send');

        Route::get(
            '/chat/{conversation}/poll',
            [MessagingController::class, 'pollMessages']
        )->name('messaging.poll');

        Route::post(
            '/chat/{conversation}/messages-json',
            [MessagingController::class, 'sendMessageJson']
        )->name('messaging.send-json');

        Route::resource('projects.reviews', ReviewController::class)
            ->scoped()
            ->names('projects.reviews');

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

// ─── Admin Panel (System Administrators only) ─────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::post('/users/{user}/toggle-ban', [AdminController::class, 'toggleBan'])->name('users.toggle-ban');
    Route::get('/projects', [AdminController::class, 'projects'])->name('projects');
});

// ─── AI Feature Endpoints ─────────────────────────────────────────────────
Route::middleware(['auth', 'onboarded'])->group(function () {
    Route::post('/ai/analyze-project', [AIFeatureController::class, 'analyzeProject'])->name('ai.analyze-project');
    Route::post('/ai/suggest-members', [AIFeatureController::class, 'suggestMembers'])->name('ai.suggest-members');
    Route::get('/ai/recommended-projects', [AIFeatureController::class, 'recommendedProjects'])->name('ai.recommended-projects');
});
