<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('display_name', 64)->nullable();
            $table->string('pronouns', 32)->nullable();
            $table->text('bio')->nullable();
            $table->string('user_id');
            $table->string('avatar_id');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_wiki_editor')->default(false);
            $table->boolean('mfa_enabled')->default(false);
            $table->bigInteger('join_date');
            $table->bigInteger('last_login');
            $table->boolean('smitty')->default(false);
            $table->binary('raw_prsv')->nullable();
            $table->binary('b64_prsv')->nullable();
            $table->string('tc_color', 20)->default('blue');
            $table->string('tc_favorite_mon')->nullable();
            $table->json('tc_sections')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
