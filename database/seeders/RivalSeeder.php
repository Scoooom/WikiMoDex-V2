<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rival;

class RivalSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('rivals_data.json')), true);

        foreach ($data as $r) {
            Rival::updateOrCreate(
                ['slug' => $r['slug']],
                [
                    'rival_id'         => $r['id'],
                    'name'             => $r['name'],
                    'role'             => $r['role'],
                    'game'             => $r['game'],
                    'type'             => $r['type'],
                    'portrait'         => $r['portrait'],
                    'encounter_pokemon' => $r['encounter_pokemon'],
                    'rematch_pokemon'   => $r['rematch_pokemon'],
                ]
            );
        }

        $this->command->info('Seeded ' . count($data) . ' rivals.');
    }
}
