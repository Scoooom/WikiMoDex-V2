<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wiki_sections', function (Blueprint $table) {
            $table->id();
            $table->string('article_slug')->index();
            $table->string('article_title');
            $table->string('article_category');
            $table->string('heading');           // e.g. "Pity System"
            $table->string('anchor');            // e.g. "pity-system"
            $table->tinyInteger('heading_level'); // 2 or 3
            $table->text('body');                // plain-text content under heading
            $table->integer('word_count')->default(0);
            $table->timestamps();

            $table->index(['article_slug', 'anchor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wiki_sections');
    }
};
