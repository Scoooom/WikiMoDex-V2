<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('changelog_entries', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 40)->unique();
            $table->string('version')->nullable();  // e.g. "v2.43"
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamp('committed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changelog_entries');
    }
};
