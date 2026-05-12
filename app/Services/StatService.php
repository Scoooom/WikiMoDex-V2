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

        // 4. Try glitches (mod glitch forms) — use OG mon stats + adjustStats
        $glitch = Glitch::where('name', $species)->first();
        if ($glitch) {
            try {
                $ogStats       = $glitch->getOGStats();          // [idx => ['value'=>X,'percent'=>Y]]
                $bst           = array_sum(array_column($ogStats, 'value'));
                $totalIncrease = $glitch->calculateTotalIncrease($bst);
                $adjusted      = $glitch->adjustStats($ogStats, $totalIncrease);
                // Convert to flat [HP,ATK,DEF,SPATK,SPDEF,SPD] array
                ksort($adjusted);
                $stats = array_map(fn($s) => (int) $s['value'], $adjusted);
                $stats = self::applyItemModifiers(array_values($stats), $items);
                return ['stats' => $stats, 'source' => 'glitch', 'error' => null];
            } catch (\Exception $e) {
                return [
                    'stats'  => null,
                    'source' => 'glitch',
                    'error'  => "Could not load stats for {$species} — report it to scooom.",
                ];
            }
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

        // Resolve override types first (slot-level overrides take top priority)
        $ot1 = $overrideType1 ? ($nameToInt[$overrideType1] ?? null) : null;
        $ot2 = $overrideType2 ? ($nameToInt[$overrideType2] ?? null) : null;

        // If no slot-level override, check type switcher item params
        if ($ot1 === null && $ot2 === null) {
            // Plate/Drive items override type for Arceus, Silvally (and their alt builds)
            $plateDriveTypes = [
                'FIST_PLATE'=>'Fighting','SKY_PLATE'=>'Flying','TOXIC_PLATE'=>'Poison',
                'EARTH_PLATE'=>'Ground','STONE_PLATE'=>'Rock','INSECT_PLATE'=>'Bug',
                'SPOOKY_PLATE'=>'Ghost','IRON_PLATE'=>'Steel','FLAME_PLATE'=>'Fire',
                'SPLASH_PLATE'=>'Water','MEADOW_PLATE'=>'Grass','ZAP_PLATE'=>'Electric',
                'MIND_PLATE'=>'Psychic','ICICLE_PLATE'=>'Ice','DRACO_PLATE'=>'Dragon',
                'DREAD_PLATE'=>'Dark','PIXIE_PLATE'=>'Fairy','BLANK_PLATE'=>'Normal',
                'SHOCK_DRIVE'=>'Electric','BURN_DRIVE'=>'Fire',
                'CHILL_DRIVE'=>'Ice','DOUSE_DRIVE'=>'Water',
            ];
            $formChangers = ['arceus','silvally'];
            $speciesLower = strtolower($species);
            $isFormChanger = array_reduce($formChangers, fn($carry, $s) => $carry || str_contains($speciesLower, $s), false);
            if ($isFormChanger) {
                foreach ($slot['items'] ?? [] as $item) {
                    $ik = $item['key'] ?? '';
                    if (isset($plateDriveTypes[$ik])) {
                        $typeName = $plateDriveTypes[$ik];
                        $ot1 = $nameToInt[$typeName] ?? null;
                        $ot2 = null; // Arceus/Silvally are mono-typed when holding a plate
                        break;
                    }
                }
            }

            foreach ($slot['items'] ?? [] as $item) {
                $key    = $item['key'] ?? '';
                $params = $item['params'] ?? [];
                if (!in_array($key, ['TYPE_SWITCHER', 'PRIMARY_TYPE_SWITCHER', 'SECONDARY_TYPE_SWITCHER', 'TYPE_SACRIFICE'])) continue;
                $pt1 = !empty($params['type1']) ? ($nameToInt[$params['type1']] ?? null) : null;
                $pt2 = !empty($params['type2']) ? ($nameToInt[$params['type2']] ?? null) : null;
                if ($key === 'SECONDARY_TYPE_SWITCHER') {
                    // Only changes type2
                    if ($pt2 !== null) $ot2 = $pt2;
                } elseif ($key === 'PRIMARY_TYPE_SWITCHER') {
                    // Only changes type1
                    if ($pt1 !== null) $ot1 = $pt1;
                } else {
                    // TYPE_SWITCHER / TYPE_SACRIFICE — changes whichever are set
                    if ($pt1 !== null) $ot1 = $pt1;
                    if ($pt2 !== null) $ot2 = $pt2;
                }
            }
        }

        // Get base types from DB
        $dbType1 = null;
        $dbType2 = null;

        $altBuild = AltBuild::where('name', $species)->first();
        if ($altBuild) {
            // Use the alt build's own types when defined; fall back to the base pokemon only for unset slots
            $core = CorePokemon::where('dex_number', $altBuild->dex_number)->where('form_key', '')->first()
                 ?? CorePokemon::where('dex_number', $altBuild->dex_number)->first();
            // alt_builds stores types as strings (e.g. "Fire"); core_pokemon stores ints
            $dbType1 = $altBuild->type1 ? ($nameToInt[$altBuild->type1] ?? null) : $core?->type1;
            $dbType2 = $altBuild->type2 ? ($nameToInt[$altBuild->type2] ?? null) : $core?->type2;
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

    // Vitamin key -> stat index
    const VITAMIN_MAP = [
        'HP_UP'   => 0,
        'PROTEIN' => 1,
        'IRON'    => 2,
        'CALCIUM' => 3,
        'ZINC'    => 4,
        'CARBOS'  => 5,
    ];

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

            // Vitamins: +1% per stack to a specific base stat
            if (isset(self::VITAMIN_MAP[$key])) {
                $si = self::VITAMIN_MAP[$key];
                $stats[$si] = (int) floor($stats[$si] * (1 + $stack * 0.01));
            }
        }

        return $stats;
    }

    /**
     * Calculate effective (in-battle) stats from base stats, nature, and level.
     * Formula from pokevoid/src/field/pokemon.ts calculateStats()
     * IVs are random per pokemon (0-31), so we return a [min, max] range.
     * Soul Dew amplifies nature multipliers: +0.1 per stack.
     * Wonder Guard forces HP to 1.
     *
     * Returns array of [min, max] pairs: [[hpMin,hpMax],[atkMin,atkMax],...]
     */
    /**
     * $ivs: array of 6 values (0-31) or null per stat. null = unknown (show range).
     * Returns array of [min, max] pairs. When IV is known, min === max.
     */
    public static function calcEffectiveStats(array $base, string $nature, int $level, array $items = [], ?string $ability = null, ?string $passive = null, array $ivs = []): array
    {
        $natureBoost = [
            1 => ['Lonely'=>1.1,'Brave'=>1.1,'Adamant'=>1.1,'Naughty'=>1.1,'Bold'=>0.9,'Timid'=>0.9,'Modest'=>0.9,'Calm'=>0.9],
            2 => ['Bold'=>1.1,'Relaxed'=>1.1,'Impish'=>1.1,'Lax'=>1.1,'Lonely'=>0.9,'Hasty'=>0.9,'Mild'=>0.9,'Gentle'=>0.9],
            3 => ['Modest'=>1.1,'Mild'=>1.1,'Quiet'=>1.1,'Rash'=>1.1,'Adamant'=>0.9,'Impish'=>0.9,'Jolly'=>0.9,'Careful'=>0.9],
            4 => ['Calm'=>1.1,'Gentle'=>1.1,'Sassy'=>1.1,'Careful'=>1.1,'Naughty'=>0.9,'Lax'=>0.9,'Naive'=>0.9,'Rash'=>0.9],
            5 => ['Timid'=>1.1,'Hasty'=>1.1,'Jolly'=>1.1,'Naive'=>1.1,'Brave'=>0.9,'Relaxed'=>0.9,'Quiet'=>0.9,'Sassy'=>0.9],
        ];

        $wonderGuard = stripos($ability ?? '', 'Wonder Guard') !== false
                    || stripos($passive ?? '', 'Wonder Guard') !== false;

        $soulDewStack = 0;
        foreach ($items as $item) {
            if (($item['key'] ?? '') === 'SOUL_DEW') {
                $soulDewStack = min(2, (int)($item['stack'] ?? 1));
            }
        }

        $result = [];

        foreach ($base as $s => $baseStat) {
            // Use known IV if provided, otherwise show range (0–31)
            $knownIv = isset($ivs[$s]) && $ivs[$s] !== null ? (int) $ivs[$s] : null;

            if ($s === 0) {
                // HP — Wonder Guard forces to 1
                if ($wonderGuard) {
                    $result[] = [1, 1];
                } else {
                    if ($knownIv !== null) {
                        $val = (int) floor(((2 * $baseStat + $knownIv) * $level) / 100) + $level + 10;
                        $result[] = [$val, $val];
                    } else {
                        $min = (int) floor(((2 * $baseStat + 0)  * $level) / 100) + $level + 10;
                        $max = (int) floor(((2 * $baseStat + 31) * $level) / 100) + $level + 10;
                        $result[] = [$min, $max];
                    }
                }
            } else {
                $mult = $natureBoost[$s][$nature] ?? 1.0;
                if ($mult !== 1.0 && $soulDewStack > 0) {
                    $mult += ($mult > 1 ? 1 : -1) * 0.1 * $soulDewStack;
                }

                $calcStat = function(int $iv) use ($baseStat, $level, $mult): int {
                    $value = (int) floor(((2 * $baseStat + $iv) * $level) / 100) + 5;
                    if ($mult !== 1.0) {
                        $value = (int) ($mult > 1 ? ceil($value * $mult) : floor($value * $mult));
                        $value = max(1, $value);
                    }
                    return $value;
                };

                if ($knownIv !== null) {
                    $val = $calcStat($knownIv);
                    $result[] = [$val, $val];
                } else {
                    $result[] = [$calcStat(0), $calcStat(31)];
                }
            }
        }

        return $result;
    }

    /**
     * Format effective stats (min/max pairs) for display.
     * Returns array of [label, min, max, pct_min, pct_max, is_focus]
     */
    public static function formatEffectiveForDisplay(array $effectivePairs, array $items = [], string $statFocus = ''): array
    {
        $focusIdxs = self::getFocusIndices($statFocus);
        $result = [];
        foreach (self::STAT_LABELS as $i => $label) {
            [$min, $max] = $effectivePairs[$i];
            $result[] = [
                'label'    => $label,
                'min'      => $min,
                'max'      => $max,
                'pct_min'  => min(100, (int) round(($min / 800) * 100)),
                'pct_max'  => min(100, (int) round(($max / 800) * 100)),
                'is_focus' => in_array($i, $focusIdxs),
                'is_range' => $min !== $max,
            ];
        }
        return $result;
    }

    private static function getFocusIndices(string $statFocus): array
    {
        $focusParts = array_filter(array_map('trim', explode('/', $statFocus)));
        return array_values(array_filter(
            array_map(fn($s) => self::STAT_MAP[$s] ?? null, $focusParts),
            fn($v) => $v !== null
        ));
    }

    // Nature -> [boosted_stat_idx, nerfed_stat_idx] (null = neutral)
    const NATURE_EFFECTS = [
        'Lonely'=>[1,2],'Brave'=>[1,5],'Adamant'=>[1,3],'Naughty'=>[1,4],
        'Bold'=>[2,1],'Relaxed'=>[2,5],'Impish'=>[2,3],'Lax'=>[2,4],
        'Modest'=>[3,1],'Mild'=>[3,2],'Quiet'=>[3,5],'Rash'=>[3,4],
        'Calm'=>[4,1],'Gentle'=>[4,2],'Sassy'=>[4,5],'Careful'=>[4,3],
        'Timid'=>[5,1],'Hasty'=>[5,2],'Jolly'=>[5,3],'Naive'=>[5,4],
    ];

    /**
     * Returns [boosted_idx, nerfed_idx] or null for neutral natures.
     */
    public static function getNatureEffect(string $nature): ?array
    {
        return self::NATURE_EFFECTS[$nature] ?? null;
    }

    /**
     * Format stats for display — returns array of [label, value, pct, is_focus, nature_mod]
     * nature_mod: 'boost' | 'nerf' | null
     */
    public static function formatForDisplay(array $stats, array $items = [], string $statFocus = '', string $nature = ''): array
    {
        $focusIdxs = self::getFocusIndices($statFocus);
        $natureEffect = self::getNatureEffect($nature);
        $result = [];
        foreach (self::STAT_LABELS as $i => $label) {
            $val      = $stats[$i];
            $pct      = min(100, (int) round(($val / 255) * 100));
            $natureMod = null;
            if ($natureEffect && $i > 0) { // HP never affected by nature
                if ($i === $natureEffect[0]) $natureMod = 'boost';
                elseif ($i === $natureEffect[1]) $natureMod = 'nerf';
            }
            $result[] = [
                'label'      => $label,
                'value'      => $val,
                'pct'        => $pct,
                'is_focus'   => in_array($i, $focusIdxs),
                'nature_mod' => $natureMod,
            ];
        }
        return $result;
    }
}
