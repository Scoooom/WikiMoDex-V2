<?php

namespace App\Services;

use App\Models\CorePokemon;
use App\Models\CoreMove;
use App\Models\Ability;

class ImportService
{
    // Nature int -> name (matches pokevoid Nature enum order)
    const NATURES = [
        'Hardy','Lonely','Brave','Adamant','Naughty',
        'Bold','Docile','Relaxed','Impish','Lax',
        'Timid','Hasty','Serious','Jolly','Naive',
        'Modest','Mild','Quiet','Bashful','Rash',
        'Calm','Gentle','Sassy','Careful','Quirky',
    ];

    // Stat index -> vitamin item key
    const STAT_TO_VITAMIN = [
        0 => 'HP_UP', 1 => 'PROTEIN', 2 => 'IRON',
        3 => 'CALCIUM', 4 => 'ZINC', 5 => 'CARBOS',
    ];

    // Stat index -> display label (for STAT_SACRIFICE params)
    const STAT_LABELS = ['HP', 'ATK', 'DEF', 'SP.ATK', 'SP.DEF', 'SPD'];

    // Items we support importing (typeId -> our key)
    const SUPPORTED_ITEMS = [
        'PLAYER_BASE_STAT_BOOSTER' => null, // handled specially via typePregenArgs
        'BASE_STAT_BOOSTER'        => null, // handled specially
        'STAT_SACRIFICE'           => 'STAT_SACRIFICE',
        'STAT_SWITCHER'            => 'STAT_SWITCHER',
        'TYPE_SWITCHER'            => 'TYPE_SWITCHER',
        'PRIMARY_TYPE_SWITCHER'    => 'PRIMARY_TYPE_SWITCHER',
        'SECONDARY_TYPE_SWITCHER'  => 'SECONDARY_TYPE_SWITCHER',
        'TYPE_SACRIFICE'           => 'TYPE_SACRIFICE',
        'SOUL_DEW'                 => 'SOUL_DEW',
        'FIST_PLATE'               => 'FIST_PLATE',
        'SKY_PLATE'                => 'SKY_PLATE',
        'TOXIC_PLATE'              => 'TOXIC_PLATE',
        'EARTH_PLATE'              => 'EARTH_PLATE',
        'STONE_PLATE'              => 'STONE_PLATE',
        'INSECT_PLATE'             => 'INSECT_PLATE',
        'SPOOKY_PLATE'             => 'SPOOKY_PLATE',
        'IRON_PLATE'               => 'IRON_PLATE',
        'FLAME_PLATE'              => 'FLAME_PLATE',
        'SPLASH_PLATE'             => 'SPLASH_PLATE',
        'MEADOW_PLATE'             => 'MEADOW_PLATE',
        'ZAP_PLATE'                => 'ZAP_PLATE',
        'MIND_PLATE'               => 'MIND_PLATE',
        'ICICLE_PLATE'             => 'ICICLE_PLATE',
        'DRACO_PLATE'              => 'DRACO_PLATE',
        'DREAD_PLATE'              => 'DREAD_PLATE',
        'PIXIE_PLATE'              => 'PIXIE_PLATE',
        'BLANK_PLATE'              => 'BLANK_PLATE',
        'SHOCK_DRIVE'              => 'SHOCK_DRIVE',
        'BURN_DRIVE'               => 'BURN_DRIVE',
        'CHILL_DRIVE'              => 'CHILL_DRIVE',
        'DOUSE_DRIVE'              => 'DOUSE_DRIVE',
    ];

    /**
     * Decode a PRSV file and return slot previews.
     * Returns array of up to 5 slots: [index, wave, timestamp, party_preview[]]
     * Empty slots are included as null.
     */
    public static function decodeFile(string $path): array
    {
        $content = file_get_contents($path);

        // Try raw JSON first (unencrypted builds)
        $data = json_decode($content, true);
        if (!$data) {
            // Try base64
            $data = json_decode(base64_decode($content), true);
        }
        if (!$data) {
            // Try decrypt
            $data = json_decode(\App\Services\PrsvService::decrypt($path,true), true); //json_decode(base64_decode($content), true);
        }
        if (!$data) {
            throw new \RuntimeException('Could not parse PRSV file.');
        }

        $sessionData = $data['sessionData'] ?? [];
        $slots = [];

        for ($i = 0; $i < 5; $i++) {
            $raw = $sessionData[$i] ?? null;
            if ($raw === null) {
                $slots[] = null;
                continue;
            }

            $session = is_string($raw) ? json_decode($raw, true) : $raw;
            $party   = $session['party'] ?? [];

            if (empty($party)) {
                $slots[] = null;
                continue;
            }

            // Index modifiers by pokemonId for alt build detection in preview
            $previewModsByPokemon = [];
            foreach ($session['modifiers'] ?? [] as $mod) {
                $args = $mod['args'] ?? [];
                if (empty($args)) continue;
                $pid = $args[0];
                if (!is_int($pid) && !is_string($pid)) continue;
                $previewModsByPokemon[$pid][] = $mod;
            }

            // Build party preview (just names + level for the picker)
            $preview = [];
            foreach ($party as $p) {
                $pMods    = $previewModsByPokemon[$p['id']] ?? [];
                $altBuild = self::resolveAltBuild($pMods);
                $preview[] = [
                    'species' => $altBuild ? $altBuild['name'] : self::resolveSpeciesName((int)$p['species']),
                    'level'   => $p['level'] ?? null,
                ];
            }

            $slots[] = [
                'index'     => $i,
                'wave'      => $session['waveIndex'] ?? null,
                'timestamp' => $session['timestamp'] ?? null,
                'preview'   => $preview,
            ];
        }

        return $slots;
    }

    /**
     * Parse a specific session slot into a build team array.
     */
    public static function parseSlot(string $path, int $slotIndex): array
    {
        $content = file_get_contents($path);
        $data    = json_decode($content, true) ?? json_decode(base64_decode($content), true);

        if (!$data) {
            // Try decrypt
            $data = json_decode(\App\Services\PrsvService::decrypt($path,true), true); //json_decode(base64_decode($content), true);
        }


        if (!$data) throw new \RuntimeException('Could not parse PRSV file.');

        $raw     = $data['sessionData'][$slotIndex] ?? null;
        if (!$raw) throw new \RuntimeException("Slot {$slotIndex} is empty.");

        $session   = is_string($raw) ? json_decode($raw, true) : $raw;
        $party     = $session['party'] ?? [];
        $modifiers = $session['modifiers'] ?? [];

        // Index modifiers by pokemonId for fast lookup
        $modsByPokemon = [];
        foreach ($modifiers as $mod) {
            $args = $mod['args'] ?? [];
            if (empty($args)) continue;
            $pokemonId = $args[0];
            if (!is_int($pokemonId) && !is_string($pokemonId)) continue;
            $modsByPokemon[$pokemonId][] = $mod;
        }

        $team = [];
        foreach ($party as $p) {
            $pokemonId  = $p['id'];
            $speciesInt = (int)$p['species'];
            $natureInt  = (int)($p['natureOverride'] >= 0 ? $p['natureOverride'] : $p['nature']);
            $pMods      = $modsByPokemon[$pokemonId] ?? [];

            // Alt build overrides species name and provides rank
            $altBuild     = self::resolveAltBuild($pMods);
            $species      = $altBuild ? $altBuild['name'] : self::resolveSpeciesName($speciesInt);
            $altBuildRank = $altBuild ? $altBuild['rank'] : null;

            $nature = self::NATURES[$natureInt] ?? 'Hardy';
            $level  = (int)($p['level'] ?? 1);
            $ivs    = array_values((array)($p['ivs'] ?? []));

            // Moves
            $moves = [];
            foreach ($p['moveset'] ?? [] as $m) {
                $name = self::resolveMoveName((int)$m['moveId']);
                if ($name) $moves[] = $name;
            }

            // Ability — check ANY_ABILITY modifier first, fall back to abilityIndex
            $ability = self::resolveAbility($speciesInt, (int)($p['abilityIndex'] ?? 0), $pMods);

            // Passive — from ANY_PASSIVE_ABILITY modifier
            $passive = self::resolvePassive($pMods);

            // Dex number — prefer alt_builds table if applicable, else core_pokemon
            if ($altBuild) {
                $altModel  = \App\Models\AltBuild::where('name', $species)->first();
                $dexNumber = $altModel?->dex_number ?? $speciesInt;
            } else {
                $coreMon   = CorePokemon::where('species_key', self::resolveSpeciesKey($speciesInt))
                                ->where('form_key', '')->first()
                             ?? CorePokemon::where('dex_number', $speciesInt)->first();
                $dexNumber = $coreMon?->dex_number;
            }

            // Items
            $items = self::resolveItems($pMods);

            $entry = [
                'species'         => $species,
                'dex_number'      => $dexNumber,
                'ability'         => $ability,
                'passive_ability' => $passive,
                'nature'          => $nature,
                'level'           => $level,
                'ivs'             => $ivs,
                'moves'           => $moves,
                'items'           => $items,
                'notes'           => '',
            ];
            if ($altBuildRank !== null) {
                $entry['alt_build_rank'] = $altBuildRank;
            }
            $team[] = $entry;
        }

        return $team;
    }

    // ── Resolution helpers ─────────────────────────────────────────

    /**
     * Check modifiers for a POKEMON_ALT_BUILD entry.
     * Returns ['name' => 'Fireball Seahorse', 'rank' => 3] or null if not an alt build.
     * The build_id is stored in typePregenArgs[0] (e.g. "HORSEA_FIREBALL_SEAHORSE").
     */
    private static function resolveAltBuild(array $mods): ?array
    {
        foreach ($mods as $mod) {
            if (($mod['typeId'] ?? '') !== 'POKEMON_ALT_BUILD') continue;

            $buildId = $mod['typePregenArgs'][0] ?? null;
            if (!$buildId) continue;

            $altBuild = \App\Models\AltBuild::where('build_id', strtolower($buildId))->first();
            if (!$altBuild) continue;

            return [
                'name' => $altBuild->name,
                'rank' => (int)($mod['stackCount'] ?? 1),
            ];
        }
        return null;
    }

    private static function resolveSpeciesKey(int $dex): string
    {
        static $map = null;
        if ($map === null) {
            $map = [];
            $file = base_path('pokevoid/src/enums/species.ts');
            $content = file_get_contents($file);
            $val = 0;
            foreach (explode("\n", $content) as $line) {
                $line = trim($line, " ,\r");
                if (preg_match('/^([A-Z][A-Z0-9_]+)\s*=\s*(\d+)/', $line, $m)) {
                    $val = (int)$m[2]; $map[$val] = $m[1]; $val++;
                } elseif (preg_match('/^([A-Z][A-Z0-9_]+)$/', $line, $m)) {
                    $map[$val] = $m[1]; $val++;
                }
            }
        }
        return $map[$dex] ?? 'UNKNOWN';
    }

    private static function resolveSpeciesName(int $dex): string
    {
        $key  = self::resolveSpeciesKey($dex);
        $core = CorePokemon::where('dex_number', $dex)->where('form_key', '')->first()
             ?? CorePokemon::where('dex_number', $dex)->first();
        return $core?->name ?? ucfirst(strtolower(str_replace('_', ' ', $key)));
    }

    private static function resolveMoveName(int $moveId): ?string
    {
        $move = CoreMove::where('move_key', self::resolveMoveKey($moveId))->first();
        return $move?->name;
    }

    private static function resolveMoveKey(int $id): string
    {
        static $map = null;
        if ($map === null) {
            $map = [];
            $file = base_path('pokevoid/src/enums/moves.ts');
            $content = file_get_contents($file);
            $val = 0;
            foreach (explode("\n", $content) as $line) {
                $line = trim($line, " ,\r");
                if (preg_match('/^([A-Z][A-Z0-9_]+)\s*=\s*(\d+)/', $line, $m)) {
                    $val = (int)$m[2]; $map[$val] = $m[1]; $val++;
                } elseif (preg_match('/^([A-Z][A-Z0-9_]+)$/', $line, $m)) {
                    $map[$val] = $m[1]; $val++;
                }
            }
        }
        return $map[$id] ?? 'NONE';
    }

    private static function resolveAbility(int $species, int $abilityIndex, array $mods): ?string
    {
        // ANY_ABILITY modifier overrides abilityIndex
        foreach ($mods as $mod) {
            if ($mod['typeId'] === 'ANY_ABILITY') {
                $abilityId = $mod['args'][1] ?? null;
                if ($abilityId !== null) {
                    $ab = Ability::where('enum_name', self::resolveAbilityKey((int)$abilityId))->first();
                    if ($ab) return $ab->name;
                }
            }
        }

        // Fall back to abilityIndex on the species
        $key  = self::resolveSpeciesKey($species);
        $core = CorePokemon::where('species_key', $key)->where('form_key', '')->first()
             ?? CorePokemon::where('dex_number', $species)->first();
        if (!$core) return null;

        return match($abilityIndex) {
            0 => $core->ability1,
            1 => $core->ability2,
            2 => $core->ability_hidden,
            default => $core->ability1,
        };
    }

    private static function resolvePassive(array $mods): ?string
    {
        // Last ANY_PASSIVE_ABILITY wins
        $passive = null;
        foreach ($mods as $mod) {
            if ($mod['typeId'] === 'ANY_PASSIVE_ABILITY') {
                $passive = $mod['typePregenArgs'][0]['name'] ?? null;
            }
        }
        return $passive;
    }

    private static function resolveAbilityKey(int $id): string
    {
        static $map = null;
        if ($map === null) {
            $map = [];
            $file = base_path('pokevoid/src/enums/abilities.ts');
            $content = file_get_contents($file);
            $val = 0;
            foreach (explode("\n", $content) as $line) {
                $line = trim($line, " ,\r");
                if (preg_match('/^([A-Z][A-Z0-9_]+)\s*=\s*(\d+)/', $line, $m)) {
                    $val = (int)$m[2]; $map[$val] = $m[1]; $val++;
                } elseif (preg_match('/^([A-Z][A-Z0-9_]+)$/', $line, $m)) {
                    $map[$val] = $m[1]; $val++;
                }
            }
        }
        return $map[$id] ?? 'NONE';
    }

    private static function resolveItems(array $mods): array
    {
        $items = [];

        foreach ($mods as $mod) {
            $typeId    = $mod['typeId'] ?? '';
            $stackCount = (int)($mod['stackCount'] ?? 1);
            $pregen    = $mod['typePregenArgs'] ?? [];
            $args      = $mod['args'] ?? [];

            // Vitamins — PLAYER_BASE_STAT_BOOSTER with typePregenArgs[0] = stat index
            if ($typeId === 'PLAYER_BASE_STAT_BOOSTER' || $typeId === 'BASE_STAT_BOOSTER') {
                $statIdx = $pregen[0] ?? ($args[1] ?? null);
                if ($statIdx !== null && isset(self::STAT_TO_VITAMIN[(int)$statIdx])) {
                    $vitKey  = self::STAT_TO_VITAMIN[(int)$statIdx];
                    $vitName = match($vitKey) {
                        'HP_UP' => 'HP Up', 'PROTEIN' => 'Protein', 'IRON' => 'Iron',
                        'CALCIUM' => 'Calcium', 'ZINC' => 'Zinc', 'CARBOS' => 'Carbos',
                    };
                    $items[] = ['key' => $vitKey, 'name' => $vitName, 'stack' => $stackCount, 'params' => []];
                }
                continue;
            }

            // STAT_SACRIFICE — args[1] = stat index
            if ($typeId === 'STAT_SACRIFICE') {
                $statIdx = $args[1] ?? null;
                if ($statIdx !== null) {
                    $label = self::STAT_LABELS[(int)$statIdx] ?? null;
                    $items[] = ['key' => 'STAT_SACRIFICE', 'name' => 'Stat Switcher', 'stack' => $stackCount,
                                'params' => ['stat1' => $label]];
                }
                continue;
            }

            // STAT_SWITCHER — args[1] and args[2] are stat indices
            if ($typeId === 'STAT_SWITCHER') {
                $s1 = $args[1] ?? null;
                $s2 = $args[2] ?? null;
                $items[] = ['key' => 'STAT_SWITCHER', 'name' => 'Stat Switcher', 'stack' => $stackCount,
                            'params' => [
                                'stat1' => $s1 !== null ? self::STAT_LABELS[(int)$s1] : null,
                                'stat2' => $s2 !== null ? self::STAT_LABELS[(int)$s2] : null,
                            ]];
                continue;
            }

            // Soul Dew
            if ($typeId === 'SOUL_DEW') {
                $items[] = ['key' => 'SOUL_DEW', 'name' => 'Soul Dew', 'stack' => $stackCount, 'params' => []];
                continue;
            }

            // Plates and drives — direct key mapping
            if (isset(self::SUPPORTED_ITEMS[$typeId]) && self::SUPPORTED_ITEMS[$typeId] !== null) {
                $ourKey = self::SUPPORTED_ITEMS[$typeId];
                $name   = ucwords(strtolower(str_replace('_', ' ', $ourKey)));
                $items[] = ['key' => $ourKey, 'name' => $name, 'stack' => $stackCount, 'params' => []];
            }
        }

        return $items;
    }
}
