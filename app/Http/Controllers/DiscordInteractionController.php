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

        $tpl  = "# {$ability->name}\n";
        $tpl .= $ability->description;

        return response()->json([
            'type' => self::CHANNEL_MESSAGE,
            'data' => ['content' => $tpl]
        ]);
    }

    private function handleForm(string $msg): \Illuminate\Http\JsonResponse
    {
        $msg = str_replace('Ω', '_omega', $msg);
        $msgLower = trim(strtolower($msg));

        $form     = null;
        $custom   = false;
        $coreForm = false;
        $smittyMon  = false;
        $smittyForm = false;

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
                    if ($form) {
                        $smittyForm = true;
                    }
                }
            }
        }

        if (!$form) {
            return response()->json([
                'type' => self::CHANNEL_MESSAGE,
                'data' => ['content' => "The GlitchDex was unable to locate `{$msg}`!"]
            ]);
        }

        $smitty  = $smittyMon || $smittyForm;
        $hasBase = $smittyForm || $coreForm;

        try {
            if ($custom) {
                $url      = 'g:' . urlencode(trim($form->name)) . ':' . $form->id;
                $rating   = $form->getRating();
                $nameStr  = "[".ucwords($form->name)." (Rating: {$rating})](<https://void.scooom.xyz/{$url}.html>)";
                $creator  = \App\Models\User::find($form->created_by);
                $makerStr = "## Created By [{$creator->username}](<https://void.scooom.xyz/u:{$creator->username}.html>)\n";
                $codeStr  = '';
                $smittyItemsStr = '';
                $rivalsStr = "\n-# Rivals: ||" . $form->getRivals(true) . "||";
            } elseif ($coreForm) {
                $url      = 'core:' . $form->name;
                $nameStr  = "[".ucwords($form->name)."](<https://void.scooom.xyz/{$url}.html>)";
                $makerStr = '';
                $codeStr  = '';
                $smittyItemsStr = '';
                $rivalsStr = '';
            } elseif ($smittyMon) {
                $url     = 'smitty:' . $form->name;
                $code    = $form->form_code ?? '';
                $codeStr = $code ? " - ||`{$code}`||" : '';
                $nameStr = "[".ucwords($form->name)."](<https://void.scooom.xyz/{$url}.html>)";
                $makerStr = '';
                $items   = BuiltInService::getSmittyItems(strtolower($form->name));
                $smittyItemsStr = "\nSmitty Items: `" . ($items !== false ? implode(', ', (array)$items) : 'Unknown! Please contact scooom on Discord!') . "`";
                $rivalsStr = '';
            } else {
                $url     = 'smittyForm:' . $form->name;
                $code    = $form->form_code ?? '';
                $codeStr = $code ? " - ||`{$code}`||" : '';
                $nameStr = "[".ucwords($form->name)."](<https://void.scooom.xyz/{$url}.html>)";
                $makerStr = '';
                $items   = BuiltInService::getSmittyItems(strtolower($form->name));
                $smittyItemsStr = "\nSmitty Items: `" . ($items !== false ? implode(', ', (array)$items) : 'Unknown! Please contact scooom on Discord!') . "`";
                $rivalsStr = '';
            }

            if ($hasBase) {
                $ogIds     = explode(',', $form->og_mon ?? '');
                $ogMon     = PokemonService::getMon(trim($ogIds[0]));
                $formOfStr = "\nForm Of: " . ucwords($ogMon->name);
            } else {
                $formOfStr = '';
            }

            if ($custom) {
                $data2   = $form->getJsonData();
                $typeOne = $this->typeEn($data2->primaryType);
                $typeTwo = $this->typeEn($data2->secondaryType);
            } else {
                $typeOne = $this->typeEn($form->type1);
                $typeTwo = $this->typeEn($form->type2 ?? -1);
            }

            if ($custom) {
                $ab1 = $form->getAbilityOne();
                $ab2 = $form->getAbilityTwo();
                $ha  = $form->getAbilityHA();
                $ab1Name = $ab1['name']; $ab1Desc = $ab1['desc'];
                $ab2Name = $ab2['name']; $ab2Desc = $ab2['desc'];
                $haName  = $ha['name'];  $haDesc  = $ha['desc'];
            } else {
                $ab1Name = $form->ab1->name; $ab1Desc = $form->ab1->description;
                $ab2Name = $form->ab2->name; $ab2Desc = $form->ab2->description;
                $haName  = $form->ha->name;  $haDesc  = $form->ha->description;
            }

            if ($custom) {
                $ogStats      = $form->getOGStats();
                $ogBST        = array_sum(array_column($ogStats, 'value'));
                $stats        = $form->adjustStats($ogStats, $form->calculateTotalIncrease($ogBST));
                $bst          = array_sum(array_column($stats, 'value'));
                $statValues   = array_column($stats, 'value');
                $statPercents = array_column($stats, 'percent');
            } else {
                $statValues   = [$form->hp, $form->atk, $form->def, $form->spatk, $form->spdef, $form->spd];
                $statPercents = array_map(fn($v) => floor(($v / 255) * 100), $statValues);
                $bst          = $form->bst;
            }

            $tpl  = "# {$nameStr}{$codeStr}\n";
            $tpl .= $makerStr;
            $tpl .= $formOfStr . "\n";
            $tpl .= "### Typing: `{$typeOne}` / `{$typeTwo}`\n";
            $tpl .= "### Abilities\n";
            $tpl .= "`{$ab1Name}`\n-# ||`{$ab1Desc}`||\n";
            $tpl .= "`{$ab2Name}`\n-# ||`{$ab2Desc}`||\n";
            $tpl .= "Hidden: `{$haName}`\n-# ||`{$haDesc}`||";
            $tpl .= $smittyItemsStr . "\n";
            $tpl .= "### Stats\n";
            $tpl .= "||`" . $this->statBar($statPercents[0]) . "` HP: {$statValues[0]}\n";
            $tpl .= "`" . $this->statBar($statPercents[1]) . "` Attack: {$statValues[1]}\n";
            $tpl .= "`" . $this->statBar($statPercents[2]) . "` Defense: {$statValues[2]}\n";
            $tpl .= "`" . $this->statBar($statPercents[3]) . "` Special Attack: {$statValues[3]}\n";
            $tpl .= "`" . $this->statBar($statPercents[4]) . "` Special Defence: {$statValues[4]}\n";
            $tpl .= "`" . $this->statBar($statPercents[5]) . "` Speed: {$statValues[5]}\n";
            $tpl .= "BST: {$bst}||";
            $tpl .= $rivalsStr;

            // Note: HTTP interactions can't send files directly
            // We'll send the text response and include sprite URLs
            if ($custom) {
                $tpl .= "\n[Front Sprite](<https://void.scooom.xyz/front:{$form->id}.png>) | [Back Sprite](<https://void.scooom.xyz/back:{$form->id}.png>)";
            } else {
                $tpl .= "\n[Front Sprite](<https://void.scooom.xyz/cFront:{$form->name}.png>) | [Back Sprite](<https://void.scooom.xyz/cBack:{$form->name}.png>)";
            }

            return response()->json([
                'type' => self::CHANNEL_MESSAGE,
                'data' => [
                    'content' => $tpl,
                    'embeds' => [
                        [
                            'image' => [
                                'url' => $custom
                                    ? "https://void.scooom.xyz/front:{$form->id}.png"
                                    : "https://void.scooom.xyz/cFront:{$form->name}.png"
                            ]
                        ],
                        [
                            'image' => [
                                'url' => $custom
                                    ? "https://void.scooom.xyz/back:{$form->id}.png"
                                    : "https://void.scooom.xyz/cBack:{$form->name}.png"
                            ]
                        ]
                    ]
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
        return str_repeat('=', $div5) . str_repeat('-', $remain);
    }

    private function typeEn(int $id): string
    {
        return [
            0 => 'Normal', 1 => 'Fighting', 2 => 'Flying', 3 => 'Poison',
            4 => 'Ground', 5 => 'Rock', 6 => 'Bug', 7 => 'Ghost',
            8 => 'Steel', 9 => 'Fire', 10 => 'Water', 11 => 'Grass',
            12 => 'Electric', 13 => 'Psychic', 14 => 'Ice', 15 => 'Dragon',
            16 => 'Dark', 17 => 'Fairy',
        ][$id] ?? 'Unknown';
    }
}
