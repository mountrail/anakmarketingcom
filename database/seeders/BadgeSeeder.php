<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Perkenalkan Saya',
                'description' => 'Complete your basic profile to introduce yourself',
                'icon' => 'perkenalkan-saya.png',
                'level' => 1,
            ],
            [
                'name' => 'Break the Ice',
                'description' => 'Post your first question or discussion',
                'icon' => 'break-the-ice.png',
                'level' => 1,
            ],
            [
                'name' => 'Ikutan Nimbrung',
                'description' => 'Post your first comment/answer',
                'icon' => 'ikutan-nimbrung.png',
                'level' => 1,
            ],
            [
                'name' => 'Marketers Onboard!',
                'description' => 'Complete all onboarding missions and officially join the marketing community',
                'icon' => 'marketers-onboard.png',
                'level' => 2,
            ],
            [
                'name' => 'Founding Users',
                'description' => 'One of the first 50 members to complete onboarding and join the community',
                'icon' => 'founding-users.png',
                'level' => 3,
            ]
        ];

        foreach ($badges as $badgeData) {
            // Use updateOrCreate to update existing badges or create new ones
            $badge = Badge::updateOrCreate(
                ['name' => $badgeData['name']], // Find by name
                $badgeData // Update with all data
            );

            $this->command->info("Updated/Created badge: {$badgeData['name']}");
        }

        $this->command->info('Badge seeding completed!');
    }
}
