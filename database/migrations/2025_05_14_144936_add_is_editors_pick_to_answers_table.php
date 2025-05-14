<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsEditorsPickToAnswersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->boolean('is_editors_pick')->default(false)->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn('is_editors_pick');
        });
    }
}
