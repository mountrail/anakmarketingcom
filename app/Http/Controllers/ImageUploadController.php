<?php

namespace App\Http\Controllers;

use Carbon\Traits\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            // Log the incoming request for debugging
            Log::info('Image upload attempt', [
                'has_file' => $request->hasFile('file'),
                'file_count' => $request->file('file') ? 1 : 0,
                'content_type' => $request->header('Content-Type'),
            ]);

            // Validate the request
            $validated = $request->validate([
                'file' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:2150', // 2.1MB max (extra buffer)
            ]);

            $file = $request->file('file');

            // Additional file checks
            if (!$file || !$file->isValid()) {
                throw new \Exception('Invalid file uploaded');
            }

            Log::info('File details', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
            ]);

            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . Str::random(10) . '.' . $extension;

            // Ensure uploads directory exists
            if (!Storage::disk('public')->exists('uploads')) {
                Storage::disk('public')->makeDirectory('uploads');
            }

            // Store the file in public/uploads directory
            $path = $file->storeAs('uploads', $filename, 'public');

            if (!$path) {
                throw new \Exception('Failed to store file');
            }

            // Verify file was actually stored
            if (!Storage::disk('public')->exists($path)) {
                throw new \Exception('File was not saved properly');
            }

            // Return the full URL
            $url = Storage::url($path);

            Log::info('File uploaded successfully', [
                'path' => $path,
                'url' => $url,
                'filename' => $filename,
            ]);

            return response()->json([
                'location' => $url,
                'success' => true,
                'filename' => $filename,
            ]);

        } catch (ValidationException $e) {
            Log::error('Validation error during image upload', [
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all()),
                'success' => false,
                'type' => 'validation'
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error during image upload', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'Upload failed: ' . $e->getMessage(),
                'success' => false,
                'type' => 'server_error'
            ], 500);
        }
    }
}
