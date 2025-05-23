<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\TinyMCEUploadController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\FollowController;

// Main posts listing route - accessible to all users (replaces old home route)
Route::get('/', [PostController::class, 'index'])->name('home');
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

// Google login routes
Route::controller(GoogleController::class)->group(function () {
    Route::get('auth/google', 'redirectToGoogle')->name('auth.google');
    Route::get('auth/google/callback', 'handleGoogleCallback')->name('auth.google.callback');
});

// Protected post routes (user must be authenticated) - MOVED ABOVE show route
Route::middleware(['auth'])->group(function () {
    // Post CRUD operations - CREATE ROUTE FIRST
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Editor's pick toggle for posts
    Route::post('/posts/{post}/toggle-featured', [PostController::class, 'toggleFeatured'])->name('posts.toggle-featured');

    // Answer routes
    Route::post('/posts/{post}/answers', [AnswerController::class, 'store'])->name('posts.answers.store');
    Route::patch('/answers/{answer}/toggle-editors-pick', [AnswerController::class, 'toggleEditorsPick'])->name('answers.toggle-editors-pick');
    Route::delete('/answers/{answer}', [AnswerController::class, 'destroy'])->name('answers.destroy');

    // Voting routes
    Route::post('/posts/{post}/vote', [VoteController::class, 'votePost'])->name('posts.vote');
    Route::post('/answers/{answer}/vote', [VoteController::class, 'voteAnswer'])->name('answers.vote');

    // TinyMCE upload route
    Route::post('/tinymce/upload', [TinyMCEUploadController::class, 'store'])->name('tinymce.upload');

    // Toggle follow/unfollow
    Route::post('/follow/{user}', [FollowController::class, 'toggle'])->name('follow.toggle');

    // Protected profile update routes
    Route::patch('/profile/update-profile', [ProfileController::class, 'updateProfile'])->name('profile.update-profile');
    Route::patch('/profile/update-basic-info', [ProfileController::class, 'updateBasicInfo'])->name('profile.update-basic-info');
    Route::patch('/profile/update-bio', [ProfileController::class, 'updateBio'])->name('profile.update-bio');
});

// Post route for viewing individual posts - MOVED AFTER protected routes
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/profile/{user}/posts', [PostController::class, 'loadUserPosts'])->name('profile.load-posts');

// User account routes
Route::middleware(['auth'])->group(function () {
    Route::get('/account', [ProfileController::class, 'edit'])->name('account.edit');
    Route::patch('/account', [ProfileController::class, 'update'])->name('account.update');
    Route::delete('/account', [ProfileController::class, 'destroy'])->name('account.destroy');
});

// Include authentication routes
require __DIR__ . '/auth.php';
