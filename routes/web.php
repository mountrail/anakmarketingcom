<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\TinyMCEUploadController;


// Main posts listing route - accessible to all users (replaces old home route)
Route::get('/', [PostController::class, 'index'])->name('home');
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

// tinymce
Route::middleware(['auth'])->group(function () {
    Route::post('/admin/upload-tinymce-image', [TinyMCEUploadController::class, 'store'])->name('tinymce.upload');
});


// Google login routes
Route::controller(GoogleController::class)->group(function () {
    Route::get('auth/google', 'redirectToGoogle')->name('auth.google');
    Route::get('auth/google/callback', 'handleGoogleCallback')->name('auth.google.callback');
});


// Protected post routes (require authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    // Protected post routes - Only create and store (no edit or delete)
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
});

// Post route for viewing individual posts - accessible to all users
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

// User profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Answer routes
Route::middleware(['auth'])->group(function () {
    Route::post('/posts/{post}/answers', [App\Http\Controllers\AnswerController::class, 'store'])->name('posts.answers.store');
    Route::patch('/answers/{answer}/toggle-editors-pick', [App\Http\Controllers\AnswerController::class, 'toggleEditorsPick'])->name('answers.toggle-editors-pick');

    // Voting routes
    Route::post('/posts/{post}/vote', [VoteController::class, 'votePost'])->name('posts.vote');
    Route::post('/answers/{answer}/vote', [VoteController::class, 'voteAnswer'])->name('answers.vote');


});

// Include authentication routes
require __DIR__ . '/auth.php';
