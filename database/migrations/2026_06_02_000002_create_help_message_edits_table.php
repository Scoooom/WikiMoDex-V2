<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_message_edits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('help_message_id');
            $table->string('editor_discord_id');
            // Each field stores {before, after} only when the value changed, null otherwise
            $table->json('name_diff')->nullable();
            $table->json('header_diff')->nullable();
            $table->json('body_diff')->nullable();
            $table->timestamps();

            $table->foreign('help_message_id')
                  ->references('id')
                  ->on('help_messages')
                  ->onDelete('cascade');

            $table->index('help_message_id');
            $table->index('editor_discord_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_message_edits');
    }
};
