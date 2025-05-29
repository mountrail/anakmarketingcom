<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostImageService
{
    /**
     * Handle image uploads for a new post
     *
     * @param Request $request
     * @param Post $post
     * @return void
     */
    public function handlePostImages(Request $request, Post $post): void
    {
        // Handle traditional file uploads (if any)
        $this->handleTraditionalFileUploads($request, $post);

        // Handle images uploaded via the drag-drop system
        $this->handleDragDropUploads($request, $post);
    }

    /**
     * Handle image updates for post editing
     *
     * @param Request $request
     * @param Post $post
     * @return void
     */
    public function handleImageUpdates(Request $request, Post $post): void
    {
        // Get current uploaded images from the drag-drop system
        $uploadedImages = $this->parseUploadedImages($request);

        // Get existing images from database
        $existingImages = $post->images()->get();

        // Process image changes
        $this->processImageChanges($post, $existingImages, $uploadedImages);

        // Handle new traditional file uploads
        $this->handleTraditionalFileUploads($request, $post);
    }

    /**
     * Delete a specific image from a post
     *
     * @param Post $post
     * @param int $imageId
     * @return bool
     */
    public function deletePostImage(Post $post, int $imageId): bool
    {
        try {
            $image = $post->images()->where('id', $imageId)->first();

            if (!$image) {
                return false;
            }

            // Delete physical file
            $this->deletePhysicalFile($image->url);

            // Delete database record
            $image->delete();

            Log::info('Image deleted successfully', [
                'post_id' => $post->id,
                'image_id' => $imageId,
                'image_url' => $image->url
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error deleting image', [
                'post_id' => $post->id,
                'image_id' => $imageId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Delete all images associated with a post
     *
     * @param Post $post
     * @return void
     */
    public function deleteAllPostImages(Post $post): void
    {
        foreach ($post->images as $image) {
            try {
                // Delete physical file
                $this->deletePhysicalFile($image->url);

                // Delete database record
                $image->delete();

                Log::info('Image deleted during post deletion', [
                    'post_id' => $post->id,
                    'image_url' => $image->url
                ]);

            } catch (\Exception $e) {
                Log::error('Error deleting image during post deletion', [
                    'post_id' => $post->id,
                    'image_id' => $image->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle traditional file uploads
     *
     * @param Request $request
     * @param Post $post
     * @return void
     */
    private function handleTraditionalFileUploads(Request $request, Post $post): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        foreach ($request->file('images') as $file) {
            try {
                // Store the file
                $path = $file->store('posts', 'public');
                $url = Storage::url($path);

                // Save to database
                PostImage::create([
                    'post_id' => $post->id,
                    'url' => $url,
                    'name' => $file->getClientOriginalName(),
                ]);

                Log::info('Traditional file upload processed', [
                    'post_id' => $post->id,
                    'image_url' => $url,
                    'original_name' => $file->getClientOriginalName()
                ]);

            } catch (\Exception $e) {
                Log::error('Error processing traditional file upload', [
                    'post_id' => $post->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle drag-drop uploaded images
     *
     * @param Request $request
     * @param Post $post
     * @return void
     */
    private function handleDragDropUploads(Request $request, Post $post): void
    {
        if (!$request->has('uploaded_images') || empty($request->uploaded_images)) {
            return;
        }

        $uploadedImages = $this->parseUploadedImages($request);

        if (empty($uploadedImages)) {
            return;
        }

        foreach ($uploadedImages as $imageData) {
            if (!isset($imageData['url']) || empty($imageData['url'])) {
                continue;
            }

            try {
                // Create PostImage record
                PostImage::create([
                    'post_id' => $post->id,
                    'url' => $imageData['url'],
                    'name' => $imageData['name'] ?? 'Uploaded image',
                ]);

                Log::info('Drag-drop image saved to database', [
                    'post_id' => $post->id,
                    'url' => $imageData['url'],
                    'name' => $imageData['name'] ?? 'Uploaded image'
                ]);

            } catch (\Exception $e) {
                Log::error('Error saving drag-drop image to database', [
                    'post_id' => $post->id,
                    'image_url' => $imageData['url'],
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Parse uploaded images from request JSON
     *
     * @param Request $request
     * @return array
     */
    private function parseUploadedImages(Request $request): array
    {
        if (!$request->has('uploaded_images') || empty($request->uploaded_images)) {
            return [];
        }

        try {
            $decodedImages = json_decode($request->uploaded_images, true);

            if (!is_array($decodedImages)) {
                return [];
            }

            return $decodedImages;

        } catch (\Exception $e) {
            Log::error('Error parsing uploaded images JSON', [
                'error' => $e->getMessage(),
                'uploaded_images' => $request->uploaded_images
            ]);

            return [];
        }
    }

    /**
     * Process image changes during post update
     *
     * @param Post $post
     * @param \Illuminate\Database\Eloquent\Collection $existingImages
     * @param array $uploadedImages
     * @return void
     */
    private function processImageChanges(Post $post, $existingImages, array $uploadedImages): void
    {
        // Create arrays to track which images to keep and which to delete
        $imagesToKeep = [];
        $imagesToDelete = [];

        // Check each existing image
        foreach ($existingImages as $existingImage) {
            $shouldKeep = false;

            // Check if this image is in the uploaded_images list (by URL or ID)
            foreach ($uploadedImages as $uploadedImage) {
                if ($this->imagesMatch($existingImage, $uploadedImage)) {
                    $shouldKeep = true;
                    $imagesToKeep[] = $existingImage->id;
                    break;
                }
            }

            // If not found in uploaded images, mark for deletion
            if (!$shouldKeep) {
                $imagesToDelete[] = $existingImage;
            }
        }

        // Delete images that are no longer needed
        $this->deleteUnusedImages($post, $imagesToDelete);

        // Add new images that don't exist in database yet
        $this->addNewImages($post, $uploadedImages);
    }

    /**
     * Check if existing image matches uploaded image data
     *
     * @param PostImage $existingImage
     * @param array $uploadedImage
     * @return bool
     */
    private function imagesMatch(PostImage $existingImage, array $uploadedImage): bool
    {
        // Check by URL
        if (isset($uploadedImage['url']) && $uploadedImage['url'] === $existingImage->url) {
            return true;
        }

        // Check by database ID if it exists in the uploaded image data
        if (isset($uploadedImage['id']) && is_numeric($uploadedImage['id']) && $uploadedImage['id'] == $existingImage->id) {
            return true;
        }

        return false;
    }

    /**
     * Delete unused images
     *
     * @param Post $post
     * @param array $imagesToDelete
     * @return void
     */
    private function deleteUnusedImages(Post $post, array $imagesToDelete): void
    {
        foreach ($imagesToDelete as $imageToDelete) {
            try {
                // Delete physical file
                $this->deletePhysicalFile($imageToDelete->url);

                // Delete database record
                $imageToDelete->delete();

                Log::info('Image deleted during post update', [
                    'post_id' => $post->id,
                    'image_id' => $imageToDelete->id,
                    'image_url' => $imageToDelete->url
                ]);

            } catch (\Exception $e) {
                Log::error('Error deleting image during post update', [
                    'post_id' => $post->id,
                    'image_id' => $imageToDelete->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Add new images that don't exist in database
     *
     * @param Post $post
     * @param array $uploadedImages
     * @return void
     */
    private function addNewImages(Post $post, array $uploadedImages): void
    {
        foreach ($uploadedImages as $uploadedImage) {
            if (!isset($uploadedImage['url']) || empty($uploadedImage['url'])) {
                continue;
            }

            // Check if this image already exists in database
            $existsInDb = $post->images()->where('url', $uploadedImage['url'])->exists();

            if (!$existsInDb) {
                try {
                    // Create new PostImage record
                    PostImage::create([
                        'post_id' => $post->id,
                        'url' => $uploadedImage['url'],
                        'name' => $uploadedImage['name'] ?? 'Uploaded image',
                    ]);

                    Log::info('New image added during post update', [
                        'post_id' => $post->id,
                        'image_url' => $uploadedImage['url'],
                        'image_name' => $uploadedImage['name'] ?? 'Uploaded image'
                    ]);

                } catch (\Exception $e) {
                    Log::error('Error adding new image during post update', [
                        'post_id' => $post->id,
                        'image_url' => $uploadedImage['url'],
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Delete physical file from storage
     *
     * @param string $url
     * @return void
     */
    private function deletePhysicalFile(string $url): void
    {
        $path = str_replace('/storage/', '', $url);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
