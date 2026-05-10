<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('alt_builds');
        Schema::create('alt_builds', function (Blueprint $table) {
            $table->id();
            $table->string('build_id')->unique();        // e.g. onix_crystal_leviathan
            $table->string('name');                      // e.g. Crystal Leviathan
            $table->string('species');                   // e.g. Onix
            $table->string('champion')->nullable();      // brock, misty, apollo_diana
            $table->integer('rank')->default(1);
            $table->string('type1')->nullable();
            $table->string('type2')->nullable();
            $table->string('stat_focus');               // e.g. DEF, SP.DEF
            $table->string('ability1')->nullable();
            $table->string('ability2')->nullable();
            $table->string('ability3')->nullable();
            $table->string('passive_ability')->nullable();
            $table->text('key_moves')->nullable();       // JSON array of move names
            $table->boolean('prevents_evolution')->default(false);
            $table->string('prerequisite_build')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alt_builds');
    }
};
