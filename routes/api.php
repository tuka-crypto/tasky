<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Projectcontroller;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/signin', [AuthController::class, 'signin']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/forgot-password', [AuthController::class, 'sendResetCode']);
Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Admin only
    Route::middleware('admin')->group(function () {
        Route::get('/pending-users', [AuthController::class, 'pendingUsers']);
        Route::get('/users/count', [AuthController::class, 'usersCount']);
        Route::put('/approve-user/{user}', [AuthController::class, 'approveUser']);
        Route::put('/reject-user/{user}', [AuthController::class, 'rejectUser']);
        Route::delete('/delete-user/{user}', [AuthController::class, 'deleteUser']);
        Route::get('/projects', [Projectcontroller::class, 'index']);
        Route::get('/projects-count', [Projectcontroller::class, 'projectsCount']);
        Route::get('/teams', [TeamController::class, 'index']);
    });
    Route::middleware('manager')->group(function () {
        Route::post('/projects', [Projectcontroller::class, 'store']);
        Route::put('/projects/{project}', [Projectcontroller::class, 'update']);
        Route::delete('/projects/{project}', [Projectcontroller::class, 'destroy']);
        Route::get('/my-projects', [ProjectController::class, 'myprojects']);
        Route::get('/my-teams', [ProjectController::class, 'myteams']);
        Route::post('/teams', [TeamController::class, 'store']);
        Route::put('/teams/{team}', [TeamController::class, 'update']);
        Route::delete('/teams/{team}', [TeamController::class, 'destroy']);
        Route::post('/teams/{team}/add-member', [TeamController::class, 'addMember']);
        Route::post('/teams/{team}/remove-member', [TeamController::class, 'removeMember']);
    });
    Route::get('/projects/{id}', [Projectcontroller::class, 'show']);
    Route::get('/projects/search', [ProjectController::class, 'search']);
    Route::get('/teams/{team}', [TeamController::class, 'show']);
    Route::middleware('member')->group(function(){
        Route::post('/teams/{team}/accept', [TeamController::class, 'acceptInvitation']);
        Route::post('/teams/{team}/reject', [TeamController::class, 'rejectInvitation']);
    });
});
