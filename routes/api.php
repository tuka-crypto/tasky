<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Projectcontroller;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;
use app\Http\Controllers\TaskController;
use App\Http\Controllers\UserSettingsController;

//Authintication and password
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/signin', [AuthController::class, 'signin']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/forgot-password', [AuthController::class, 'sendResetCode']);
Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::middleware(['auth:sanctum','local'])->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/user/language', [UserSettingsController::class, 'updateLanguage']);
    Route::post('/user/theme', [UserSettingsController::class, 'updateTheme']);
    Route::post('/user/fcm-token', [UserSettingsController::class, 'updateFcmToken']);
    /*
        admin
     */
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        //users
        Route::get('/pending-users', [AuthController::class, 'pendingUsers']);
        Route::get('/users/count', [AuthController::class, 'usersCount']);
        Route::put('/approve-user/{user}', [AuthController::class, 'approveUser']);
        Route::put('/reject-user/{user}', [AuthController::class, 'rejectUser']);
        Route::delete('/delete-user/{user}', [AuthController::class, 'deleteUser']);
        //projects
        Route::get('/projects', [Projectcontroller::class, 'index']);
        Route::get('/projects-count', [Projectcontroller::class, 'projectsCount']);
        //teams
        Route::get('/teams', [TeamController::class, 'index']);
    });
    /*
        manager
     */
        Route::middleware(['auth:sanctum', 'manager'])->group(function () {
        //projects
        Route::post('/projects', [Projectcontroller::class, 'store']);
        Route::put('/projects/{project}', [Projectcontroller::class, 'update']);
        Route::delete('/projects/{project}', [Projectcontroller::class, 'destroy']);
        Route::get('/my-projects', [ProjectController::class, 'myprojects']);
        //teams
        Route::get('/my-teams', [ProjectController::class, 'myteams']);
        Route::post('/teams', [TeamController::class, 'store']);
        Route::put('/teams/{team}', [TeamController::class, 'update']);
        Route::delete('/teams/{team}', [TeamController::class, 'destroy']);
        Route::post('/teams/{team}/add-member', [TeamController::class, 'addMember']);
        Route::post('/teams/{team}/remove-member', [TeamController::class, 'removeMember']);
        //tasks
        Route::get('/projects/{project}/tasks', [TaskController::class, 'projectTasks']);
        Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
        Route::put('/tasks/{task}', [TaskController::class, 'update']);
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
        Route::post('/tasks/{task}/assign-members', [TaskController::class, 'assignMembers']);
        Route::post('/tasks/{task}/add-tag', [TaskController::class, 'addTag']);
        Route::post('/tasks/{task}/add-dependency', [TaskController::class, 'addDependency']);
        //dashboard and reports
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('/dashboard/progress', [DashboardController::class, 'userProgress']); 
        Route::get('/reports/user/{user}', [ReportsController::class, 'userReport']);
        Route::get('/reports/users-performance', [ReportsController::class, 'allUsersPerformance']);
    });
    /*
        member
     */
    Route::middleware(['auth:sanctum','member'])->group(function(){
        //invitations for teams
        Route::post('/teams/{team}/accept', [TeamController::class, 'acceptInvitation']);
        Route::post('/teams/{team}/reject', [TeamController::class, 'rejectInvitation']);
        //tasks
        Route::get('/my-tasks', [TaskController::class, 'myTasks']);
        Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    });
    Route::middleware(['auth:sanctum'])->group(function(){
        //projects
        Route::get('/projects/{id}', [Projectcontroller::class, 'show']);
        Route::get('/projects/search', [ProjectController::class, 'search']);
        //teams
        Route::get('/teams/{team}', [TeamController::class, 'show']);
        //tasks
        Route::get('/tasks/{task}', [TaskController::class, 'show']);
        Route::post('/tasks/{task}/attach-file', [TaskController::class, 'attachFile']);
    });
});
});
