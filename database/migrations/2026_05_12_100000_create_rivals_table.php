<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rivals', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('rival_id')->unique(); // matches RivalService key
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('role');
            $table->string('game');
            $table->string('type');
            $table->string('portrait');                    // filename in public/rivals/
            $table->json('encounter_pokemon');             // possible team on first encounter
            $table->json('rematch_pokemon');               // possible team on rematch
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rivals');
    }
};
