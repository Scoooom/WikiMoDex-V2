<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_builds', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();           // e.g. scooom-sun-team
            $table->string('title');                    // e.g. "Sun Hyper Offense"
            $table->text('description')->nullable();    // optional notes
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('team');                       // array of up to 6 pokemon slots
            // team slot schema:
            // { species, dex_number, ability, passive_ability, nature,
            //   moves: [string, ...], items: [{key, name, stack}, ...], notes }
            $table->unsignedInteger('votes')->default(0);
            $table->timestamps();
        });

        // Upvotes table — one per user per build
        Schema::create('community_build_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('build_id')->constrained('community_builds')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['build_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_build_votes');
        Schema::dropIfExists('community_builds');
    }
};
