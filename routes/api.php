<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PersonalTaskController;
use App\Http\Controllers\Projectcontroller;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;

// Authintication and password
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/signin', [AuthController::class, 'signin']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/forgot-password', [AuthController::class, 'sendResetCode']);
Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/user/language', [UserSettingsController::class, 'updateLanguage']);
        Route::post('/user/theme', [UserSettingsController::class, 'updateTheme']);
        Route::post('/user/fcm-token', [UserSettingsController::class, 'updateFcmToken']);
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread', [NotificationController::class, 'unread']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
        /*
            admin
         */
        Route::middleware(['auth:sanctum', 'admin'])->group(function () {
            // users
            Route::get('/pending-users', [AuthController::class, 'pendingUsers']);
            Route::get('/users/count', [AuthController::class, 'usersCount']);
            Route::put('/approve-user/{user}', [AuthController::class, 'approveUser']);
            Route::put('/reject-user/{user}', [AuthController::class, 'rejectUser']);
            Route::delete('/delete-user/{user}', [AuthController::class, 'deleteUser']);
            // projects
            Route::get('/projects', [Projectcontroller::class, 'index']);
            Route::get('/projects-count', [Projectcontroller::class, 'projectsCount']);
            // teams
            Route::get('/teams', [TeamController::class, 'index']);
            Route::get('/teams-count', [TeamController::class, 'teamsCount']);
            // tasks
            Route::get('/tasks', [TaskController::class, 'index']);
            Route::get('/tasks-count', [TaskController::class, 'tasksCount']);
            // dashboard
            Route::get('/dashboard/admin', [DashboardController::class, 'adminState']);
        });
        /*
            manager
         */
        Route::middleware(['auth:sanctum', 'manager'])->group(function () {
            // projects
            Route::post('/projects', [Projectcontroller::class, 'store']);
            Route::put('/projects/{project}', [Projectcontroller::class, 'update']);
            Route::delete('/projects/{project}', [Projectcontroller::class, 'destroy']);
            Route::get('/my-projects', [Projectcontroller::class, 'myprojects']);
            // teams
            Route::get('/my-teams', [Projectcontroller::class, 'myteams']);
            Route::post('/teams', [TeamController::class, 'store']);
            Route::put('/teams/{team}', [TeamController::class, 'update']);
            Route::delete('/teams/{team}', [TeamController::class, 'destroy']);
            Route::post('/teams/{team}/add-member', [TeamController::class, 'addMember']);
            Route::post('/teams/{team}/remove-member', [TeamController::class, 'removeMember']);
            // tasks
            Route::get('/projects/{project}/tasks', [TaskController::class, 'projectTasks']);
            Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
            Route::get('/projects/filter', [ProjectController::class,'filterProjects']);
            Route::put('/tasks/{task}', [TaskController::class, 'update']);
            Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
            Route::post('/tasks/{task}/assign-members', [TaskController::class, 'assignMembers']);
            Route::post('/tasks/{task}/add-dependency', [TaskController::class, 'addDependency']);
            Route::delete('/tasks/{task}/dependency/{dependency}', [TaskController::class, 'removeDependency']);
            Route::get('/projects/{project}/tasks-count', [TaskController::class, 'tasksCount']);
            Route::get('/completed_tasks', [TaskController::class, 'completedTasks']);
            Route::post('/tasks/{task}/accept', [TaskController::class, 'approvedTasks']);
            Route::post('/tasks/{task}/reject', [TaskController::class, 'rejectTasks']);
            // history
            Route::get('/projects/{project}/history', [HistoryController::class, 'projectIndex']);
            Route::get('/project-history/{log}', [HistoryController::class, 'projectLog']);
            Route::get('/tasks/{task}/history', [HistoryController::class, 'taskIndex']);
            Route::get('/task-history/{log}', [HistoryController::class, 'taskLog']);
            // dashboard and reports
            Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
            Route::get('/dashboard/progress', [DashboardController::class, 'userProgress']);
            Route::get('/dashboard/project/{project}/progress', [DashboardController::class, 'taskProgress']);
            Route::get('/dashboard/members-progress', [DashboardController::class, 'membersProgress']);
            Route::get('/dashboard/overdue-tasks', [DashboardController::class, 'overdueTasks']);
            Route::get('/dashboard/upcoming-deadlines', [DashboardController::class, 'upcomingDeadlines']);
            Route::get('/dashboard/recent-activities', [DashboardController::class, 'recentActivities']);
            Route::get('/reports/user/{user}', [ReportController::class, 'userReport']);
            Route::get('/reports/project/{project}', [ReportController::class, 'projectReport']);
            Route::get('/reports/performance/{user}', [ReportController::class, 'userPerformance']);
            Route::get('/reports/all-performance', [ReportController::class, 'allUsersPerformance']);
        });
        /*
            member
         */
        Route::middleware(['auth:sanctum', 'member'])->group(function () {
            // invitations for teams
            Route::post('/teams/{team}/accept', [TeamController::class, 'acceptInvitation']);
            Route::post('/teams/{team}/reject', [TeamController::class, 'rejectInvitation']);
            // tasks
            Route::get('/my-tasks', [TaskController::class, 'myTasks']);
            Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
            // dashboard and reports
            Route::get('/reports/my-performance', [ReportController::class, 'myPerformance']);
            Route::get('/my-rewards', [RewardController::class, 'myReward']);
        });
        Route::middleware(['auth:sanctum'])->group(function () {
            // projects
            Route::get('/projects/search', [Projectcontroller::class, 'search']);
            Route::get('/projects/{id}', [Projectcontroller::class, 'show']);
            
            // teams
            Route::get('/teams/{team}', [TeamController::class, 'show']);
            Route::get('/invitations', [TeamController::class, 'allInvitation']);
            // tasks
            Route::get('/tasks/{task}', [TaskController::class, 'show']);
            Route::post('/tasks/{task}/attach-file', [TaskController::class, 'attachFile']);
            Route::delete('/tasks/{task}/file/{attachment}', [TaskController::class, 'removeAttachment']);

            Route::get('/categories', [CategoryController::class, 'index']);
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::put('/categories/{category}', [CategoryController::class, 'update']);
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
            // Admin + Manager
            Route::get('/rewards', [RewardController::class, 'index']);
            Route::post('/rewards', [RewardController::class, 'store']);
            // personal task
            Route::get('/personal-tasks', [PersonalTaskController::class, 'index']);
            Route::post('/personal-tasks', [PersonalTaskController::class, 'store']);
            Route::get('/personal-tasks/{task}', [PersonalTaskController::class, 'show']);
            Route::put('/personal-tasks/{task}', [PersonalTaskController::class, 'update']);
            Route::delete('/personal-tasks/{task}', [PersonalTaskController::class, 'destroy']);
            Route::post('/personal-tasks/{task}/status', [PersonalTaskController::class, 'updateStatus']);
            Route::get('/personal-tasks/filter', [PersonalTaskController::class, 'filter']);
            Route::get('/personal-tasks/count',[PersonalTaskController::class,'count']);
        });
    });
});
