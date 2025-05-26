<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\TinyMCEUploadController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\NotificationController;

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

    // Follow/Unfollow routes
    Route::post('/follow/{user}', [FollowController::class, 'toggle'])->name('follow.toggle');

    // Follow modal routes (AJAX) - matching the JavaScript URLs
    Route::get('/follow/{user}/followers', [FollowController::class, 'getFollowersModal'])->name('follow.followers-modal');
    Route::get('/follow/{user}/following', [FollowController::class, 'getFollowingModal'])->name('follow.following-modal');

    // Follow suggestions
    Route::get('/follow/suggestions', [FollowController::class, 'suggestions'])->name('follow.suggestions');

    Route::get('/account', [ProfileController::class, 'edit'])->name('account.edit');
    Route::patch('/account', [ProfileController::class, 'update'])->name('account.update');
    Route::delete('/account', [ProfileController::class, 'destroy'])->name('account.destroy');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Protected profile update routes
    Route::post('/profile/update-profile-picture', [ProfileController::class, 'updateProfilePicture'])
        ->name('profile.update-profile-picture');
    Route::patch('/profile/update-profile', [ProfileController::class, 'updateProfile'])->name('profile.update-profile');
    Route::patch('/profile/update-basic-info', [ProfileController::class, 'updateBasicInfo'])->name('profile.update-basic-info');
    Route::patch('/profile/update-bio', [ProfileController::class, 'updateBio'])->name('profile.update-bio');
    Route::patch('/profile/badges', [ProfileController::class, 'updateBadges'])->name('profile.update-badges');

    // Onboarding route - ADD THIS NEW ROUTE
    Route::get('/onboarding', function () {
        return view('onboarding.index');
    })->name('onboarding.index');
});

// Post route for viewing individual posts - MOVED AFTER protected routes
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/profile/{user}/posts', [PostController::class, 'loadUserPosts'])->name('profile.load-posts');

// Include authentication routes
require __DIR__ . '/auth.php';
