<?php

namespace App\Http\Controllers;

use App\Models\Glitch;
use App\Models\BuiltinForm;
use App\Services\BuiltInService;
use App\Services\PokemonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DiscordInteractionController extends Controller
{
    // Interaction types
    const PING          = 1;
    const APPLICATION   = 2;
    const AUTOCOMPLETE  = 4;

    // Response types
    const PONG                         = 1;
    const CHANNEL_MESSAGE              = 4;
    const AUTOCOMPLETE_RESULT          = 8;

    public function handle(Request $request)
    {
        // Verify Discord signature
        if (!$this->verifySignature($request)) {
            return response('Invalid signature', 401);
        }

        $data = $request->json()->all();
        $type = $data['type'];

        // Ping
        if ($type === self::PING) {
            return response()->json(['type' => self::PONG]);
        }

        // Autocomplete
        if ($type === self::AUTOCOMPLETE) {
            return $this->handleAutocomplete($data);
        }

        // Slash command
        if ($type === self::APPLICATION) {
            return $this->handleCommand($data);
        }

        return response('Unknown interaction type', 400);
    }

    private function verifySignature(Request $request): bool
    {
        $publicKey  = env('DISCORD_PUBLIC_KEY');
        $signature  = $request->header('X-Signature-Ed25519');
        $timestamp  = $request->header('X-Signature-Timestamp');
        $body       = $request->getContent();

        if (!$signature || !$timestamp || !$publicKey) {
            return false;
        }

        try {
            $message = $timestamp . $body;
            $key     = sodium_hex2bin($publicKey);
            $sig     = sodium_hex2bin($signature);
            return sodium_crypto_sign_verify_detached($sig, $message, $key);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function handleAutocomplete(array $data): \Illuminate\Http\JsonResponse
    {
        $commandName = $data['data']['name'];
        $query = '';
        foreach ($data['data']['options'] ?? [] as $option) {
            if (isset($option['value'])) {
                $query = strtolower($option['value']);
                break;
            }
        }

        if ($commandName === 'ability') {
            return $this->autocompleteAbility($query);
        }

        if ($commandName === 'alt-build') {
            return $this->autocompleteAltBuild($query);
        }

        return $this->autocompleteForm($query);
    }

    private function autocompleteForm(string $query): \Illuminate\Http\JsonResponse
    {
        $choices = [];

        $glitches = Glitch::whereRaw('LOWER(name) LIKE ?', ["%{$query}%"])
            ->limit(8)
            ->get();
        foreach ($glitches as $g) {
            $choices[] = ['name' => $g->name . ' (Mod Glitch)', 'value' => $g->name];
        }

        $remaining = 25 - count($choices);
        if ($remaining > 0) {
            $forms = BuiltinForm::whereRaw('LOWER(name) LIKE ?', ["%{$query}%"])
                ->limit($remaining)
                ->get();
            foreach ($forms as $f) {
                $label = match($f->form_type) {
                    'core'        => 'Core Glitch',
                    'smitty'      => 'SMITTY',
                    'smitty_form' => 'SMITTY Form',
                    default       => 'Form'
                };
                $choices[] = ['name' => ucwords($f->name) . " ({$label})", 'value' => $f->name];
            }
        }

        return response()->json([
            'type' => self::AUTOCOMPLETE_RESULT,
            'data' => ['choices' => $choices]
        ]);
    }

    private function autocompleteAbility(string $query): \Illuminate\Http\JsonResponse
    {
        $abilities = \App\Models\Ability::whereRaw('LOWER(name) LIKE ?', ["%{$query}%"])
            ->where('enum_name', '!=', 'NONE')
            ->limit(25)
            ->get();

        $choices = [];
        foreach ($abilities as $a) {
            $choices[] = ['name' => $a->name, 'value' => $a->enum_name];
        }

        return response()->json([
            'type' => self::AUTOCOMPLETE_RESULT,
            'data' => ['choices' => $choices]
        ]);
    }

    private function handleCommand(array $data): \Illuminate\Http\JsonResponse
    {
        $commandName = $data['data']['name'];

        if ($commandName === 'form') {
            $formName = '';
            foreach ($data['data']['options'] ?? [] as $option) {
                if ($option['name'] === 'name') {
                    $formName = $option['value'];
                    break;
                }
            }
            return $this->handleForm($formName);
        }

        if ($commandName === 'wiki') {
            return response()->json([
                'type' => self::CHANNEL_MESSAGE,
                'data' => [
                    'embeds' => [[
                        'title'       => 'WikiMoDex',
                        'description' => 'The PokéVoid community wiki and Pokémon form database.',
                        'color'       => 0x7c5cbf,
                        'fields'      => [
                            ['name' => '📖 Wiki',          'value' => "[Game mechanics, champions, items & more](<https://void.scooom.xyz/wiki.html>)",                   'inline' => false],
                            ['name' => '🎒 Items',         'value' => "[Full item reference by tier](<https://void.scooom.xyz/wiki:items.html>)",                         'inline' => true],
                            ['name' => '✨ Alt Builds',    'value' => "[Champion alt build gallery](<https://void.scooom.xyz/wiki:alt-builds.html>)",                     'inline' => true],
                            ['name' => '👾 Mod Glitches',  'value' => "[Community-made glitch forms](<https://void.scooom.xyz/gallery.html>)",                           'inline' => true],
                            ['name' => '⚡ Core Glitches', 'value' => "[Official glitch forms](<https://void.scooom.xyz/galleryCore.html>)",                             'inline' => true],
                            ['name' => '📅 Gacha',         'value' => "[Today\'s legendary & Pokérus calendar](<https://void.scooom.xyz/gacha.html>)",                   'inline' => true],
                            ['name' => '❓ FAQ',           'value' => "[Frequently asked questions](<https://void.scooom.xyz/faq.html>)",                                 'inline' => true],
                        ],
                        'footer' => ['text' => 'WikiMoDex • Use /form, /ability, or /alt-build for specific lookups'],
                    ]]
                ]
            ]);
        }

        if ($commandName === 'ability') {
            $enumName = '';
            foreach ($data['data']['options'] ?? [] as $option) {
                if ($option['name'] === 'name') {
                    $enumName = $option['value'];
                    break;
                }
            }
            return $this->handleAbility($enumName);
        }

        if ($commandName === 'alt-build') {
            $buildId = '';
            foreach ($data['data']['options'] ?? [] as $option) {
                if ($option['name'] === 'name') {
                    $buildId = $option['value'];
                    break;
                }
            }
            return $this->handleAltBuild($buildId);
        }

        if ($commandName === 'wiki-search') {
            $query = '';
            foreach ($data['data']['options'] ?? [] as $option) {
                if ($option['name'] === 'query') { $query = $option['value']; break; }
            }
            return $this->handleWikiSearch($query);
        }

        return response()->json([
            'type' => self::CHANNEL_MESSAGE,
            'data' => ['content' => 'Unknown command.']
        ]);
    }

    private function handleWikiSearch(string $query): \Illuminate\Http\JsonResponse
    {
        if (strlen(trim($query)) < 2) {
            return response()->json([
                'type' => self::CHANNEL_MESSAGE,
                'data' => ['content' => 'Please provide a search term of at least 2 characters.']
            ]);
        }

        $q = trim($query);

        // Search wiki articles
        $articles = \App\Models\WikiArticle::where('title', 'like', "%{$q}%")
            ->orWhere('content', 'like', "%{$q}%")
            ->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", ["%{$q}%"])
            ->limit(3)
            ->get(['slug', 'title', 'category', 'content']);

        // Search items
        $items = \App\Models\GameItem::where('name', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->limit(2)
            ->get(['name', 'description', 'tier']);

        // Search alt builds
        $builds = \App\Models\AltBuild::where('name', 'like', "%{$q}%")
            ->orWhere('species', 'like', "%{$q}%")
            ->limit(2)
            ->get(['build_id', 'name', 'species', 'type1', 'type2']);

        $totalResults = $articles->count() + $items->count() + $builds->count();

        if ($totalResults === 0) {
            return response()->json([
                'type' => self::CHANNEL_MESSAGE,
                'data' => ['embeds' => [[
                    'title'       => "No results for \"{$q}\"",
                    'description' => "Try searching on the [full wiki](<https://void.scooom.xyz/wiki.html>) for broader results.",
                    'color'       => 0x7c5cbf,
                    'footer'      => ['text' => 'WikiMoDex • Search'],
                ]]]
            ]);
        }

        $fields = [];

        foreach ($articles as $article) {
            // Extract excerpt around the match
            $plain = preg_replace('/[#*`\[\]_>|~]/u', '', $article->content);
            $plain = preg_replace('/\s+/', ' ', $plain);
            $pos   = stripos($plain, $q);
            if ($pos !== false) {
                $start   = max(0, $pos - 40);
                $excerpt = ($start > 0 ? '…' : '') . substr($plain, $start, 120) . '…';
            } else {
                $excerpt = substr($plain, 0, 120) . '…';
            }

            $url = "https://void.scooom.xyz/wiki:{$article->slug}.html";
            $fields[] = [
                'name'   => "📄 {$article->title}",
                'value'  => "{$excerpt}\n[Read more](<{$url}>)",
                'inline' => false,
            ];
        }

        foreach ($items as $item) {
            $tierLabel = ucfirst(strtolower($item->tier));
            $desc = $item->description ? substr($item->description, 0, 100) . (strlen($item->description) > 100 ? '…' : '') : 'No description.';
            $fields[] = [
                'name'   => "🎒 {$item->name} ({$tierLabel})",
                'value'  => "{$desc}\n[Items Reference](<https://void.scooom.xyz/wiki:items.html>)",
                'inline' => false,
            ];
        }

        foreach ($builds as $build) {
            $types = collect([$build->type1, $build->type2])->filter()->join(' / ');
            $url   = "https://void.scooom.xyz/wiki:alt-builds.html#build-{$build->build_id}";
            $fields[] = [
                'name'   => "✨ {$build->species} — {$build->name}",
                'value'  => ($types ? "Types: {$types}\n" : '') . "[View Alt Build](<{$url}>)",
                'inline' => false,
            ];
        }

        $wikiUrl = "https://void.scooom.xyz/wiki.html";

        return response()->json([
            'type' => self::CHANNEL_MESSAGE,
            'data' => ['embeds' => [[
                'title'       => "Wiki Search: \"{$q}\"",
                'description' => "{$totalResults} result" . ($totalResults !== 1 ? 's' : '') . " · [Search on wiki](<https://void.scooom.xyz/wiki.html>)",
                'color'       => 0x7c5cbf,
                'fields'      => array_slice($fields, 0, 5),
                'footer'      => ['text' => 'WikiMoDex • Use /form, /ability, or /alt-build for specific lookups'],
            ]]]
        ]);
    }

    private function autocompleteAltBuild(string $query): \Illuminate\Http\JsonResponse
    {
        $builds = \App\Models\AltBuild::where('name', 'like', "%{$query}%")
            ->orWhere('species', 'like', "%{$query}%")
            ->orderByRaw("CASE WHEN species LIKE ? THEN 0 ELSE 1 END", ["%{$query}%"])
            ->limit(25)
            ->get(['build_id', 'name', 'species', 'champion']);

        $choices = $builds->map(fn($b) => [
            'name'  => "{$b->species} — {$b->name}",
            'value' => $b->build_id,
        ])->values()->toArray();

        return response()->json([
            'type' => self::AUTOCOMPLETE_RESULT,
            'data' => ['choices' => $choices],
        ]);
    }

    private function handleAltBuild(string $buildId): \Illuminate\Http\JsonResponse
    {
        $build = \App\Models\AltBuild::where('build_id', $buildId)->first();

        if (!$build) {
            return response()->json([
                'type' => self::CHANNEL_MESSAGE,
                'data' => ['content' => "Alt Build `{$buildId}` not found!"]
            ]);
        }

        $types     = collect([$build->type1, $build->type2])->filter()->join(' / ');
        $abilities = collect([$build->ability1, $build->ability2, $build->ability3])->filter()->join(' / ');

        $fields = [];
        if ($types)                  $fields[] = ['name' => 'Types',      'value' => $types,                  'inline' => true];
        if ($build->stat_focus)      $fields[] = ['name' => 'Stat Focus', 'value' => $build->stat_focus,      'inline' => true];
        if ($abilities)              $fields[] = ['name' => 'Abilities',  'value' => $abilities,               'inline' => false];
        if ($build->passive_ability) $fields[] = ['name' => 'Passive',    'value' => $build->passive_ability, 'inline' => true];
        if ($build->prevents_evolution) $fields[] = ['name' => 'Note',    'value' => '⚠ Prevents evolution', 'inline' => true];

        // Fetch base stats from PokéAPI and apply stat focus algorithm
        if ($build->dex_number) {
            $pokemon = \App\Services\PokemonService::getMon($build->dex_number);
            if ($pokemon && isset($pokemon->stats)) {
                $statOrder = ['hp', 'attack', 'defense', 'special-attack', 'special-defense', 'speed'];
                $baseStats = [];
                foreach ($pokemon->stats as $stat) {
                    $baseStats[] = $stat->base_stat;
                }

                // Parse stat focus string to indices [HP=0, ATK=1, DEF=2, SPATK=3, SPDEF=4, SPD=5]
                $statMap = ['HP' => 0, 'ATK' => 1, 'DEF' => 2, 'SP.ATK' => 3, 'SP.DEF' => 4, 'SPD' => 5];
                $focusParts = array_map('trim', explode('/', $build->stat_focus ?? ''));
                $focusIndices = array_values(array_filter(array_map(fn($s) => $statMap[$s] ?? null, $focusParts), fn($v) => $v !== null));

                $calculated = $this->calculateAltBuildStats($baseStats, $focusIndices, $build->rank ?? 1);
                [$hp, $atk, $def, $spa, $spd2, $spe] = $calculated;
                $bst = array_sum($calculated);

                $statsStr  = "```\n";
                $statsStr .= "HP:      " . $this->statBar((int)floor(($hp   / 255) * 100)) . " {$hp}\n";
                $statsStr .= "Atk:     " . $this->statBar((int)floor(($atk  / 255) * 100)) . " {$atk}\n";
                $statsStr .= "Def:     " . $this->statBar((int)floor(($def  / 255) * 100)) . " {$def}\n";
                $statsStr .= "Sp.Atk:  " . $this->statBar((int)floor(($spa  / 255) * 100)) . " {$spa}\n";
                $statsStr .= "Sp.Def:  " . $this->statBar((int)floor(($spd2 / 255) * 100)) . " {$spd2}\n";
                $statsStr .= "Speed:   " . $this->statBar((int)floor(($spe  / 255) * 100)) . " {$spe}\n";
                $statsStr .= "BST:     {$bst}\n```";

                $fields[] = ['name' => 'Stats', 'value' => $statsStr, 'inline' => false];
            }
        }

        $championLabels = \App\Models\AltBuild::championLabel();
        $champion = $championLabels[$build->champion] ?? ucfirst($build->champion ?? 'Unknown');
        $spriteUrl = "https://void.scooom.xyz/alt-build-sprite:{$build->build_id}.png?v=2";

        $embed = [
            'title'       => "{$build->species} — {$build->name}",
            'description' => "Champion: **{$champion}**",
            'color'       => 0x7c5cbf,
            'thumbnail'   => ['url' => $spriteUrl],
            'fields'      => $fields,
            'footer'      => ['text' => 'WikiMoDex • Alt Builds'],
            'url'         => 'https://void.scooom.xyz/wiki:alt-builds.html#champion-' . ($build->champion ?? ''),
        ];

        return response()->json([
            'type' => self::CHANNEL_MESSAGE,
            'data' => ['embeds' => [$embed]],
        ]);
    }

    private function calculateAltBuildStats(array $stats, array $focusIndices, int $rank): array
    {
        // Step 1: swap focus stats to highest positions (mirrors TypeScript algorithm)
        $newStats = $stats;
        foreach ($focusIndices as $rankIdx => $focusStat) {
            $ranked = [];
            for ($s = 0; $s <= 5; $s++) {
                $ranked[] = ['stat' => $s, 'value' => $newStats[$s]];
            }
            usort($ranked, fn($a, $b) => $b['value'] - $a['value']);
            $highestAtRank = $ranked[$rankIdx];
            if ($focusStat !== $highestAtRank['stat']) {
                $temp = $newStats[$focusStat];
                $newStats[$focusStat] = $newStats[$highestAtRank['stat']];
                $newStats[$highestAtRank['stat']] = $temp;
            }
        }

        // Step 2: scale BST to target
        $rankClamped = min($rank, 9);
        $targetBST   = 425 + ($rankClamped * 25);
        $currentBST  = array_sum($newStats);

        if ($currentBST >= $targetBST) return $newStats;

        $nonFocusIndices = array_values(array_filter([0,1,2,3,4,5], fn($i) => !in_array($i, $focusIndices)));
        $allocated = $newStats;
        $difference = $targetBST - $currentBST;

        if ($difference <= 30) {
            $focusTotal = array_sum(array_map(fn($i) => $newStats[$i], $focusIndices));
            foreach ($focusIndices as $i) {
                $proportion = $focusTotal > 0 ? $newStats[$i] / $focusTotal : 1 / count($focusIndices);
                $allocated[$i] = $newStats[$i] + (int)floor($difference * $proportion);
            }
            $scaledTotal = array_sum($allocated);
            $diff = $targetBST - $scaledTotal;
            $idx = 0;
            while ($diff !== 0 && $idx < 100) {
                $t = $focusIndices[$idx % count($focusIndices)];
                if ($diff > 0) { $allocated[$t]++; $diff--; }
                else           { $allocated[$t]--; $diff++; }
                $idx++;
            }
            return $allocated;
        }

        $statCap    = (int)floor($targetBST * 0.30);
        $focusTarget = (int)floor($statCap * 0.80);
        $focusBudget = count($focusIndices) * $focusTarget;
        $nonFocusBudget = $targetBST - $focusBudget;

        $nonFocusRanked = array_map(fn($i) => ['index' => $i, 'value' => $newStats[$i]], $nonFocusIndices);
        usort($nonFocusRanked, fn($a, $b) => $b['value'] - $a['value']);

        $weights = [];
        foreach ($nonFocusRanked as $r => $stat) {
            $pct = 0.50 - ($r / max(1, count($nonFocusRanked) - 1)) * 0.15;
            $weights[] = array_merge($stat, ['weight' => pow($pct, 1.5)]);
        }
        $totalWeight = array_sum(array_column($weights, 'weight'));
        foreach ($weights as $w) {
            $allocated[$w['index']] = max($newStats[$w['index']], (int)floor($nonFocusBudget * ($w['weight'] / $totalWeight)));
        }

        $focusBoost = 50;
        $focusWeights = array_map(fn($i) => $newStats[$i] + $focusBoost, $focusIndices);
        $totalFocusWeight = array_sum($focusWeights);
        foreach ($focusIndices as $k => $i) {
            $allocated[$i] = $totalFocusWeight > 0
                ? (int)floor($focusBudget * ($focusWeights[$k] / $totalFocusWeight))
                : (int)floor($focusBudget / count($focusIndices));
        }

        $capped = array_map(fn($s) => min($s, $statCap), $allocated);
        $diff = $targetBST - array_sum($capped);
        $allIndices = array_merge($focusIndices, $nonFocusIndices);
        $adjustIdx = 0;
        while ($diff !== 0 && $adjustIdx < 1000) {
            $t = $allIndices[$adjustIdx % count($allIndices)];
            if ($diff > 0 && $capped[$t] < $statCap)  { $capped[$t]++; $diff--; }
            elseif ($diff < 0 && $capped[$t] > 1)     { $capped[$t]--; $diff++; }
            $adjustIdx++;
        }
        return $capped;
    }

    private function generateAltBuildSprite(\App\Models\AltBuild $build): ?string
    {
        $script    = base_path('scripts/render_alt_build_sprite.py');
        $srcSprite = base_path("pokevoid/public/images/pokemon/{$build->dex_number}.png");
        $outPath   = storage_path("app/alt-build-sprites/{$build->build_id}.png");

        if (!file_exists($script) || !file_exists($srcSprite)) return null;
        if (!is_dir(dirname($outPath))) mkdir(dirname($outPath), 0755, true);

        $palette = escapeshellarg(json_encode($build->target_palette));
        shell_exec("python3 {$script} " . escapeshellarg($srcSprite) . " {$palette} " . escapeshellarg($outPath) . " 2>/dev/null");

        return file_exists($outPath) ? $outPath : null;
    }

    private function handleAbility(string $enumName): \Illuminate\Http\JsonResponse
    {
        $ability = \App\Models\Ability::where('enum_name', $enumName)->first();

        if (!$ability) {
            return response()->json([
                'type' => self::CHANNEL_MESSAGE,
                'data' => ['content' => "Ability `{$enumName}` not found!"]
            ]);
        }

        return response()->json([
            'type' => self::CHANNEL_MESSAGE,
            'data' => [
                'embeds' => [[
                    'title'       => $ability->name,
                    'description' => $ability->description,
                    'color'       => 0x7c5cbf,
                    'footer'      => ['text' => 'WikiMoDex • Ability']
                ]]
            ]
        ]);
    }

    private function handleForm(string $msg): \Illuminate\Http\JsonResponse
    {
        $msg      = str_replace('Ω', '_omega', $msg);
        $msgLower = trim(strtolower($msg));

        $form = $custom = $coreForm = $smittyMon = $smittyForm = false;

        $glitch = Glitch::whereRaw('LOWER(name) = ?', [$msgLower])->first();
        if ($glitch) {
            $form   = $glitch;
            $custom = true;
        } else {
            $form = BuiltInService::loadCore($msgLower);
            if ($form) {
                $coreForm = true;
            } else {
                $form = BuiltInService::loadSmitty($msgLower);
                if ($form) {
                    $smittyMon = true;
                } else {
                    $form = BuiltInService::loadSmittyForm($msgLower);
                    if ($form) { $smittyForm = true; }
                }
            }
        }

        if (!$form) {
            return response()->json([
                'type' => self::CHANNEL_MESSAGE,
                'data' => ['content' => "The GlitchDex was unable to locate `{$msg}`!"]
            ]);
        }

        try {
            $smitty  = $smittyMon || $smittyForm;
            $hasBase = $smittyForm || $coreForm;

            // URLs
            if ($custom) {
                $pageUrl   = 'https://void.scooom.xyz/g:' . urlencode(trim($form->name)) . ':' . $form->id . '.html';
                $frontUrl  = "https://void.scooom.xyz/front:{$form->id}.png";
                $backUrl   = "https://void.scooom.xyz/back:{$form->id}.png";
            } elseif ($coreForm) {
                $pageUrl  = "https://void.scooom.xyz/core:{$form->name}.html";
                $frontUrl = "https://void.scooom.xyz/cFront:{$form->name}.png";
                $backUrl  = "https://void.scooom.xyz/cBack:{$form->name}.png";
            } elseif ($smittyMon) {
                $pageUrl  = "https://void.scooom.xyz/smitty:{$form->name}.html";
                $frontUrl = "https://void.scooom.xyz/cFront:{$form->name}.png";
                $backUrl  = "https://void.scooom.xyz/cBack:{$form->name}.png";
            } else {
                $pageUrl  = "https://void.scooom.xyz/smittyForm:{$form->name}.html";
                $frontUrl = "https://void.scooom.xyz/cFront:{$form->name}.png";
                $backUrl  = "https://void.scooom.xyz/cBack:{$form->name}.png";
            }

            // Title
            $title = ucwords($form->name);
            if ($custom) {
                $title .= ' — Rating: ' . $form->getRating();
            }
            if (!empty($form->form_code ?? '')) {
                $title .= ' (' . $form->form_code . ')';
            }

            // Types
            if ($custom) {
                $data2   = $form->getJsonData();
                $typeOne = PokemonService::getTypeName($data2->primaryType);
                $typeTwo = PokemonService::getTypeName($data2->secondaryType);
            } else {
                $typeOne = PokemonService::getTypeName($form->type1);
                $typeTwo = PokemonService::getTypeName($form->type2 ?? -1);
            }
            $typesStr = $typeTwo !== 'Unknown' ? "{$typeOne} / {$typeTwo}" : $typeOne;

            // Abilities
            if ($custom) {
                $ab1 = $form->getAbilityOne();
                $ab2 = $form->getAbilityTwo();
                $ha  = $form->getAbilityHA();
                [$ab1Name, $ab1Desc] = [$ab1['name'], $ab1['desc']];
                [$ab2Name, $ab2Desc] = [$ab2['name'], $ab2['desc']];
                [$haName,  $haDesc]  = [$ha['name'],  $ha['desc']];
            } else {
                [$ab1Name, $ab1Desc] = [$form->ab1->name, $form->ab1->description];
                [$ab2Name, $ab2Desc] = [$form->ab2->name, $form->ab2->description];
                [$haName,  $haDesc]  = [$form->ha->name,  $form->ha->description];
            }

            // Stats
            if ($custom) {
                $ogStats    = $form->getOGStats();
                $ogBST      = array_sum(array_column($ogStats, 'value'));
                $stats      = $form->adjustStats($ogStats, $form->calculateTotalIncrease($ogBST));
                $bst        = array_sum(array_column($stats, 'value'));
                $statValues = array_column($stats, 'value');
            } else {
                $statValues = [$form->hp, $form->atk, $form->def, $form->spatk, $form->spdef, $form->spd];
                $bst        = $form->bst;
            }
            [$hp, $atk, $def, $spa, $spd2, $spe] = $statValues;

            // Base form
            $formOfStr = '';
            if ($hasBase) {
                $ogIds     = explode(',', $form->og_mon ?? '');
                $ogMon     = PokemonService::getMon(trim($ogIds[0]));
                $formOfStr = ucwords(str_replace('-', ' ', $ogMon->name));
            }

            // SMITTY items
            $smittyItemsStr = '';
            if ($smitty) {
                $items = BuiltInService::getSmittyItems(strtolower($form->name));
                $smittyItemsStr = $items !== false
                    ? implode(', ', (array)$items)
                    : 'Unknown — contact scooom on Discord';
            }

            // Build embed fields
            $fields = [];

            if ($formOfStr) {
                $fields[] = ['name' => 'Form of', 'value' => $formOfStr, 'inline' => true];
            }
            if ($custom) {
                $creator = \App\Models\User::find($form->created_by);
                $fields[] = ['name' => 'Created by', 'value' => "[{$creator->username}](https://void.scooom.xyz/u:{$creator->username}.html)", 'inline' => true];
            }

            $fields[] = ['name' => 'Types', 'value' => $typesStr, 'inline' => false];

            $fields[] = ['name' => 'Ability 1', 'value' => "**{$ab1Name}**\n{$ab1Desc}", 'inline' => true];
            $fields[] = ['name' => 'Ability 2', 'value' => "**{$ab2Name}**\n{$ab2Desc}", 'inline' => true];
            $fields[] = ['name' => "\u{200B}", 'value' => "\u{200B}", 'inline' => true];
            $fields[] = ['name' => 'Hidden Ability', 'value' => "**{$haName}**\n{$haDesc}", 'inline' => false];

            if ($smittyItemsStr) {
                $fields[] = ['name' => 'SMITTY Items', 'value' => $smittyItemsStr, 'inline' => false];
            }

            $statsStr = "```\n";
            $statsStr .= "HP:      " . $this->statBar((int)floor(($hp   / 255) * 100)) . " {$hp}\n";
            $statsStr .= "Atk:     " . $this->statBar((int)floor(($atk  / 255) * 100)) . " {$atk}\n";
            $statsStr .= "Def:     " . $this->statBar((int)floor(($def  / 255) * 100)) . " {$def}\n";
            $statsStr .= "Sp.Atk:  " . $this->statBar((int)floor(($spa  / 255) * 100)) . " {$spa}\n";
            $statsStr .= "Sp.Def:  " . $this->statBar((int)floor(($spd2 / 255) * 100)) . " {$spd2}\n";
            $statsStr .= "Speed:   " . $this->statBar((int)floor(($spe  / 255) * 100)) . " {$spe}\n";
            $statsStr .= "BST:     {$bst}\n```";
            $fields[] = ['name' => 'Stats', 'value' => $statsStr, 'inline' => false];

            if ($custom) {
                $rivals = $form->getRivals(true);
                if ($rivals) {
                    $fields[] = ['name' => 'Rivals', 'value' => $rivals, 'inline' => false];
                }
            }

            // Footer label
            $footerLabel = match(true) {
                $custom     => 'WikiMoDex • Mod Glitch Form',
                $coreForm   => 'WikiMoDex • Core Glitch',
                $smittyMon  => 'WikiMoDex • SMITTY Pokémon',
                $smittyForm => 'WikiMoDex • SMITTY Form',
                default     => 'WikiMoDex',
            };

            return response()->json([
                'type' => self::CHANNEL_MESSAGE,
                'data' => [
                    'embeds' => [[
                        'title'     => $title,
                        'url'       => $pageUrl,
                        'color'     => 0x7c5cbf,
                        'thumbnail' => ['url' => $frontUrl],
                        'fields'    => $fields,
                        'footer'    => ['text' => $footerLabel],
                    ]]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'type' => self::CHANNEL_MESSAGE,
                'data' => ['content' => "The GlitchDex encountered an error looking up `{$msg}`!"]
            ]);
        }
    }
    private function statBar(int $value): string
    {
        $rounded = round($value / 5) * 5;
        $div5    = (int)($rounded / 5);
        $remain  = 20 - $div5;
        return str_repeat('█', $div5) . str_repeat('░', $remain);
    }
}
