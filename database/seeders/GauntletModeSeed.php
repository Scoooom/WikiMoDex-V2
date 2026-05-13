<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WikiArticle;

class GauntletModeSeed extends Seeder
{
    public function run(): void
    {
        $content = file_get_contents(__DIR__ . '/gauntlet-mode-content.md');

        WikiArticle::updateOrCreate(
            ['slug' => 'gauntlet-mode'],
            [
                'title'    => 'Gauntlet Mode',
                'content'  => $content,
                'category' => 'Game Modes',
                'order'    => 5,
            ]
        );

        $this->command->info('Gauntlet Mode wiki article seeded.');
    }
}
