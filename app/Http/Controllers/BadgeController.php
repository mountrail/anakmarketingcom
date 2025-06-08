<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    /**
     * Display the badge earned page
     */
    public function earned(Request $request)
    {
        $user = auth()->user();
        $badgeName = $request->query('badge');

        if (!$badgeName) {
            return redirect()->route('home');
        }

        // Get the specified badge
        $badge = Badge::where('name', $badgeName)->first();

        // If badge doesn't exist or user doesn't have it, redirect to home
        if (!$badge || !$user->hasBadge($badgeName)) {
            return redirect()->route('home');
        }

        return view('badges.badge-earned', [
            'badge' => $badge,
            'user' => $user
        ]);
    }
}
