<?php

namespace App\Http\Controllers;

use App\Models\CorePokemon;
use App\Models\BuiltinForm;
use App\Models\Glitch;
use App\Models\AltBuild;
use Illuminate\Http\Request;

class PokemonSearchController extends Controller
{
    /**
     * Unified Pokémon search across all sources.
     * Returns [{label, value, category, dex}] for the build form typeahead.
     */
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = collect();
        $like = "%{$q}%";

        // 1. Official Pokémon + forms (core_pokemon)
        CorePokemon::where('name', 'like', $like)
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", ["{$q}%"])
            ->limit(20)
            ->get()
            ->each(fn($p) => $results->push([
                'label'    => $p->name,
                'value'    => $p->name,
                'category' => 'Official',
                'dex'      => $p->dex_number,
                'ability1' => $p->ability1,
                'ability2' => $p->ability2,
                'abilityH' => $p->ability_hidden,
            ]));

        // 2. Core glitches + smitty forms (builtin_forms)
        BuiltinForm::with(['ab1', 'ab2', 'ha'])
            ->where('name', 'like', $like)
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", ["{$q}%"])
            ->limit(15)
            ->get()
            ->each(fn($f) => $results->push([
                'label'    => $f->name,
                'value'    => $f->name,
                'category' => match($f->form_type) {
                    'core'        => 'Core Glitch',
                    'smitty'      => 'SMITTY Pokémon',
                    'smitty_form' => 'SMITTY Form',
                    default       => 'Custom',
                },
                'dex'      => null,
                'ability1' => $f->ab1?->name,
                'ability2' => $f->ab2?->name,
                'abilityH' => $f->ha?->name,
            ]));

        // 3. Mod glitch forms (user-uploaded)
        Glitch::where('name', 'like', $like)
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", ["{$q}%"])
            ->limit(10)
            ->get()
            ->each(fn($g) => $results->push([
                'label'    => $g->name,
                'value'    => $g->name,
                'category' => 'Mod Glitch',
                'dex'      => null,
                'ability1' => null,
                'ability2' => null,
                'abilityH' => null,
            ]));

        // 4. Alt builds
        AltBuild::where('name', 'like', $like)
            ->orWhere('species', 'like', $like)
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", ["{$q}%"])
            ->limit(10)
            ->get()
            ->each(fn($a) => $results->push([
                'label'    => $a->name . ' (' . $a->species . ')',
                'value'    => $a->name,
                'category' => 'Alt Build',
                'dex'      => $a->dex_number,
                'ability1' => $a->ability1,
                'ability2' => $a->ability2,
                'abilityH' => $a->passive_ability,
            ]));

        // Deduplicate by value, official first
        $seen = [];
        $deduped = $results->filter(function ($r) use (&$seen) {
            $key = strtolower($r['value']);
            if (isset($seen[$key])) return false;
            $seen[$key] = true;
            return true;
        })->values();

        return response()->json($deduped)
            ->header('Cache-Control', 'no-store');
    }
}
