<?php

namespace Database\Seeders;

use App\Models\HelpMessage;
use Illuminate\Database\Seeder;

class HelpMessageSeeder extends Seeder
{
    public function run(): void
    {
        HelpMessage::truncate();

        $entries = [
            [
                'order'  => 1,
                'name'   => 'Not Pokémon Void',
                'header' => 'PokéVoid ≠ Pokémon Void',
                'body'   => "PokéVoid and Pokémon Void are two completely separate games — we have no affiliation with each other.\n\nIf you're looking for Pokémon Void, this isn't the right server. If you meant to ask about PokéVoid (the fan-made roguelite at pokevoid.wiki), you're in the right place!",
            ],
            [
                'order'  => 2,
                'name'   => 'Where to report bugs',
                'header' => 'How to report a bug',
                'body'   => "Found something broken? Post in #bugs-or-issues with:\n• A description of what happened\n• Your console log — press CTRL+SHIFT+J and screenshot the red area\n\nNo report = no fix. We can't track down bugs we don't know about.",
            ],
            [
                'order'  => 3,
                'name'   => 'Where to find the game',
                'header' => 'Where to play PokéVoid',
                'body'   => "PokéVoid is a browser game — no download needed.\n\nPlay at: https://pokevoid.gg\nWiki: https://pokevoid.wiki\n\nIt works on desktop and mobile. Chrome is recommended on iOS; Safari can be unstable.",
            ],
        ];

        foreach ($entries as $entry) {
            $entry['slug'] = HelpMessage::slugFor($entry['name']);
            HelpMessage::create($entry);
        }

        $this->command->info('HelpMessageSeeder: ' . count($entries) . ' help messages seeded.');
    }
}
