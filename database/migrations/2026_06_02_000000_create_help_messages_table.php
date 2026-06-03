<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_messages', function (Blueprint $table) {
            $table->id();
            $table->integer('order')->default(0);
            $table->string('name');           // Short label shown in autocomplete (e.g. "Not Pokemon Void")
            $table->string('slug')->unique(); // Autocomplete value / URL-safe key
            $table->string('header');         // Bold title line sent in the embed
            $table->text('body');             // Full message body (plain text, shown in Discord embed)
            $table->timestamps();

            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_messages');
    }
};
