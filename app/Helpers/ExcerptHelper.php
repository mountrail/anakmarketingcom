<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use DOMDocument;
use DOMXPath;

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
        return $limitedText;
    }

    /**
     * Generate an excerpt that preserves line structure up to specified number of lines
     *
     * @param string $content The TinyMCE HTML content
     * @param int $maxLines Maximum number of lines to include (default: 3)
     * @param string $end The ending string when content is truncated
     * @return string The excerpt with line structure preserved
     */
    public static function preserveLines($content, $maxLines = 3, $end = '...')
    {
        if (empty($content)) {
            return '';
        }

        // Clean up the content first
        $content = trim($content);

        // Handle empty content
        if (empty($content)) {
            return '';
        }

        // Simple approach: split content by block elements and take first N elements
        $lines = [];
        $lineCount = 0;

        // Split by paragraph and div tags, but keep the tags
        $parts = preg_split('/(<\/(?:p|div|h[1-6]|li|blockquote|pre)>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

        $currentLine = '';

        for ($i = 0; $i < count($parts); $i++) {
            if ($lineCount >= $maxLines) {
                break;
            }

            $part = $parts[$i];
            $currentLine .= $part;

            // If this part is a closing tag, we've completed a line
            if (preg_match('/^<\/(?:p|div|h[1-6]|li|blockquote|pre)>$/i', $part)) {
                $textContent = trim(strip_tags($currentLine));

                // Only add non-empty lines
                if (!empty($textContent)) {
                    $lines[] = trim($currentLine);
                    $lineCount++;
                }

                $currentLine = '';
            }
        }

        // Handle remaining content if it doesn't end with a block element
        if (!empty($currentLine) && $lineCount < $maxLines) {
            $textContent = trim(strip_tags($currentLine));
            if (!empty($textContent)) {
                // Wrap in paragraph if it's not already wrapped
                if (!preg_match('/^<(?:p|div|h[1-6]|li|blockquote|pre)/i', trim($currentLine))) {
                    $currentLine = '<p>' . trim($currentLine) . '</p>';
                }
                $lines[] = $currentLine;
                $lineCount++;
            }
        }

        // If no proper block elements found, fallback to simple splitting
        if (empty($lines)) {
            return self::fallbackLineExtraction($content, $maxLines, $end);
        }

        // Join the lines
        $result = implode('', $lines);

        // Clean up any broken HTML
        $result = self::cleanupBrokenHtml($result);

        // Add ellipsis if we have more content
        $hasMoreContent = $lineCount >= $maxLines && strlen(strip_tags($content)) > strlen(strip_tags($result));

        if ($hasMoreContent) {
            // Find the last closing tag and insert ellipsis before it
            $result = preg_replace('/(<\/[^>]+>)(\s*)$/', ' ' . $end . '$1', $result);
            if (!preg_match('/(<\/[^>]+>)$/', $result)) {
                $result .= $end;
            }
        }

        return $result;
    }

    /**
     * Clean up broken HTML tags and structure
     *
     * @param string $html
     * @return string
     */
    private static function cleanupBrokenHtml($html)
    {
        // Remove any unclosed or broken tags at the end
        $html = preg_replace('/<[^>]*$/', '', $html);

        // Remove empty tags
        $html = preg_replace('/<([^>]+)>\s*<\/\1>/', '', $html);

        // Clean up multiple spaces
        $html = preg_replace('/\s+/', ' ', $html);

        return trim($html);
    }

    /**
     * Fallback method for line extraction when DOM parsing fails
     *
     * @param string $content
     * @param int $maxLines
     * @param string $end
     * @return string
     */
    private static function fallbackLineExtraction($content, $maxLines, $end)
    {
        // Convert BR tags to line breaks and split by paragraph/div tags
        $content = preg_replace('/<br\s*\/?>/i', "\n", $content);

        // Split by block elements
        $blocks = preg_split('/(<\/?(?:p|div|h[1-6]|li|blockquote|pre)[^>]*>)/i', $content, -1, PREG_SPLIT_NO_EMPTY);

        $lines = [];
        $lineCount = 0;

        foreach ($blocks as $block) {
            if ($lineCount >= $maxLines) {
                break;
            }

            $block = trim($block);

            // Skip empty blocks and HTML tags
            if (empty($block) || preg_match('/^<\/?[^>]+>$/', $block)) {
                continue;
            }

            // Clean the text
            $cleanText = trim(strip_tags($block));

            if (!empty($cleanText)) {
                // Split by line breaks if multiple lines in one block
                $subLines = explode("\n", $cleanText);

                foreach ($subLines as $subLine) {
                    $subLine = trim($subLine);

                    if (!empty($subLine) && $lineCount < $maxLines) {
                        $lines[] = '<p>' . htmlspecialchars($subLine, ENT_QUOTES, 'UTF-8') . '</p>';
                        $lineCount++;
                    }
                }
            }
        }

        $result = implode('', $lines);

        // Add ellipsis if we have more content
        if ($lineCount >= $maxLines && strlen(strip_tags($content)) > strlen(strip_tags($result))) {
            $result = preg_replace('/(<\/p>)$/', ' ' . $end . '$1', $result);
        }

        return $result;
    }

    /**
     * Generate an excerpt with line breaks preserved (simplified version)
     *
     * @param string $content The HTML content
     * @param int $maxLines Maximum number of lines
     * @param string $end The ending string when content is truncated
     * @return string The excerpt with line breaks
     */
    public static function withLineBreaks($content, $maxLines = 3, $end = '...')
    {
        return self::preserveLines($content, $maxLines, $end);
    }
}
