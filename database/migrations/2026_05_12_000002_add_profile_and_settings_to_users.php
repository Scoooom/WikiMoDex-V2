<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'display_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('display_name', 64)->nullable()->after('username');
                $table->string('pronouns', 32)->nullable()->after('display_name');
                $table->text('bio')->nullable()->after('pronouns');
                $table->string('tc_favorite_mon')->nullable()->after('tc_color');
                $table->json('tc_sections')->nullable()->after('tc_favorite_mon');
            });
        }

        // Default all tc_sections to shown for existing users
        DB::table('users')->whereNull('tc_sections')->update([
            'tc_sections' => json_encode([
                'rivals'    => true,
                'core'      => true,
                'mod'       => true,
                'smitty'    => true,
                'unismitty' => true,
                'submitted' => true,
            ]),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'pronouns', 'bio', 'tc_favorite_mon', 'tc_sections']);
        });
    }
};
