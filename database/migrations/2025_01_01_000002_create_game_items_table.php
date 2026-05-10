<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_items', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();        // modifierTypes key e.g. SOUL_DEW
            $table->string('name');                 // display name
            $table->text('description')->nullable();
            $table->string('tier');                 // COMMON, GREAT, ULTRA, ROGUE, MASTER, MEH, LUXURY, OMEGA
            $table->string('pool')->default('player'); // player, enemy, trainer, shop, omega
            $table->boolean('conditional')->default(false); // ★ only appears conditionally
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_items');
    }
};
