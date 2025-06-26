<?php

namespace Database\Seeders;

use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class WebsiteSettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            // General Settings
            ['key' => 'site_name', 'value' => 'anakmarketing', 'type' => 'text', 'group' => 'general'],
            ['key' => 'site_tagline', 'value' => 'Learn Marketing, Do Marketing', 'type' => 'text', 'group' => 'general'],
            ['key' => 'site_description', 'value' => 'A community-driven Q&A platform', 'type' => 'textarea', 'group' => 'general'],

            // SEO Settings
            ['key' => 'default_meta_description', 'value' => 'Join our community to ask questions and share knowledge', 'type' => 'textarea', 'group' => 'seo'],
            ['key' => 'default_meta_keywords', 'value' => 'questions, answers, community, knowledge', 'type' => 'text', 'group' => 'seo'],

            // Contact Settings
            ['key' => 'contact_email', 'value' => 'tech@demandgenlab.com', 'type' => 'email', 'group' => 'contact'],
            ['key' => 'support_email', 'value' => 'tech@demandgenlab.com', 'type' => 'email', 'group' => 'contact'],

            // Social Media
            ['key' => 'facebook_url', 'value' => '', 'type' => 'url', 'group' => 'social'],
            ['key' => 'twitter_url', 'value' => '', 'type' => 'url', 'group' => 'social'],
            ['key' => 'linkedin_url', 'value' => '', 'type' => 'url', 'group' => 'social'],
        ];

        foreach ($settings as $setting) {
            WebsiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
