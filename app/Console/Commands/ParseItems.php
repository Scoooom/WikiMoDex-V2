<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ParseItems extends Command
{
    protected $signature = 'items:parse';
    protected $description = 'Parse PokeVoid modifier-type source and upsert item data into game_items table';

    public function handle()
    {
        $script = base_path('scripts/parse_items.py');
        if (!file_exists($script)) {
            $this->error("Parser script not found at {$script}");
            return 1;
        }

        $this->info('Running item parser...');

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
            $line = trim($line);
            if (str_starts_with($line, '  OK:')) {
                $this->line("<fg=green>{$line}</>");
            } elseif (str_starts_with($line, '  SKIP') || str_starts_with($line, '  ERROR')) {
                $this->line("<fg=yellow>{$line}</>");
            } else {
                $this->info($line);
            }
        }

        while ($line = fgets($pipes[2])) {
            $this->error(trim($line));
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        if ($exit === 0) {
            $this->info('Done!');
        } else {
            $this->error("Parser exited with code {$exit}");
        }

        return $exit;
    }
}
