<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alt_builds', function (Blueprint $table) {
            $table->string('base_stats')->nullable()->after('stat_focus'); // JSON: [hp,atk,def,spatk,spdef,spd]
        });
    }

    public function down(): void
    {
        Schema::table('alt_builds', function (Blueprint $table) {
            $table->dropColumn('base_stats');
        });
    }
};
