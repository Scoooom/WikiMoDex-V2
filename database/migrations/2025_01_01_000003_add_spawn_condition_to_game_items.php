<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_items', function (Blueprint $table) {
            $table->string('spawn_condition', 500)->nullable()->after('conditional');
        });
    }

    public function down(): void
    {
        Schema::table('game_items', function (Blueprint $table) {
            $table->dropColumn('spawn_condition');
        });
    }
};
