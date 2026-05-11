<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ParseOfficialPokemon extends Command
{
    protected $signature   = 'pokemon:parse-official';
    protected $description = 'Parse pokevoid pokemon-species.ts and upsert all official Pokémon + forms into core_pokemon table';

    public function handle()
    {
        $script = base_path('scripts/parse_official_pokemon.py');
        if (!file_exists($script)) {
            $this->error("Script not found: {$script}");
            return 1;
        }

        $this->info('Parsing official Pokémon from pokemon-species.ts...');

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

        if ($exit === 0) {
            $count = \DB::table('core_pokemon')->count();
            $this->info("Done! {$count} rows in core_pokemon.");
        } else {
            $this->error("Parser exited with code {$exit}");
        }

        return $exit;
    }
}
