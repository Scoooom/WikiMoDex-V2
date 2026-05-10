<?php

namespace App\Console\Commands;

use App\Models\AltBuild;
use Illuminate\Console\Command;

class WarmAltBuildSprites extends Command
{
    protected $signature   = 'altbuilds:warm-sprites {--force : Re-render even if cached}';
    protected $description = 'Pre-render all alt build sprites so Discord responses are instant';

    public function handle()
    {
        $builds = AltBuild::whereNotNull('dex_number')
            ->whereNotNull('target_palette')
            ->get(['build_id', 'dex_number', 'target_palette']);

        $script  = base_path('scripts/render_alt_build_sprite.py');
        $outDir  = storage_path('app/alt-build-sprites');

        if (!is_dir($outDir)) mkdir($outDir, 0755, true);

        $rendered = 0;
        $skipped  = 0;

        foreach ($builds as $build) {
            $outPath   = "{$outDir}/{$build->build_id}.png";
            $srcSprite = base_path("pokevoid/public/images/pokemon/{$build->dex_number}.png");

            if (!$this->option('force') && file_exists($outPath)) {
                $this->line("  SKIP: {$build->build_id} (already cached)");
                $skipped++;
                continue;
            }

            if (!file_exists($srcSprite)) {
                $this->warn("  MISSING sprite: {$build->dex_number}.png for {$build->build_id}");
                continue;
            }

            $palette = escapeshellarg(json_encode($build->target_palette));
            $result  = shell_exec(
                "python3 " . escapeshellarg($script) .
                " " . escapeshellarg($srcSprite) .
                " {$palette}" .
                " " . escapeshellarg($outPath) .
                " 2>&1"
            );

            if (file_exists($outPath)) {
                $this->line("  OK: {$build->build_id}");
                $rendered++;
            } else {
                $this->error("  FAIL: {$build->build_id} — {$result}");
            }
        }

        $this->info("Done! Rendered: {$rendered}, Skipped: {$skipped}");
        return 0;
    }
}
