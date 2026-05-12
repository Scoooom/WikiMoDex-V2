<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExtractItemIcons extends Command
{
    protected $signature   = 'items:extract-icons';
    protected $description = 'Extract individual item icon PNGs from pokevoid atlas spritesheets';

    public function handle()
    {
        $script = base_path('scripts/extract_item_icons.py');
        if (!file_exists($script)) {
            $this->error("Script not found: {$script}");
            return 1;
        }

        $this->info('Extracting item icons...');

        $process = proc_open(
            "python3 {$script}",
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );

        if (!is_resource($process)) {
            $this->error('Failed to start process.');
            return 1;
        }

        while ($line = fgets($pipes[1])) $this->line(trim($line));
        while ($line = fgets($pipes[2])) $this->warn(trim($line));

        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        if ($exit === 0) {
            $count = count(glob(storage_path('app/item-icons/*.png')));
            $this->info("Done! {$count} icons available.");
        } else {
            $this->error("Exited with code {$exit}");
        }

        return $exit;
    }
}
