<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class FollowController extends Controller
{
    /**
     * Follow or unfollow a user.
     */
    public function toggle(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        // Prevent users from following themselves
        if ($currentUser->id === $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You cannot follow yourself'], 400);
            }
            return back()->with('error', 'You cannot follow yourself');
        }

        $isFollowing = $currentUser->isFollowing($user);

        if ($isFollowing) {
            $currentUser->unfollow($user);
            $action = 'unfollowed';
            $buttonText = 'Follow';
            $buttonClass = 'bg-branding-primary hover:bg-branding-primary/90';
        } else {
            $currentUser->follow($user);
            $action = 'followed';
            $buttonText = 'Following';
            $buttonClass = 'bg-gray-500 hover:bg-red-500 hover:text-white';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'action' => $action,
                'isFollowing' => !$isFollowing,
                'followersCount' => $user->followers()->count(),
                'buttonText' => $buttonText,
                'buttonClass' => $buttonClass,
                'message' => $action === 'followed'
                    ? "You are now following {$user->name}"
                    : "You have unfollowed {$user->name}"
            ]);
        }

        $message = $action === 'followed'
            ? "You are now following {$user->name}"
            : "You have unfollowed {$user->name}";

        return back()->with('success', $message);
    }

    /**
     * Get followers data for modal (AJAX).
     */
    public function getFollowersModal(User $user)
    {
        try {
            // Get the user IDs of followers
            $followerIds = $user->followers()->pluck('user_id');

            // Get the actual User models
            $followers = User::whereIn('id', $followerIds)
                ->select('id', 'name', 'profile_picture', 'avatar', 'job_title', 'company')
                ->latest()
                ->take(50)
                ->get();

            $followersData = $followers->map(function ($follower) {
                return [
                    'id' => $follower->id,
                    'name' => $follower->name,
                    'profile_image' => $follower->getProfileImageUrl(),
                    'job_title' => $follower->job_title,
                    'company' => $follower->company,
                    'profile_url' => route('profile.show', $follower),
                    'is_following' => auth()->check() ? auth()->user()->isFollowing($follower) : false,
                ];
            });

            return response()->json([
                'success' => true,
                'followers' => $followersData,
                'total' => $user->followers()->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load followers'
            ], 500);
        }
    }

    /**
     * Get following data for modal (AJAX).
     */
    public function getFollowingModal(User $user)
    {
        try {
            // Get the user IDs of people this user is following
            $followingIds = $user->followings()->pluck('followable_id');

            // Get the actual User models
            $following = User::whereIn('id', $followingIds)
                ->select('id', 'name', 'profile_picture', 'avatar', 'job_title', 'company')
                ->latest()
                ->take(50)
                ->get();

            $followingData = $following->map(function ($followingUser) {
                return [
                    'id' => $followingUser->id,
                    'name' => $followingUser->name,
                    'profile_image' => $followingUser->getProfileImageUrl(),
                    'job_title' => $followingUser->job_title,
                    'company' => $followingUser->company,
                    'profile_url' => route('profile.show', $followingUser),
                    'is_following' => auth()->check() ? auth()->user()->isFollowing($followingUser) : false,
                ];
            });

            return response()->json([
                'success' => true,
                'following' => $followingData,
                'total' => $user->followings()->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load following'
            ], 500);
        }
    }

    /**
     * Get follow suggestions for the current user.
     */
    public function suggestions()
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Get users that the current user is not following
        $followingIds = $currentUser->followings()->pluck('followable_id')->toArray();

        $suggestions = User::whereNotIn('id', $followingIds)
            ->where('id', '!=', $currentUser->id)
            ->select('id', 'name', 'profile_picture', 'avatar', 'job_title', 'company')
            ->withCount('followers')
            ->orderBy('followers_count', 'desc')
            ->take(5)
            ->get();

        return response()->json($suggestions);
    }
}
