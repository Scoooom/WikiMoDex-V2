<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('glitches', function (Blueprint $table) {
            $table->increments('id');
            $table->binary('json_data');
            $table->integer('created_by');
            $table->string('name');
            $table->binary('front');
            $table->binary('back');
            $table->binary('icon');
            $table->string('filename');
        });

        Schema::create('glitchLikes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('glitchID')->nullable();
            $table->string('userID')->nullable();
        });

        Schema::create('glitchDislikes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('glitchID')->nullable();
            $table->string('userID')->nullable();
        });

        Schema::create('userLikes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('creatorID')->nullable();
            $table->string('userID')->nullable();
        });

        Schema::create('community_build_votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('build_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->unique(['build_id', 'user_id']);
            $table->index('build_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glitches');
        Schema::dropIfExists('glitchLikes');
        Schema::dropIfExists('glitchDislikes');
        Schema::dropIfExists('userLikes');
        Schema::dropIfExists('community_build_votes');
    }
};
