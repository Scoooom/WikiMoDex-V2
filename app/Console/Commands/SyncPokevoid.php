<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncPokevoid extends Command
{
    protected $signature = 'pokevoid:sync';
    protected $description = 'Pull latest PokeVoid source and sync form/ability data to the database';

    public function handle()
    {
        $repoPath = base_path('pokevoid');

        // Pull latest
        $this->info('Pulling latest PokeVoid source...');
        $output = [];
        $exit = 0;
        exec("cd {$repoPath} && git pull origin main 2>&1", $output, $exit);
        foreach ($output as $line) {
            $this->line($line);
        }

        if ($exit !== 0) {
            $this->error('git pull failed!');
            return 1;
        }

        // Clear relevant caches
        $this->info('Clearing cache...');
        $this->call('cache:clear');

        // Run the parser
        $this->info('Syncing forms and abilities...');
        $this->call('forms:parse', ['--quiet-abilities' => true]);
        $this->call('items:parse');
        $this->call('changelog:parse');
        $this->call('altbuilds:parse');
        $this->call('altbuilds:warm-sprites');

        $this->info('Sync complete!');

        // Purge pages that forms:parse affects (sub-commands handle their own pages)
        $base = rtrim(config('services.cloudflare.base_url'), '/');
        \Illuminate\Support\Facades\Artisan::call('cf:purge', [
            '--url' => [
                $base . '/',
                $base . '/galleryCore.html',
                $base . '/gallerySmitty.html',
                $base . '/gallerySmittyForm.html',
            ],
        ]);
        $this->info('CF cache purged for galleries and homepage.');

        return 0;
    }
}
