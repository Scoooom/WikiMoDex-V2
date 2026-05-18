<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WarmCache extends Command
{
    protected $signature = 'cache:warm
        {--concurrency=5 : Number of parallel requests}
        {--timeout=10    : Request timeout in seconds}';

    protected $description = 'Warm the Cloudflare cache by fetching all sitemap URLs';

    public function handle(): int
    {
        $sitemapUrl  = rtrim(config('services.cloudflare.base_url'), '/') . '/sitemap.xml';
        $concurrency = (int) $this->option('concurrency');
        $timeout     = (int) $this->option('timeout');

        $this->line("Fetching sitemap: {$sitemapUrl}");

        $xml = @file_get_contents($sitemapUrl);
        if (!$xml) {
            $this->error('Failed to fetch sitemap.');
            return self::FAILURE;
        }

        preg_match_all('/<loc>([^<]+)<\/loc>/', $xml, $matches);
        $urls = $matches[1] ?? [];

        if (empty($urls)) {
            $this->error('No URLs found in sitemap.');
            return self::FAILURE;
        }

        $this->info('Found ' . count($urls) . ' URLs. Warming cache...');

        $chunks   = array_chunk($urls, $concurrency);
        $warmed   = 0;
        $failed   = 0;

        foreach ($chunks as $chunk) {
            $handles = [];
            $mh      = curl_multi_init();

            foreach ($chunk as $url) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => $timeout,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_USERAGENT      => 'WikiMoDex-CacheWarmer/1.0',
                    CURLOPT_NOBODY         => false, // GET, not HEAD, to actually cache the page
                ]);
                curl_multi_add_handle($mh, $ch);
                $handles[$url] = $ch;
            }

            // Execute all handles in parallel
            do {
                curl_multi_exec($mh, $running);
                curl_multi_select($mh);
            } while ($running > 0);

            foreach ($handles as $url => $ch) {
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($code >= 200 && $code < 400) {
                    $this->line("  ✓ {$code} {$url}");
                    $warmed++;
                } else {
                    $this->warn("  ✗ {$code} {$url}");
                    $failed++;
                }
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }

            curl_multi_close($mh);
        }

        $this->info("Done. Warmed: {$warmed}, Failed: {$failed}");
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
