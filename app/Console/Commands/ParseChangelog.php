<?php

namespace App\Console\Commands;

use App\Models\ChangelogEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ParseChangelog extends Command
{
    protected $signature   = 'changelog:parse';
    protected $description = 'Parse pokevoid git log and upsert into changelog_entries table';

    public function handle()
    {
        $repoPath = base_path('pokevoid');

        if (!is_dir($repoPath . '/.git')) {
            $this->error("pokevoid repo not found at {$repoPath}");
            return 1;
        }

        $output = shell_exec(
            "git -C " . escapeshellarg($repoPath) .
            " log --format='%H|%aI|%s|%b' --all 2>/dev/null"
        );

        if (!$output) {
            $this->error('git log returned no output.');
            return 1;
        }

        $upserted = 0;

        // Split on lines that look like the start of a new log entry (40-char hash)
        $entries = preg_split('/\n(?=[0-9a-f]{40}\|)/', trim($output));

        foreach ($entries as $entry) {
            $lines = explode("\n", trim($entry));
            $first = array_shift($lines);
            $parts = explode('|', $first, 4);

            if (count($parts) < 3) continue;

            [$hash, $date, $subject] = $parts;
            $bodyFromFirst = isset($parts[3]) ? trim($parts[3]) : '';

            // Collect remaining body lines
            $bodyLines = array_filter(array_map('trim', $lines));
            $body = trim($bodyFromFirst . "\n" . implode("\n", $bodyLines));

            // Extract version tag e.g. "v2.43" from subject
            preg_match('/v[\d.]+/i', $subject, $vm);
            $version = $vm[0] ?? null;

            ChangelogEntry::updateOrCreate(
                ['hash' => $hash],
                [
                    'version'      => $version,
                    'title'        => trim($subject),
                    'body'         => $body ?: null,
                    'committed_at' => Carbon::parse($date),
                ]
            );

            $this->line("  OK: {$subject}");
            $upserted++;
        }

        $this->info("Done! Upserted {$upserted} entries.");

        \Illuminate\Support\Facades\Artisan::call('cf:purge', [
            '--url' => [rtrim(config('services.cloudflare.base_url'), '/') . '/wiki:changelog.html'],
        ]);
        $this->info('CF cache purged for changelog.');

        return 0;
    }
}
