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
                'description' => 'Lengkapi profil dasar Anda untuk memperkenalkan diri',
                'icon' => 'perkenalkan-saya.png',
                'level' => 1,
            ],
            [
                'name' => 'Break the Ice',
                'description' => 'Posting pertanyaan atau diskusi pertama Anda',
                'icon' => 'break-the-ice.png',
                'level' => 1,
            ],
            [
                'name' => 'Ikutan Nimbrung',
                'description' => 'Posting komentar/jawaban pertama Anda',
                'icon' => 'ikutan-nimbrung.png',
                'level' => 1,
            ],
            [
                'name' => 'Marketers Onboard!',
                'description' => 'Selesaikan semua misi onboarding dan resmi bergabung dengan komunitas marketing',
                'icon' => 'marketers-onboard.png',
                'level' => 2,
            ],
            [
                'name' => 'Founding Users',
                'description' => 'Salah satu dari 50 anggota pertama yang menyelesaikan onboarding dan bergabung dengan komunitas',
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

