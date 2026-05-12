<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('core_moves', function (Blueprint $table) {
            $table->string('dynamic_type_behaviour')->nullable()->after('is_dynamic_type');
            // 'primary', 'secondary', 'form', 'weather', 'terrain', 'iv', or null
        });
    }

    public function down(): void
    {
        Schema::table('core_moves', function (Blueprint $table) {
            $table->dropColumn('dynamic_type_behaviour');
        });
    }
};
