<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WikiArticle;

class ChaosModeSeed extends Seeder
{
    public function run(): void
    {
        $content = file_get_contents(__DIR__ . '/chaos-mode-content.md');

        WikiArticle::updateOrCreate(
            ['slug' => 'chaos-mode'],
            [
                'title'    => 'Chaos Mode',
                'content'  => $content,
                'category' => 'Game Modes',
                'order'    => 10,
            ]
        );

        $this->command->info('Chaos Mode wiki article seeded.');
    }
}
