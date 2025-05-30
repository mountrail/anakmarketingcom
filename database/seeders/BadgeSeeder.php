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
                'icon' => 'user-circle',
                'level' => 1,
            ],
            [
                'name' => 'Break the Ice',
                'description' => 'Post your first question or discussion',
                'icon' => 'ice-cube',
                'level' => 1,
            ],
            [
                'name' => 'Ikutan Nimbrung',
                'description' => 'Post your first comment/answer',
                'icon' => 'chat-bubble',
                'level' => 1,
            ],
            [
                'name' => 'Marketers Onboard!',
                'description' => 'Complete all onboarding missions and officially join the marketing community',
                'icon' => 'rocket',
                'level' => 2,
            ]
        ];

        foreach ($badges as $badgeData) {
            // Check if badge already exists
            $existingBadge = Badge::where('name', $badgeData['name'])->first();

            if (!$existingBadge) {
                Badge::create($badgeData);
                $this->command->info("Created badge: {$badgeData['name']}");
            } else {
                $this->command->info("Badge already exists: {$badgeData['name']}");
            }
        }

        $this->command->info('Badge seeding completed!');
    }
}
