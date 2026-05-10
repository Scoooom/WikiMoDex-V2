<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alt_builds', function (Blueprint $table) {
            $table->unsignedSmallInteger('dex_number')->nullable()->after('species');
            $table->text('target_palette')->nullable()->after('prevents_evolution');   // JSON hex array
            $table->text('dark_palette')->nullable()->after('target_palette');         // JSON hex array
        });
    }

    public function down(): void
    {
        Schema::table('alt_builds', function (Blueprint $table) {
            $table->dropColumn(['dex_number', 'target_palette', 'dark_palette']);
        });
    }
};
