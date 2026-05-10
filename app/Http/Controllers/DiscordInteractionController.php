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
                    'content' =>
                        "The WikiMoDex can be found [here](<https://void.scooom.xyz/>)! " .
                        "You can use it to look up [Core Glitches](<https://void.scooom.xyz/galleryCore.html>) " .
                        "and [Mod Glitches](<https://void.scooom.xyz/gallery.html>).\n" .
                        "Additionally, you may find the [FAQ](<https://void.scooom.xyz/faq.html>) " .
                        "and the [Legendary Up And PokeRus Calendar](<https://void.scooom.xyz/gacha.html>)!"
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

        return response()->json([
            'type' => self::CHANNEL_MESSAGE,
            'data' => ['content' => 'Unknown command.']
        ]);
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
            } elseif ($coreForm) {
                $pageUrl  = "https://void.scooom.xyz/core:{$form->name}.html";
                $frontUrl = "https://void.scooom.xyz/cFront:{$form->name}.png";
            } elseif ($smittyMon) {
                $pageUrl  = "https://void.scooom.xyz/smitty:{$form->name}.html";
                $frontUrl = "https://void.scooom.xyz/cFront:{$form->name}.png";
            } else {
                $pageUrl  = "https://void.scooom.xyz/smittyForm:{$form->name}.html";
                $frontUrl = "https://void.scooom.xyz/cFront:{$form->name}.png";
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
