<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    public function up(): void
    {
        Artisan::call('avatars:backfill', [], new \Symfony\Component\Console\Output\ConsoleOutput());
    }

    public function down(): void
    {
        // Remove all cached avatars and hash sidecars
        $avatarDir = public_path('avatars');
        if (!is_dir($avatarDir)) return;

        foreach (glob("{$avatarDir}/*.png") as $file) {
            @unlink($file);
        }
        foreach (glob("{$avatarDir}/*.hash") as $file) {
            @unlink($file);
        }
    }
};
