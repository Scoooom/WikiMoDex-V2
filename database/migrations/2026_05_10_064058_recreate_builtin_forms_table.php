<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('builtin_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->enum('form_type', ['core', 'smitty', 'smitty_form']);
            $table->string('og_mon')->nullable();
            $table->tinyInteger('type1');
            $table->tinyInteger('type2')->nullable();
            $table->foreignId('ab1_id')->constrained('abilities');
            $table->foreignId('ab2_id')->constrained('abilities');
            $table->foreignId('ha_id')->constrained('abilities');
            $table->integer('bst');
            $table->integer('hp');
            $table->integer('atk');
            $table->integer('def');
            $table->integer('spatk');
            $table->integer('spdef');
            $table->integer('spd');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('builtin_forms');
    }
};
