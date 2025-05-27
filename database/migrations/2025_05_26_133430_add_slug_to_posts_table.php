<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Post;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add slug column without unique constraint
        Schema::table('posts', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        // Generate slugs for existing posts
        $this->generateSlugsForExistingPosts();

        // Now add the unique constraint and index after populating data
        Schema::table('posts', function (Blueprint $table) {
            $table->string('slug')->unique()->change();
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropColumn('slug');
        });
    }

    /**
     * Generate slugs for existing posts using title-id format
     */
    private function generateSlugsForExistingPosts()
    {
        // Process posts in chunks to avoid memory issues
        Post::chunk(100, function ($posts) {
            foreach ($posts as $post) {
                $baseSlug = Str::slug($post->title);
                $slug = $baseSlug . '-' . $post->id;

                // Handle edge cases where title might be empty or generate empty slug
                if (empty($baseSlug)) {
                    $slug = 'post-' . $post->id;
                }

                // Ensure uniqueness (though ID should make it unique)
                $counter = 1;
                $originalSlug = $slug;

                while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                // Update without triggering model events
                \DB::table('posts')
                    ->where('id', $post->id)
                    ->update(['slug' => $slug]);
            }
        });
    }
};
