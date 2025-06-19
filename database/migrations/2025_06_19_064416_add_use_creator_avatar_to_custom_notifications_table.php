<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('custom_notifications', function (Blueprint $table) {
            $table->boolean('use_creator_avatar')->default(false)->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('custom_notifications', function (Blueprint $table) {
            $table->dropColumn('use_creator_avatar');
        });
    }
};
