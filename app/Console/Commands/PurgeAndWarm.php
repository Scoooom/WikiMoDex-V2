<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PurgeAndWarm extends Command
{
    protected $signature = 'cf:refresh
        {--concurrency=5 : Number of parallel requests for cache warming}';

    protected $description = 'Purge the entire Cloudflare cache then warm it from the sitemap';

    public function handle(): int
    {
        $this->info('Step 1/2: Purging Cloudflare cache...');
        $purge = $this->call('cf:purge', ['--all' => true]);

        if ($purge !== self::SUCCESS) {
            $this->error('Purge failed, aborting warm.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Step 2/2: Warming cache from sitemap...');
        return $this->call('cache:warm', [
            '--concurrency' => $this->option('concurrency'),
        ]);
    }
}
