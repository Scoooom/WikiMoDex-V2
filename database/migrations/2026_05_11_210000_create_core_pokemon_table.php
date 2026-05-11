<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_pokemon', function (Blueprint $table) {
            $table->id();
            $table->integer('dex_number');               // Species enum value
            $table->string('species_key');               // enum name e.g. CHARIZARD
            $table->string('name');                      // display name e.g. Charizard
            $table->string('form_name')->default('');    // e.g. "Mega X", "" for base
            $table->string('form_key')->default('');     // e.g. "mega-x", "" for base
            $table->tinyInteger('type1');
            $table->tinyInteger('type2')->nullable();
            $table->string('ability1')->nullable();      // display name
            $table->string('ability2')->nullable();
            $table->string('ability_hidden')->nullable();
            $table->integer('bst');
            $table->integer('hp');
            $table->integer('atk');
            $table->integer('def');
            $table->integer('spatk');
            $table->integer('spdef');
            $table->integer('spd');
            $table->unique(['dex_number', 'form_key']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_pokemon');
    }
};
