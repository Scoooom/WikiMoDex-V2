<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('core_pokemon', function (Blueprint $table) {
            // Position after form_key; 0 = base form, 1+ = forms in species definition order
            $table->tinyInteger('form_index')->default(0)->after('form_key');
        });
    }

    public function down(): void
    {
        Schema::table('core_pokemon', function (Blueprint $table) {
            $table->dropColumn('form_index');
        });
    }
};
