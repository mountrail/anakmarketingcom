<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reformat existing phone numbers to include a space between country code and number
        $users = User::whereNotNull('phone')->get();
        
        foreach ($users as $user) {
            if (strpos($user->phone, ' ') === false && strlen($user->phone) > 1) {
                // If no space exists, determine country code
                $phone = ltrim($user->phone, '+');
                
                // Common approach: First look for 1-3 digit country codes
                // This is a basic approach - for production, consider using a library like libphonenumber
                if (preg_match('/^(\d{1,3})(\d+)$/', $phone, $matches)) {
                    $countryCode = $matches[1];
                    $phoneNumber = $matches[2];
                    
                    // Update with space
                    $user->phone = '+' . $countryCode . ' ' . $phoneNumber;
                    $user->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // If needed, remove spaces from phone numbers
        $users = User::whereNotNull('phone')->get();
        
        foreach ($users as $user) {
            $user->phone = str_replace(' ', '', $user->phone);
            $user->save();
        }
    }
};