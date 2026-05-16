<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CloudflarePurge extends Command
{
    protected $signature = 'cf:purge
        {--all         : Purge the entire zone cache}
        {--url=*       : One or more URLs to purge}
        {--wiki        : Purge all wiki pages}
        {--gallery     : Purge gallery pages}
        {--home        : Purge the homepage}
        {--sprites     : Purge all pokevoid sprites}';

    protected $description = 'Purge pages from the Cloudflare cache';

    private string $apiToken;
    private string $zoneId;
    private string $baseUrl;

    public function handle(): int
    {
        $this->apiToken = config('services.cloudflare.api_token');
        $this->zoneId   = config('services.cloudflare.zone_id');
        $this->baseUrl  = rtrim(config('services.cloudflare.base_url'), '/');

        if (app()->environment('local')) {
            $this->error('cf:purge is disabled in local environment.');
            return self::FAILURE;
        }

        if (!$this->apiToken || !$this->zoneId) {
            $this->error('CLOUDFLARE_API_TOKEN and CLOUDFLARE_ZONE_ID must be set in .env');
            return self::FAILURE;
        }

        if ($this->option('all')) {
            return $this->purgeAll();
        }

        $urls = array_values($this->option('url'));

        if ($this->option('home')) {
            $urls[] = $this->baseUrl . '/';
        }

        if ($this->option('wiki')) {
            $urls = array_merge($urls, $this->wikiUrls());
        }

        if ($this->option('gallery')) {
            $urls = array_merge($urls, $this->galleryUrls());
        }

        if ($this->option('sprites')) {
            $urls = array_merge($urls, $this->spriteUrls());
        }

        if (empty($urls)) {
            $this->error('Specify --all, --url=<url>, --wiki, --gallery, or --home.');
            $this->line('Examples:');
            $this->line('  php artisan cf:purge --all');
            $this->line('  php artisan cf:purge --wiki');
            $this->line('  php artisan cf:purge --sprites');
            $this->line('  php artisan cf:purge --url=https://void.scooom.xyz/wiki.html --url=https://void.scooom.xyz/');
            return self::FAILURE;
        }

        return $this->purgeUrls($urls);
    }

    private function purgeAll(): int
    {
        $this->warn('Purging ENTIRE Cloudflare zone cache…');

        $result = $this->cfRequest('DELETE', '/purge_cache', [
            'purge_everything' => true,
        ]);

        if ($result['success'] ?? false) {
            $this->info('✓ Entire cache purged.');
            return self::SUCCESS;
        }

        $this->error('Purge failed: ' . json_encode($result['errors'] ?? []));
        return self::FAILURE;
    }

    private function purgeUrls(array $urls): int
    {
        $urls = array_unique($urls);

        // CF allows max 30 URLs per request
        $chunks = array_chunk($urls, 30);
        $failed = false;

        foreach ($chunks as $chunk) {
            $this->line('Purging ' . count($chunk) . ' URL(s)…');
            foreach ($chunk as $url) {
                $this->line("  · {$url}");
            }

            $result = $this->cfRequest('DELETE', '/purge_cache', ['files' => $chunk]);

            if ($result['success'] ?? false) {
                $this->info('  ✓ Done.');
            } else {
                $this->error('  ✗ Failed: ' . json_encode($result['errors'] ?? []));
                $failed = true;
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function wikiUrls(): array
    {
        $urls = [
            $this->baseUrl . '/wiki.html',
            $this->baseUrl . '/wiki:items.html',
            $this->baseUrl . '/wiki:alt-builds.html',
            $this->baseUrl . '/wiki:changelog.html',
        ];

        // Add all article pages
        $articles = \App\Models\WikiArticle::pluck('slug');
        foreach ($articles as $slug) {
            $urls[] = $this->baseUrl . '/wiki:' . $slug . '.html';
        }

        return $urls;
    }

    private function spriteUrls(): array
    {
        // Purge Laravel sprite routes
        $urls = [];
        $glitchIds = \App\Models\Glitch::pluck('id');
        foreach ($glitchIds as $id) {
            $urls[] = $this->baseUrl . '/front:' . $id . '.png';
            $urls[] = $this->baseUrl . '/back:' . $id . '.png';
        }

        // Purge pokevoid atlas sprites by dex number
        $dexNumbers = \App\Models\AltBuild::whereNotNull('dex_number')->pluck('dex_number')->unique();
        foreach ($dexNumbers as $dex) {
            $urls[] = $this->baseUrl . '/pokevoid-sprites/' . $dex . '.png';
            $urls[] = $this->baseUrl . '/pokevoid-atlas/' . $dex . '.json';
        }

        // Purge alt build sprites
        $buildIds = \App\Models\AltBuild::pluck('build_id');
        foreach ($buildIds as $id) {
            $urls[] = $this->baseUrl . '/alt-build-sprite:' . $id . '.png';
        }

        return $urls;
    }

    private function galleryUrls(): array
    {
        return [
            $this->baseUrl . '/gallery.html',
            $this->baseUrl . '/galleryCore.html',
            $this->baseUrl . '/gallerySmitty.html',
            $this->baseUrl . '/gallerySmittyForm.html',
        ];
    }

    private function cfRequest(string $method, string $endpoint, array $body): array
    {
        $url = "https://api.cloudflare.com/client/v4/zones/{$this->zoneId}{$endpoint}";

        $ctx = stream_context_create([
            'http' => [
                'method'  => $method,
                'header'  => implode("\r\n", [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apiToken,
                ]),
                'content' => json_encode($body),
                'timeout' => 15,
                'ignore_errors' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $ctx);

        if ($raw === false) {
            return ['success' => false, 'errors' => ['Network error']];
        }

        return json_decode($raw, true) ?? ['success' => false, 'errors' => ['Invalid JSON response']];
    }
}
