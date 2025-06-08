<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;

// Home route - defaults to questions
Route::get('/', [PostController::class, 'index'])->name('home');
Route::get('/home', function () {
    return redirect()->route('home');
});
// Specific type routes
Route::get('/pertanyaan', [PostController::class, 'index'])->defaults('type', 'question')->name('posts.questions');
Route::get('/diskusi', [PostController::class, 'index'])->defaults('type', 'discussion')->name('posts.discussions');

// Keep the old routes for backward compatibility (redirect to new ones)
Route::get('/posts', function (Request $request) {
    $type = $request->get('type', 'question');
    if ($type === 'discussion') {
        return redirect()->route('posts.discussions');
    }
    return redirect()->route('posts.questions');
})->name('posts.index');

// Profile redirect route - redirect to current user's profile if authenticated
Route::get('/profile', function () {
    if (auth()->check()) {
        return redirect()->route('profile.show', auth()->user()->id);
    }
    return redirect()->route('login');
})->name('profile.redirect');

// IMPORTANT: Specific routes MUST come before dynamic slug routes
Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/profile/{user}/posts', [PostController::class, 'loadUserPosts'])->name('profile.load-posts');

// Google login routes
Route::controller(GoogleController::class)->group(function () {
    Route::get('auth/google', 'redirectToGoogle')->name('auth.google');
    Route::get('auth/google/callback', 'handleGoogleCallback')->name('auth.google.callback');
});

// Onboarding routes - Only require auth and verified, NOT onboarding.complete
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/onboarding/welcome', [OnboardingController::class, 'welcome'])->name('onboarding.welcome');
    Route::get('/onboarding/checklist', [OnboardingController::class, 'checklist'])->name('onboarding.checklist');
    Route::get('/onboarding/basic-profile', [OnboardingController::class, 'basicProfile'])->name('onboarding.basic-profile');
    Route::post('/onboarding/basic-profile', [OnboardingController::class, 'updateBasicProfile'])->name('onboarding.update-basic-profile');
    Route::get('/onboarding/badge-earned', [OnboardingController::class, 'badgeEarned'])->name('onboarding.badge-earned');
    Route::get('/onboarding/discussion-list', [OnboardingController::class, 'discussionList'])->name('onboarding.discussion-list');
    Route::get('/onboarding/first-post', [OnboardingController::class, 'firstPost'])->name('onboarding.first-post');
    Route::get('/onboarding/follow-users', [OnboardingController::class, 'followUsers'])->name('onboarding.follow-users');

    // Add this new route for claiming the final badge
    Route::post('/onboarding/claim-badge', [OnboardingController::class, 'claimBadge'])->name('onboarding.claim-badge');

    // Add this route with your other onboarding routes
    Route::get('/onboarding/founding-users-badge', [OnboardingController::class, 'showFoundingUsersBadge'])->name('onboarding.founding-users-badge');
});

// Protected routes that require completed onboarding
Route::middleware(['auth', 'verified', \App\Http\Middleware\EnsureOnboardingComplete::class])->group(function () {
    // SPECIFIC POST ROUTES FIRST - These must come before the dynamic slug route
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::post('/posts/{post}/increment-view', [PostController::class, 'incrementView'])->name('posts.increment-view');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Editor's pick toggle for posts
    Route::post('/posts/{post}/toggle-featured', [PostController::class, 'toggleFeatured'])->name('posts.toggle-featured');

    // Answer routes - USING ID FOR FORM SUBMISSIONS
    Route::post('/posts/{post}/answers', [AnswerController::class, 'store'])->name('posts.answers.store');
    Route::patch('/answers/{answer}', [AnswerController::class, 'update'])->name('answers.update');
    Route::patch('/answers/{answer}/toggle-editors-pick', [AnswerController::class, 'toggleEditorsPick'])->name('answers.toggle-editors-pick');
    Route::delete('/answers/{answer}', [AnswerController::class, 'destroy'])->name('answers.destroy');

    // Update your voting routes to use post ID
    Route::post('/posts/{post}/vote', [VoteController::class, 'votePost'])->name('posts.vote');
    Route::post('/answers/{answer}/vote', [VoteController::class, 'voteAnswer'])->name('answers.vote');

    // Main image upload route
    Route::post('/image/upload', [ImageUploadController::class, 'upload'])->name('image.upload');

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
    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAll'])->name('notifications.delete-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');

    // AJAX endpoints for notifications
    Route::patch('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');

    // NEW: Profile edit page route
    Route::get('/profile-edit', [ProfileController::class, 'editProfile'])->name('profile.edit-profile');

    // Protected profile update routes
    Route::post('/profile/update-profile-picture', [ProfileController::class, 'updateProfilePicture'])
        ->name('profile.update-profile-picture');
    Route::patch('/profile/update-profile', [ProfileController::class, 'updateProfile'])->name('profile.update-profile');
    Route::patch('/profile/update-basic-info', [ProfileController::class, 'updateBasicInfo'])->name('profile.update-basic-info');
    Route::patch('/profile/update-bio', [ProfileController::class, 'updateBio'])->name('profile.update-bio');
    Route::patch('/profile/badges', [ProfileController::class, 'updateBadges'])->name('profile.update-badges');
});

// DYNAMIC SLUG ROUTE MUST COME LAST
// This catches any remaining /posts/{anything} patterns including new format: user_id/title-id
Route::get('/posts/{slug}', [PostController::class, 'show'])->name('posts.show')->where('slug', '.*');

// Include authentication routes
require __DIR__ . '/auth.php';
