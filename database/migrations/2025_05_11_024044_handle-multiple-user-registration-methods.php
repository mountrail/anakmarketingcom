<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, ensure all email_verified_at fields are filled
        DB::statement("UPDATE users SET email_verified_at = NOW() WHERE email_verified_at IS NULL");

        // Get all emails that appear more than once
        $duplicateEmails = DB::table('users')
            ->select('email')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('email');

        foreach ($duplicateEmails as $email) {
            // Find all users with this email
            $users = User::where('email', $email)->get();

            // Find if there's a Google user among them
            $googleUser = $users->firstWhere('provider', 'google');
            $regularUser = $users->firstWhere('provider', null);

            // If we have both types of users, merge their data
            if ($googleUser && $regularUser) {
                // Update Google user with data from regular user
                // We prioritize keeping the Google user and enhancing it with form data
                $googleUser->update([
                    'phone' => $regularUser->phone ?? $googleUser->phone,
                    'industry' => $regularUser->industry ?? $googleUser->industry,
                    'seniority' => $regularUser->seniority ?? $googleUser->seniority,
                    'company_size' => $regularUser->company_size ?? $googleUser->company_size,
                    'city' => $regularUser->city ?? $googleUser->city,
                    // Keep Google auth data intact
                ]);

                // If needed, transfer other data from regular user to Google user here
                // e.g., posts, votes, etc. using relationships

                // Delete the regular user
                $regularUser->delete();

                // Log the merge
                Log::info("Merged accounts for email: $email. Kept Google account ID: {$googleUser->id}, deleted regular account ID: {$regularUser->id}");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be reversed since it deletes data
    }
};
