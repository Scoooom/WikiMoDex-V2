<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_moves', function (Blueprint $table) {
            $table->id();
            $table->string('move_key')->unique();   // enum name e.g. FLAMETHROWER
            $table->string('name');                 // display name e.g. Flamethrower
            $table->boolean('is_smitty')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_moves');
    }
};
