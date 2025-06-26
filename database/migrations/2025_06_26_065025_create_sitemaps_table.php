// database/migrations/xxxx_create_sitemaps_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sitemaps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['posts', 'users', 'static', 'custom']);
            $table->string('filename');
            $table->boolean('is_active')->default(true);
            $table->decimal('priority', 2, 1)->default(0.8);
            $table->enum('changefreq', ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'])->default('weekly');
            $table->json('custom_urls')->nullable();
            $table->timestamp('last_generated')->nullable();
            $table->timestamps();

            $table->unique('filename');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sitemaps');
    }
};
