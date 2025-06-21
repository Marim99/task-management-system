<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/my-tasks', [TaskController::class, 'myTasks']); // Only tasks assigned to me
    Route::put('/tasks/{id}/status', [TaskController::class, 'updateStatus']); // Update my assigned task's status

    Route::get('/tasks/{id}', [TaskController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'role:manager'])->prefix('tasks')->group(function () {
    Route::post('/', [TaskController::class, 'store']);
    Route::post('/filteredList', [TaskController::class, 'filteredList']);
    Route::put('/{id}', [TaskController::class, 'update']);
    Route::delete('/{id}', [TaskController::class, 'destroy']);

    Route::post('/{id}/assign', [TaskController::class, 'assign']);

    Route::post('/{id}/dependencies', [TaskController::class, 'setDependencies']);
});