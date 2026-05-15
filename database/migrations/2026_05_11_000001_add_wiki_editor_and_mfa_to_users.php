<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_wiki_editor')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_wiki_editor')->default(false)->after('is_admin');
                $table->boolean('mfa_enabled')->default(false)->after('is_wiki_editor');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_wiki_editor', 'mfa_enabled']);
        });
    }
};
