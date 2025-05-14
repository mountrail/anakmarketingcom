<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;

// Admin routes - protected by admin role middleware
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // User management
    Route::resource('users', UserController::class);

    // Role management
    Route::get('/users/{user}/roles', [UserController::class, 'editRoles'])->name('users.roles');
    Route::post('/users/{user}/roles', [UserController::class, 'updateRoles'])->name('users.roles.update');
});

// Editor routes - protected by editor or admin role middleware
Route::middleware(['auth', 'verified', 'role:editor|admin'])->prefix('editor')->name('editor.')->group(function () {
    // Editor dashboard
    Route::get('/', [App\Http\Controllers\Editor\DashboardController::class, 'index'])->name('dashboard');

    // Editor's picks management
    Route::get('/editors-picks', [App\Http\Controllers\Editor\EditorPickController::class, 'index'])->name('editors-picks');
});
