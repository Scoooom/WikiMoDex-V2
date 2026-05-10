<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('builtin_forms', function (Blueprint $table) {
            $table->string('form_code')->nullable()->after('spd');
        });
    }

    public function down(): void
    {
        Schema::table('builtin_forms', function (Blueprint $table) {
            $table->dropColumn('form_code');
        });
    }
};
