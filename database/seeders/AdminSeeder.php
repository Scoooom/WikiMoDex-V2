<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Grant admin + wiki editor to the scooom account.
     * This is a no-op on fresh installs until the user logs in via Discord.
     * Run manually after first login with: php artisan db:seed --class=AdminSeeder
     */
    public function run(): void
    {
        DB::table('users')
            ->where('user_id', '356260100064673814')
            ->update(['is_admin' => true, 'is_wiki_editor' => true]);
    }
}
