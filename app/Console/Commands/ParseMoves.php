<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ParseMoves extends Command
{
    protected $signature   = 'moves:parse';
    protected $description = 'Parse pokevoid moves enum + locale and upsert into core_moves table';

    public function handle()
    {
        $script = base_path('scripts/parse_moves.py');
        if (!file_exists($script)) {
            $this->error("Script not found: {$script}");
            return 1;
        }

        $this->info('Parsing moves...');

        $process = proc_open(
            "python3 {$script}",
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );

        if (!is_resource($process)) {
            $this->error('Failed to start parser.');
            return 1;
        }

        while ($line = fgets($pipes[1])) $this->line(trim($line));
        while ($line = fgets($pipes[2])) $this->error(trim($line));

        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        if ($exit === 0) {
            $count = \DB::table('core_moves')->count();
            $this->info("Done! {$count} rows in core_moves.");
        } else {
            $this->error("Parser exited with code {$exit}");
        }

        return $exit;
    }
}
