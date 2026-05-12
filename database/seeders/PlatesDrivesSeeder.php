<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlatesDrivesSeeder extends Seeder
{
    // item key => [name, type_name, tier]
    const ITEMS = [
        // Arceus/Silvally plates
        'FIST_PLATE'   => ['Fist Plate',   'Fighting', 'ULTRA'],
        'SKY_PLATE'    => ['Sky Plate',    'Flying',   'ULTRA'],
        'TOXIC_PLATE'  => ['Toxic Plate',  'Poison',   'ULTRA'],
        'EARTH_PLATE'  => ['Earth Plate',  'Ground',   'ULTRA'],
        'STONE_PLATE'  => ['Stone Plate',  'Rock',     'ULTRA'],
        'INSECT_PLATE' => ['Insect Plate', 'Bug',      'ULTRA'],
        'SPOOKY_PLATE' => ['Spooky Plate', 'Ghost',    'ULTRA'],
        'IRON_PLATE'   => ['Iron Plate',   'Steel',    'ULTRA'],
        'FLAME_PLATE'  => ['Flame Plate',  'Fire',     'ULTRA'],
        'SPLASH_PLATE' => ['Splash Plate', 'Water',    'ULTRA'],
        'MEADOW_PLATE' => ['Meadow Plate', 'Grass',    'ULTRA'],
        'ZAP_PLATE'    => ['Zap Plate',    'Electric', 'ULTRA'],
        'MIND_PLATE'   => ['Mind Plate',   'Psychic',  'ULTRA'],
        'ICICLE_PLATE' => ['Icicle Plate', 'Ice',      'ULTRA'],
        'DRACO_PLATE'  => ['Draco Plate',  'Dragon',   'ULTRA'],
        'DREAD_PLATE'  => ['Dread Plate',  'Dark',     'ULTRA'],
        'PIXIE_PLATE'  => ['Pixie Plate',  'Fairy',    'ULTRA'],
        'BLANK_PLATE'  => ['Blank Plate',  'Normal',   'ULTRA'],
        // Genesect drives
        'SHOCK_DRIVE'  => ['Shock Drive',  'Electric', 'ULTRA'],
        'BURN_DRIVE'   => ['Burn Drive',   'Fire',     'ULTRA'],
        'CHILL_DRIVE'  => ['Chill Drive',  'Ice',      'ULTRA'],
        'DOUSE_DRIVE'  => ['Douse Drive',  'Water',    'ULTRA'],
    ];

    public function run(): void
    {
        foreach (self::ITEMS as $key => [$name, $type, $tier]) {
            DB::table('game_items')->upsert(
                ['key' => $key, 'name' => $name, 'tier' => $tier],
                ['key'],
                ['name', 'tier']
            );
        }
    }
}
