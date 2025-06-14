<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GenerateBadgeThumbnails extends Command
{
    protected $signature = 'badges:generate-thumbnails';
    protected $description = 'Generate thumbnail versions of badge images';

    public function handle()
    {
        $badgesPath = public_path('images/badges');
        $thumbsPath = public_path('images/badges/thumbs');

        // Initialize ImageManager with GD driver
        $manager = new ImageManager(new Driver());

        // Thumbnail sizes to generate
        $sizes = [
            '32x32' => 32,
            '56x56' => 56,
            '64x64' => 64,
            '96x96' => 96
        ];

        if (!File::exists($badgesPath)) {
            $this->error('Badges directory not found: ' . $badgesPath);
            return;
        }

        // Create thumbs directories
        foreach ($sizes as $sizeDir => $size) {
            $sizeThumbPath = $thumbsPath . '/' . $sizeDir;
            if (!File::exists($sizeThumbPath)) {
                File::makeDirectory($sizeThumbPath, 0755, true);
            }
        }

        // Get all badge images
        $badgeFiles = File::files($badgesPath);
        $badgeImages = collect($badgeFiles)->filter(function ($file) {
            return in_array(strtolower($file->getExtension()), ['png', 'jpg', 'jpeg', 'svg', 'gif']);
        });

        if ($badgeImages->isEmpty()) {
            $this->info('No badge images found to process.');
            return;
        }

        $this->info('Found ' . $badgeImages->count() . ' badge images to process...');

        $progressBar = $this->output->createProgressBar($badgeImages->count() * count($sizes));
        $progressBar->start();

        foreach ($badgeImages as $file) {
            $filename = $file->getFilename();

            foreach ($sizes as $sizeDir => $size) {
                $outputPath = $thumbsPath . '/' . $sizeDir . '/' . $filename;

                try {
                    // Skip SVG files for now (they don't need thumbnails typically)
                    if (strtolower($file->getExtension()) === 'svg') {
                        File::copy($file->getPathname(), $outputPath);
                    } else {
                        // Create thumbnail using Intervention Image v3
                        $image = $manager->read($file->getPathname());

                        // Scale to fit within the size while maintaining aspect ratio
                        $image->scaleDown($size, $size);

                        // Create a canvas with transparent background and center the image
                        $canvas = $manager->create($size, $size)->fill('rgba(0, 0, 0, 0)');
                        $canvas->place($image, 'center');

                        $canvas->save($outputPath);
                    }
                } catch (\Exception $e) {
                    $this->error('Failed to process ' . $filename . ' for size ' . $sizeDir . ': ' . $e->getMessage());
                }

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('Badge thumbnails generated successfully!');
    }
}
