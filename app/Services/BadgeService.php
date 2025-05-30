<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;
use App\Models\UserProfileBadge;
use App\Notifications\BadgeEarnedNotification;
use Illuminate\Support\Facades\Log;

class BadgeService
{
    /**
     * Award "Perkenalkan Saya" badge for completing basic profile
     */
    public static function checkPerkenalkanSaya(User $user)
    {
        try {
            // Check if user already has this badge
            if ($user->hasBadge('Perkenalkan Saya')) {
                return;
            }

            // Check if user has completed basic profile (name and job_title required)
            if (!empty($user->name) && !empty($user->job_title)) {
                $badge = Badge::where('name', 'Perkenalkan Saya')->first();
                if ($badge) {
                    // Award the badge
                    $user->giveBadge($badge);

                    // Create profile badge entry
                    UserProfileBadge::firstOrCreate([
                        'user_id' => $user->id,
                        'badge_id' => $badge->id,
                    ], [
                        'is_displayed' => false,
                        'display_order' => null,
                    ]);

                    // Send notification
                    $user->notify(new BadgeEarnedNotification($badge));

                    Log::info("Badge 'Perkenalkan Saya' awarded to user {$user->id}");

                    return true; // Return true to indicate badge was just awarded
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Error awarding 'Perkenalkan Saya' badge to user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Award "Break the Ice" badge for first post
     */
    public static function checkBreakTheIce(User $user)
    {
        try {
            // Check if user already has this badge
            if ($user->hasBadge('Break the Ice')) {
                return;
            }

            // Check if this is their first post or they have at least one post
            $postCount = $user->posts()->count();
            if ($postCount >= 1) {
                $badge = Badge::where('name', 'Break the Ice')->first();
                if ($badge) {
                    // Award the badge
                    $user->giveBadge($badge);

                    // Create profile badge entry
                    UserProfileBadge::firstOrCreate([
                        'user_id' => $user->id,
                        'badge_id' => $badge->id,
                    ], [
                        'is_displayed' => false,
                        'display_order' => null,
                    ]);

                    // Send notification
                    $user->notify(new BadgeEarnedNotification($badge));

                    Log::info("Badge 'Break the Ice' awarded to user {$user->id}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Error awarding 'Break the Ice' badge to user {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * Award "Ikutan Nimbrung" badge for first answer/comment
     */
    public static function checkIkutanNimbrung(User $user)
    {
        try {
            // Check if user already has this badge
            if ($user->hasBadge('Ikutan Nimbrung')) {
                return false;
            }

            // Check if this is their first answer or they have at least one answer
            $answerCount = $user->answers()->count();
            if ($answerCount >= 1) {
                $badge = Badge::where('name', 'Ikutan Nimbrung')->first();
                if ($badge) {
                    // Award the badge
                    $user->giveBadge($badge);

                    // Create profile badge entry
                    UserProfileBadge::firstOrCreate([
                        'user_id' => $user->id,
                        'badge_id' => $badge->id,
                    ], [
                        'is_displayed' => false,
                        'display_order' => null,
                    ]);

                    // Send notification
                    $user->notify(new BadgeEarnedNotification($badge));

                    Log::info("Badge 'Ikutan Nimbrung' awarded to user {$user->id}");

                    return true; // Return true to indicate badge was just awarded
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Error awarding 'Ikutan Nimbrung' badge to user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Award "Marketers Onboard!" badge for completing all onboarding missions
     */
    public static function checkMarketersOnboard(User $user)
    {
        try {
            // Check if user already has this badge
            if ($user->hasBadge('Marketers Onboard!')) {
                return false;
            }

            // Check if user has completed all onboarding missions
            $hasBasicProfile = !empty($user->name) && !empty($user->job_title);
            $hasAccessedNotifications = self::hasAccessedNotificationCenter($user);
            $hasFirstPost = $user->posts()->count() > 0;
            $hasFirstAnswer = $user->answers()->count() > 0;
            $hasFollowedUser = $user->followings()->count() > 0;

            // All missions must be completed
            if ($hasBasicProfile && $hasAccessedNotifications && $hasFirstPost && $hasFirstAnswer && $hasFollowedUser) {
                $badge = Badge::where('name', 'Marketers Onboard!')->first();
                if ($badge) {
                    // Award the badge
                    $user->giveBadge($badge);

                    // Create profile badge entry
                    UserProfileBadge::firstOrCreate([
                        'user_id' => $user->id,
                        'badge_id' => $badge->id,
                    ], [
                        'is_displayed' => false,
                        'display_order' => null,
                    ]);

                    // Send notification
                    $user->notify(new BadgeEarnedNotification($badge));

                    Log::info("Badge 'Marketers Onboard!' awarded to user {$user->id}");

                    return true; // Return true to indicate badge was just awarded
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Error awarding 'Marketers Onboard!' badge to user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user has accessed notification center
     */
    private static function hasAccessedNotificationCenter($user): bool
    {
        // Check if user has any notifications that have been marked as read
        // This indicates they've accessed the notification center
        if ($user->notifications()->whereNotNull('read_at')->exists()) {
            return true;
        }

        // Alternative: Check if user has the onboarding step marked as completed
        return $user->onboarding_steps &&
            in_array('accessed_notifications', json_decode($user->onboarding_steps, true) ?? []);
    }

    /**
     * Check and award all applicable badges for existing users
     */
    public static function checkAllBadgesForUser(User $user)
    {
        self::checkPerkenalkanSaya($user);
        self::checkBreakTheIce($user);
        self::checkIkutanNimbrung($user);
        self::checkMarketersOnboard($user);
    }

    /**
     * Retroactively award badges to all existing users
     */
    public static function awardRetroactiveBadges()
    {
        try {
            $users = User::all();

            foreach ($users as $user) {
                self::checkAllBadgesForUser($user);
            }

            Log::info("Retroactive badge awarding completed for all users");
        } catch (\Exception $e) {
            Log::error("Error in retroactive badge awarding: " . $e->getMessage());
        }
    }

    /**
     * Get user's displayed badges for profile
     */
    public static function getDisplayedBadges(User $user)
    {
        return UserProfileBadge::where('user_id', $user->id)
            ->where('is_displayed', true)
            ->with('badge')
            ->orderBy('display_order')
            ->take(3)
            ->get();
    }

    /**
     * Get all user's badges for badge selection with proper ordering
     * Selected badges appear first, then unselected badges ordered by newest earned first
     */
    public static function getAllUserBadges(User $user)
    {
        try {
            // Get badge IDs that the user has earned with their earned_at timestamps
            $earnedBadges = $user->badges()->withPivot('earned_at')->get();

            if ($earnedBadges->isEmpty()) {
                return collect();
            }

            $earnedBadgeIds = $earnedBadges->pluck('id')->toArray();

            // Ensure all earned badges have corresponding UserProfileBadge entries
            foreach ($earnedBadges as $badge) {
                UserProfileBadge::firstOrCreate([
                    'user_id' => $user->id,
                    'badge_id' => $badge->id,
                ], [
                    'is_displayed' => false,
                    'display_order' => null,
                ]);
            }

            // Get UserProfileBadge entries for earned badges
            $userProfileBadges = UserProfileBadge::where('user_id', $user->id)
                ->whereIn('badge_id', $earnedBadgeIds)
                ->with('badge')
                ->get();

            // Create a mapping of badge_id to earned_at timestamp
            $earnedAtMap = $earnedBadges->keyBy('id')->map(function ($badge) {
                return $badge->pivot->earned_at;
            });

            // Sort badges: selected first (by display_order), then unselected by newest earned first
            return $userProfileBadges->sort(function ($a, $b) use ($earnedAtMap) {
                // If both are displayed, sort by display_order
                if ($a->is_displayed && $b->is_displayed) {
                    return $a->display_order <=> $b->display_order;
                }

                // If only one is displayed, displayed comes first
                if ($a->is_displayed && !$b->is_displayed) {
                    return -1;
                }
                if (!$a->is_displayed && $b->is_displayed) {
                    return 1;
                }

                // If neither is displayed, sort by newest earned first
                $aEarnedAt = $earnedAtMap[$a->badge_id];
                $bEarnedAt = $earnedAtMap[$b->badge_id];

                return $bEarnedAt <=> $aEarnedAt; // Descending order (newest first)
            })->values();

        } catch (\Exception $e) {
            Log::error("Error getting user badges for user {$user->id}: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Update user's displayed badges
     */
    public static function updateDisplayedBadges(User $user, array $badgeIds)
    {
        try {
            // Limit to 3 badges maximum
            $badgeIds = array_slice($badgeIds, 0, 3);

            // Verify user actually has these badges
            $earnedBadgeIds = $user->badges()->pluck('badges.id')->toArray();
            $validBadgeIds = array_intersect($badgeIds, $earnedBadgeIds);

            // First, set all badges to not displayed
            UserProfileBadge::where('user_id', $user->id)
                ->update(['is_displayed' => false, 'display_order' => null]);

            // Then set selected badges as displayed with order
            foreach ($validBadgeIds as $index => $badgeId) {
                UserProfileBadge::where('user_id', $user->id)
                    ->where('badge_id', $badgeId)
                    ->update([
                        'is_displayed' => true,
                        'display_order' => $index + 1
                    ]);
            }

            Log::info("Updated displayed badges for user {$user->id}");
        } catch (\Exception $e) {
            Log::error("Error updating displayed badges for user {$user->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
