<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class BackfillAvatars extends Command
{
    protected $signature   = 'avatars:backfill {--force : Re-download even if avatar already cached}';
    protected $description = 'Download and cache Discord avatars for all existing users';

    public function handle(): int
    {
        $avatarDir = public_path('avatars');
        if (!is_dir($avatarDir)) {
            mkdir($avatarDir, 0755, true);
        }

        $users = User::all();
        $total = $users->count();

        $this->info("Backfilling avatars for {$total} users…");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $ok = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($users as $user) {
            $dest     = public_path("avatars/{$user->user_id}.png");
            $hashFile = public_path("avatars/{$user->user_id}.hash");
            $hash     = $user->avatar_id;

            // Skip if already cached and hash matches, unless --force
            if (
                !$this->option('force')
                && file_exists($dest)
                && file_exists($hashFile)
                && file_get_contents($hashFile) === $hash
            ) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // No avatar hash — nothing to download, will show default
            if (!$hash || $hash === 'default') {
                $skipped++;
                $bar->advance();
                continue;
            }

            $url = "https://cdn.discordapp.com/avatars/{$user->user_id}/{$hash}.png?size=128";

            try {
                $ctx  = stream_context_create(['http' => ['timeout' => 8]]);
                $data = @file_get_contents($url, false, $ctx);

                if ($data !== false) {
                    file_put_contents($dest, $data);
                    file_put_contents($hashFile, $hash);
                    $ok++;
                } else {
                    $this->newLine();
                    $this->warn("  Failed (no data): {$user->username} — will show default avatar");
                    $failed++;
                }
            } catch (\Throwable $e) {
                $this->newLine();
                $this->warn("  Error: {$user->username} — {$e->getMessage()}");
                $failed++;
            }

            $bar->advance();

            // Be polite to Discord's CDN
            usleep(100_000); // 100ms between requests
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Done. {$ok} downloaded, {$skipped} skipped, {$failed} failed.");

        if ($failed > 0) {
            $this->line("  Failed users will show <fg=cyan>/avatars/default.svg</> until they log in again.");
        }

        return self::SUCCESS;
    }
}
