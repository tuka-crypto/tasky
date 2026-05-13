<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
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
        Route::put('/approve-user/{user}', [AuthController::class, 'approveUser']);
        Route::put('/reject-user/{user}', [AuthController::class, 'rejectUser']);
        Route::delete('/delete-user/{user}', [AuthController::class, 'deleteUser']);
    });
});
Route::middleware(['auth:sanctum'])->group(function () {
    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::get('/tasks', [TaskController::class, 'index']);          // show all tasks
        Route::post('/tasks', [TaskController::class, 'store']);         // create task
        Route::get('/tasks/{task}', [TaskController::class, 'show']);    // show one task
        Route::put('/tasks/{task}', [TaskController::class, 'update']);  // update task
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']); // delete task
    });
    // Member routes
    Route::middleware('member')->group(function () {
        Route::get('/my-tasks', [TaskController::class, 'myTasks']);              // show all his tasks
        Route::get('/my-tasks/{task}', [TaskController::class, 'myTask']);        // show one of his tasks
        Route::put('/my-tasks/{task}/status', [TaskController::class, 'updateStatus']); // update status
    });
});

