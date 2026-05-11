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
            $table->unsignedBigInteger('user_id')->index(); // no FK constraint — users table has non-standard PK
            $table->json('team');                       // array of up to 6 pokemon slots
            $table->unsignedInteger('votes')->default(0);
            $table->timestamps();
        });

        Schema::create('community_build_votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('build_id')->index();
            $table->unsignedBigInteger('user_id')->index();
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
