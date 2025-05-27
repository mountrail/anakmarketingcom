<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_slug_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('old_slug')->index();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Ensure old_slug is unique
            $table->unique('old_slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_slug_redirects');
    }
};
