<?php

namespace App\Services;

use App\Models\CorePokemon;
use App\Models\BuiltinForm;
use App\Models\AltBuild;
use App\Models\Glitch;

class StatService
{
    const STAT_LABELS = ['HP', 'ATK', 'DEF', 'SP.ATK', 'SP.DEF', 'SPD'];
    const STAT_MAP    = ['HP' => 0, 'ATK' => 1, 'DEF' => 2, 'SP.ATK' => 3, 'SP.DEF' => 4, 'SPD' => 5];

    /**
     * Resolve base stats for a build slot.
     * Returns ['stats' => [HP,ATK,DEF,SPATK,SPDEF,SPD], 'source' => string, 'error' => string|null]
     */
    public static function resolveSlot(array $slot): array
    {
        $species  = trim($slot['species'] ?? '');
        $items    = $slot['items'] ?? [];
        $rank     = (int) ($slot['alt_build_rank'] ?? 1);

        if (!$species) {
            return ['stats' => null, 'source' => null, 'error' => null];
        }

        // 1. Try alt_builds first (name match on the build name)
        $altBuild = AltBuild::where('name', $species)->first();
        if ($altBuild) {
            $base = self::coreStatsForDex($altBuild->dex_number);
            if (!$base) {
                return ['stats' => null, 'source' => 'alt_build', 'error' => "Base Pokémon #{$altBuild->dex_number} not found in core_pokemon."];
            }
            $stats = self::calcAltBuildStats($base, $altBuild->stat_focus ?? '', $rank);
            $stats = self::applyItemModifiers($stats, $items);
            return ['stats' => $stats, 'source' => 'alt_build', 'error' => null];
        }

        // 2. Try core_pokemon (official + forms)
        $core = CorePokemon::where('name', $species)->first();
        if ($core) {
            $stats = [$core->hp, $core->atk, $core->def, $core->spatk, $core->spdef, $core->spd];
            $stats = self::applyItemModifiers($stats, $items);
            return ['stats' => $stats, 'source' => 'official', 'error' => null];
        }

        // 3. Try builtin_forms (core glitches + smitty)
        $form = BuiltinForm::where('name', $species)->first();
        if ($form) {
            $stats = [$form->hp, $form->atk, $form->def, $form->spatk, $form->spdef, $form->spd];
            $stats = self::applyItemModifiers($stats, $items);
            return ['stats' => $stats, 'source' => 'builtin', 'error' => null];
        }

        // 4. Try glitches (mod glitch forms) — stats too complex to compute here
        $glitch = Glitch::where('name', $species)->first();
        if ($glitch) {
            return [
                'stats'  => null,
                'source' => 'glitch',
                'error'  => "Mod glitch form stats aren't available in the build planner yet. If this seems wrong, report it to scooom.",
            ];
        }

        // 5. Not found at all
        return [
            'stats'  => null,
            'source' => null,
            'error'  => "Couldn't find \"{$species}\" in any Pokémon database. If this seems wrong, report it to scooom.",
        ];
    }

    // ── Type resolution ───────────────────────────────────────────────

    const TYPE_NAMES = [
        -1=>'Unknown',0=>'Normal',1=>'Fighting',2=>'Flying',3=>'Poison',
        4=>'Ground',5=>'Rock',6=>'Bug',7=>'Ghost',8=>'Steel',9=>'Fire',
        10=>'Water',11=>'Grass',12=>'Electric',13=>'Psychic',14=>'Ice',
        15=>'Dragon',16=>'Dark',17=>'Fairy',18=>'Stellar',20=>'SMITTY',21=>'Glitch',
    ];

    /**
     * Resolve types for a slot. Returns [type1_int|null, type2_int|null].
     * Override types take precedence over DB types.
     */
    public static function resolveTypes(array $slot): array
    {
        $species       = trim($slot['species'] ?? '');
        $overrideType1 = $slot['override_type1'] ?? null;
        $overrideType2 = $slot['override_type2'] ?? null;

        // Flip type name -> int
        $nameToInt = array_flip(self::TYPE_NAMES);

        // Resolve override types first
        $ot1 = $overrideType1 ? ($nameToInt[$overrideType1] ?? null) : null;
        $ot2 = $overrideType2 ? ($nameToInt[$overrideType2] ?? null) : null;

        // Get base types from DB
        $dbType1 = null;
        $dbType2 = null;

        $altBuild = AltBuild::where('name', $species)->first();
        if ($altBuild) {
            // Alt builds define type changes
            $core = CorePokemon::where('dex_number', $altBuild->dex_number)->where('form_key', '')->first()
                 ?? CorePokemon::where('dex_number', $altBuild->dex_number)->first();
            if ($core) { $dbType1 = $core->type1; $dbType2 = $core->type2; }
        } else {
            $core = CorePokemon::where('name', $species)->first();
            if ($core) { $dbType1 = $core->type1; $dbType2 = $core->type2; }
            else {
                $form = BuiltinForm::where('name', $species)->first();
                if ($form) { $dbType1 = $form->type1; $dbType2 = $form->type2; }
            }
        }

        return [
            'type1'      => $ot1 ?? $dbType1,
            'type2'      => $ot2 ?? $dbType2,
            'type1_name' => self::TYPE_NAMES[$ot1 ?? $dbType1] ?? null,
            'type2_name' => self::TYPE_NAMES[$ot2 ?? $dbType2] ?? null,
        ];
    }

    // ── Alt build stat calculation ─────────────────────────────────

    private static function coreStatsForDex(int $dex): ?array
    {
        $mon = CorePokemon::where('dex_number', $dex)->where('form_key', '')->first()
            ?? CorePokemon::where('dex_number', $dex)->first();
        if (!$mon) return null;
        return [$mon->hp, $mon->atk, $mon->def, $mon->spatk, $mon->spdef, $mon->spd];
    }

    private static function calcAltBuildStats(array $base, string $statFocus, int $rank): array
    {
        $statMap = self::STAT_MAP;
        $focusParts   = array_values(array_filter(array_map('trim', explode('/', $statFocus))));
        $focusIndices = array_values(array_filter(
            array_map(fn($s) => $statMap[$s] ?? null, $focusParts),
            fn($v) => $v !== null
        ));

        $new = $base;

        // Stat swapping phase
        foreach ($focusIndices as $ri => $fi) {
            $ranked = [];
            for ($s = 0; $s <= 5; $s++) $ranked[] = [$s, $new[$s]];
            usort($ranked, fn($a, $b) => $b[1] - $a[1]);
            $hi = $ranked[$ri][0];
            if ($fi !== $hi) { $t = $new[$fi]; $new[$fi] = $new[$hi]; $new[$hi] = $t; }
        }

        if ($rank <= 0) return $new;

        $target = 425 + min($rank, 9) * 25;
        $cur    = array_sum($new);
        if ($cur >= $target) return $new;

        $nf    = array_values(array_filter([0,1,2,3,4,5], fn($i) => !in_array($i, $focusIndices)));
        $alloc = $new;
        $diff  = $target - $cur;

        if ($diff <= 30) {
            $ft = array_sum(array_map(fn($i) => $new[$i], $focusIndices));
            foreach ($focusIndices as $i) {
                $alloc[$i] = $new[$i] + (int) floor($diff * ($ft > 0 ? $new[$i] / $ft : 1 / count($focusIndices)));
            }
            $d = $target - array_sum($alloc); $ix = 0;
            while ($d && $ix < 100) {
                $t = $focusIndices[$ix % count($focusIndices)];
                $d > 0 ? $alloc[$t]++ : $alloc[$t]--;
                $d > 0 ? $d-- : $d++;
                $ix++;
            }
            return $alloc;
        }

        $cap = (int) floor($target * 0.30);
        $fb  = count($focusIndices) * (int) floor($cap * 0.80);
        $nfb = $target - $fb;

        $nfr = array_map(fn($i) => ['i' => $i, 'v' => $new[$i]], $nf);
        usort($nfr, fn($a, $b) => $b['v'] - $a['v']);
        $ws = [];
        foreach ($nfr as $r => $s) {
            $p    = 0.50 - ($r / max(1, count($nfr) - 1)) * 0.15;
            $ws[] = [$s['i'], pow($p, 1.5)];
        }
        $tw = array_sum(array_column($ws, 1));
        foreach ($ws as [$i, $w]) $alloc[$i] = max($new[$i], (int) floor($nfb * $w / $tw));

        $fw  = array_map(fn($i) => $new[$i] + 50, $focusIndices);
        $tfw = array_sum($fw);
        foreach ($focusIndices as $k => $i) {
            $alloc[$i] = $tfw > 0 ? (int) floor($fb * $fw[$k] / $tfw) : (int) floor($fb / count($focusIndices));
        }

        $c   = array_map(fn($s) => min($s, $cap), $alloc);
        $d   = $target - array_sum($c);
        $ai  = array_merge($focusIndices, $nf);
        $ai2 = 0;
        while ($d && $ai2 < 1000) {
            $t = $ai[$ai2 % count($ai)];
            if ($d > 0 && $c[$t] < $cap) { $c[$t]++; $d--; }
            elseif ($d < 0 && $c[$t] > 1) { $c[$t]--; $d++; }
            $ai2++;
        }
        return $c;
    }

    // ── Item modifier application ──────────────────────────────────

    private static function applyItemModifiers(array $stats, array $items): array
    {
        foreach ($items as $item) {
            $key    = $item['key'] ?? '';
            $params = $item['params'] ?? [];
            $stack  = max(1, (int) ($item['stack'] ?? 1));

            if (in_array($key, ['STAT_SWITCHER', 'RANDOM_STAT_SWITCHER'])) {
                $s1 = self::STAT_MAP[$params['stat1'] ?? ''] ?? null;
                $s2 = self::STAT_MAP[$params['stat2'] ?? ''] ?? null;
                if ($s1 !== null && $s2 !== null && $s1 !== $s2) {
                    $t = $stats[$s1]; $stats[$s1] = $stats[$s2]; $stats[$s2] = $t;
                }
            }

            if ($key === 'STAT_SACRIFICE') {
                $s1 = self::STAT_MAP[$params['stat1'] ?? ''] ?? null;
                if ($s1 !== null) {
                    $stats[$s1] = (int) floor($stats[$s1] * (1 + $stack * 0.1));
                }
            }
        }

        return $stats;
    }

    /**
     * Format stats for display — returns array of [label, value, pct, is_focus]
     */
    public static function formatForDisplay(array $stats, array $items = [], string $statFocus = ''): array
    {
        $focusMap   = self::STAT_MAP;
        $focusParts = array_filter(array_map('trim', explode('/', $statFocus)));
        $focusIdxs  = array_filter(array_map(fn($s) => $focusMap[$s] ?? null, $focusParts), fn($v) => $v !== null);

        $result = [];
        foreach (self::STAT_LABELS as $i => $label) {
            $val      = $stats[$i];
            $pct      = min(100, (int) round(($val / 255) * 100));
            $result[] = [
                'label'    => $label,
                'value'    => $val,
                'pct'      => $pct,
                'is_focus' => in_array($i, $focusIdxs),
            ];
        }
        return $result;
    }
}
