<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TinyMCEUploadController extends Controller
{
    public function store(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        try {
            $file = $request->file('file');

            // Validate file type
            $validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file->getMimeType(), $validTypes)) {
                return response()->json(['error' => 'Invalid file type'], 400);
            }

            // Validate file size (2MB maximum)
            if ($file->getSize() > 2097152) { // 2MB in bytes
                return response()->json(['error' => 'File size exceeds 2MB limit'], 400);
            }

            // Generate unique filename
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

            // Debug info
            Log::info('TinyMCE Upload Attempt', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'target_path' => 'uploads/tinymce/' . $filename
            ]);

            // Store file directly without 'public/' prefix
            // Laravel's storage system will handle the mapping correctly
            $path = $file->store('uploads/tinymce', 'public');

            // Debug info
            Log::info('TinyMCE Upload Success', [
                'path' => $path,
                'url' => Storage::disk('public')->url($path)
            ]);

            return response()->json([
                'location' => Storage::disk('public')->url($path),
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('TinyMCE Upload Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }
}
