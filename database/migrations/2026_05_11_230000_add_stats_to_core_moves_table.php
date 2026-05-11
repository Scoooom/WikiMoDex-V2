<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('core_moves', function (Blueprint $table) {
            $table->tinyInteger('type')->nullable()->after('is_smitty');       // Type enum int
            $table->string('type_name')->nullable()->after('type');             // e.g. Fire
            $table->string('category')->nullable()->after('type_name');         // physical/special/status
            $table->smallInteger('power')->nullable()->after('category');       // -1 = variable/N/A
            $table->smallInteger('accuracy')->nullable()->after('power');       // -1 = never misses
            $table->smallInteger('pp')->nullable()->after('accuracy');
            $table->boolean('is_dynamic_type')->default(false)->after('pp');   // type changes based on user/weather
        });
    }

    public function down(): void
    {
        Schema::table('core_moves', function (Blueprint $table) {
            $table->dropColumn(['type', 'type_name', 'category', 'power', 'accuracy', 'pp', 'is_dynamic_type']);
        });
    }
};
