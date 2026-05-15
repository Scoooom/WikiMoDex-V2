<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_entries', function (Blueprint $table) {
            $table->id();
            $table->string('group');           // e.g. "Getting Started"
            $table->integer('group_order')->default(0);
            $table->integer('order')->default(0);
            $table->string('question');
            $table->text('answer_html');       // rendered HTML (for web)
            $table->text('answer_plain');      // plain text (for Discord embed)
            $table->string('slug')->unique();  // for URL hash + autocomplete value
            $table->boolean('open_by_default')->default(false);
            $table->timestamps();

            $table->index(['group_order', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_entries');
    }
};
