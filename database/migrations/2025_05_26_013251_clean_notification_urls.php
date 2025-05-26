<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean up absolute URLs in notifications data
        $this->cleanupNotificationUrls();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as we're cleaning up data
        // If you need to rollback, you would need to manually restore the data
    }

    /**
     * Clean up notification URLs to make them relative
     */
    private function cleanupNotificationUrls(): void
    {
        // Get all notifications that have action_url in their data
        $notifications = DB::table('notifications')
            ->whereRaw("JSON_EXTRACT(data, '$.action_url') IS NOT NULL")
            ->get();

        foreach ($notifications as $notification) {
            $data = json_decode($notification->data, true);

            if (isset($data['action_url'])) {
                $actionUrl = $data['action_url'];

                // Convert absolute URLs to relative URLs
                $relativeUrl = $this->convertToRelativeUrl($actionUrl);

                // Update the data if the URL was changed
                if ($relativeUrl !== $actionUrl) {
                    $data['action_url'] = $relativeUrl;

                    DB::table('notifications')
                        ->where('id', $notification->id)
                        ->update(['data' => json_encode($data)]);
                }
            }
        }
    }

    /**
     * Convert absolute URL to relative URL
     */
    private function convertToRelativeUrl(string $url): string
    {
        if (empty($url)) {
            return $url;
        }

        // If it's already a relative URL, return as is
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            return $url;
        }

        // Parse URL and extract path, query, and fragment
        $parsed = parse_url($url);

        if (!$parsed || !isset($parsed['path'])) {
            return $url; // Return original if parsing fails
        }

        $relative = $parsed['path'];

        if (!empty($parsed['query'])) {
            $relative .= '?' . $parsed['query'];
        }

        if (!empty($parsed['fragment'])) {
            $relative .= '#' . $parsed['fragment'];
        }

        return $relative;
    }
};
