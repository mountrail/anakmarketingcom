<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamifyTables extends Migration
{
    public function up(): void
    {
        // Create reputations table
        if (!Schema::hasTable('reputations')) {
            Schema::create('reputations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->mediumInteger('point', false)->default(0);
                $table->integer('subject_id')->nullable();
                $table->string('subject_type')->nullable();
                $table->unsignedInteger('payee_id')->nullable();
                $table->text('meta')->nullable();
                $table->timestamps();
            });
        }

        // Create badges table
        if (!Schema::hasTable('badges')) {
            Schema::create('badges', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('description')->nullable();
                $table->string('icon')->nullable();
                $table->tinyInteger('level')->default(config('gamify.badge_default_level', 1));
                $table->timestamps();
            });
        }

        // Create user_badges table
        if (!Schema::hasTable('user_badges')) {
            Schema::create('user_badges', function (Blueprint $table) {
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('badge_id');
                $table->timestamps();

                $table->primary(['user_id', 'badge_id']);

                // Add foreign key constraints if users table exists
                if (Schema::hasTable('users')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                }
                $table->foreign('badge_id')->references('id')->on('badges')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('reputations');
    }
}
