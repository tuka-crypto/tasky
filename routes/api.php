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
        Route::get('/users/count', [AuthController::class, 'usersCount']);
        Route::put('/approve-user/{user}', [AuthController::class, 'approveUser']);
        Route::put('/reject-user/{user}', [AuthController::class, 'rejectUser']);
        Route::delete('/delete-user/{user}', [AuthController::class, 'deleteUser']);
    });
});
