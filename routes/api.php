<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\CurrentUserController;
use App\Http\Controllers\Api\DailyTaskController;
use App\Http\Controllers\Api\WeeklyGoalController;
use Illuminate\Support\Facades\Route;

Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class)->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', CurrentUserController::class);
    Route::post('/logout', LogoutController::class);

    Route::apiResource('daily-tasks', DailyTaskController::class)->except('show');
    Route::apiResource('weekly-goals', WeeklyGoalController::class)->except('show');
});
