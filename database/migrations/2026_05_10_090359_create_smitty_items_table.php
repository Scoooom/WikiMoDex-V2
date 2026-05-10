<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smitty_items', function (Blueprint $table) {
            $table->id();
            $table->string('form_name');
            $table->string('enum_name');
            $table->string('item_name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('form_name');
            $table->unique(['form_name', 'enum_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smitty_items');
    }
};
