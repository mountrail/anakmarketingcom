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
                return;
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
                }
            }
        } catch (\Exception $e) {
            Log::error("Error awarding 'Ikutan Nimbrung' badge to user {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * Check and award all applicable badges for existing users
     */
    public static function checkAllBadgesForUser(User $user)
    {
        self::checkBreakTheIce($user);
        self::checkIkutanNimbrung($user);
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
     * Get all user's badges for badge selection
     */
    public static function getAllUserBadges(User $user)
    {
        try {
            // Get badge IDs that the user has earned
            $earnedBadgeIds = $user->badges()->pluck('badges.id')->toArray();

            if (empty($earnedBadgeIds)) {
                return collect();
            }

            // Ensure all earned badges have corresponding UserProfileBadge entries
            foreach ($earnedBadgeIds as $badgeId) {
                UserProfileBadge::firstOrCreate([
                    'user_id' => $user->id,
                    'badge_id' => $badgeId,
                ], [
                    'is_displayed' => false,
                    'display_order' => null,
                ]);
            }

            // Return UserProfileBadge entries for earned badges
            return UserProfileBadge::where('user_id', $user->id)
                ->whereIn('badge_id', $earnedBadgeIds)
                ->with('badge')
                ->get();
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
