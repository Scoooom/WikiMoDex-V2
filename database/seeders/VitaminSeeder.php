<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VitaminSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Vitamins — +1% to specific base stat per stack
            ['key' => 'HP_UP',    'name' => 'HP Up',   'tier' => 'COMMON'],
            ['key' => 'PROTEIN',  'name' => 'Protein', 'tier' => 'COMMON'],
            ['key' => 'IRON',     'name' => 'Iron',    'tier' => 'COMMON'],
            ['key' => 'CALCIUM',  'name' => 'Calcium', 'tier' => 'COMMON'],
            ['key' => 'ZINC',     'name' => 'Zinc',    'tier' => 'COMMON'],
            ['key' => 'CARBOS',   'name' => 'Carbos',  'tier' => 'COMMON'],
            // Soul Dew — amplifies nature stat multipliers
            ['key' => 'SOUL_DEW', 'name' => 'Soul Dew', 'tier' => 'ROGUE'],
        ];

        foreach ($items as $item) {
            DB::table('game_items')->upsert(
                $item,
                ['key'],
                ['name', 'tier']
            );
        }
    }
}
