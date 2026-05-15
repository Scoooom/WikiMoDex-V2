<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')
            ->where('user_id', '356260100064673814')
            ->update(['is_admin' => true, 'is_wiki_editor' => true]);
    }
}
