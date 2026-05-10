<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ParseAltBuilds extends Command
{
    protected $signature   = 'altbuilds:parse';
    protected $description = 'Parse pokemon-alt-buid.ts and upsert named Alt Builds into alt_builds table';

    public function handle()
    {
        $script = base_path('scripts/parse_alt_builds.py');

        if (!file_exists($script)) {
            $this->error("Parser script not found at {$script}");
            return 1;
        }

        $this->info('Running alt builds parser...');

        $process = proc_open(
            "python3 {$script}",
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );

        if (!is_resource($process)) {
            $this->error('Failed to start parser process.');
            return 1;
        }

        while ($line = fgets($pipes[1])) {
            $this->line(trim($line));
        }
        while ($line = fgets($pipes[2])) {
            $this->error(trim($line));
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        $this->info($exit === 0 ? 'Done!' : "Parser exited with code {$exit}");
        return $exit;
    }
}
