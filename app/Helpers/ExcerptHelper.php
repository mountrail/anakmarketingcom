<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class ExcerptHelper
{
    /**
     * Generate a clean excerpt from HTML content
     *
     * @param string $content The HTML content
     * @param int $limit The character limit for the excerpt
     * @param string $end The ending string when content is truncated
     * @return string The clean excerpt
     */
    public static function clean($content, $limit = 100, $end = '...')
    {
        // First, completely strip all HTML tags to get pure text
        $plainText = strip_tags($content);

        // Trim whitespace and normalize spaces
        $plainText = preg_replace('/\s+/', ' ', trim($plainText));

        // Limit the text to the specified number of characters
        return Str::limit($plainText, $limit, $end);
    }

    /**
     * Generate an excerpt that preserves some basic formatting
     *
     * @param string $content The HTML content
     * @param int $limit The character limit for the excerpt
     * @param string $end The ending string when content is truncated
     * @return string The excerpt with basic formatting preserved
     */
    public static function preserveBasicFormatting($content, $limit = 100, $end = '...')
    {
        // Allow only very basic formatting tags
        $allowedTags = '<b><strong><i><em>';
        $text = strip_tags($content, $allowedTags);

        // Trim whitespace and normalize spaces
        $text = preg_replace('/\s+/', ' ', trim($text));

        // Limit the text to the specified number of characters
        // Note: This approach is simplistic and might cut in the middle of HTML tags
        $limitedText = Str::limit($text, $limit, $end);

        // Clean up potentially broken HTML tags after limiting
        return clean($limitedText);
    }
}
