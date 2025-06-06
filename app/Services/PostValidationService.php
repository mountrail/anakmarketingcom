<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PostValidationService
{
    /**
     * Validate post creation request
     */
    public function validateStore(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:question,discussion',
            'images.*' => 'nullable|file|image|max:2048',
            'uploaded_images' => 'nullable|string',
        ]);
    }

    /**
     * Validate post update request
     */
    public function validateUpdate(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:question,discussion',
            'images.*' => 'nullable|file|image|max:2048',
            'uploaded_images' => 'nullable|string',
            'keep_images' => 'nullable|array',
            'keep_images.*' => 'integer|exists:post_images,id',
        ]);
    }

    /**
     * Validate announcement request
     */
    public function validateAnnouncement(Request $request): array
    {
        return $request->validate([
            'message' => 'required|string|max:1000',
            'is_pinned' => 'boolean'
        ]);
    }
}
