<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PokemonService
{
    public static function getAbility($ability, $array = false)
    {
        $cacheKey = 'ability_' . $ability;

        $data = Cache::rememberForever($cacheKey, function () use ($ability) {
            $response = Http::get('https://pokeapi.co/api/v2/ability/' . $ability);
            $data = $response->object();

            $return = ['name' => 'Unknown', 'desc' => 'Unknown'];
            foreach ($data->effect_entries as $lang) {
                if ($lang->language->name === 'en') {
                    $return['desc'] = $lang->short_effect;
                    break;
                }
            }
            $return['name'] = ucwords(str_replace('-', ' ', $data->name));
            return $return;
        });

        return $array ? (array) $data : (object) $data;
    }

    public static function getMon($mon)
    {
        $cacheKey = 'pokemon_' . $mon;

        $data = Cache::rememberForever($cacheKey, function () use ($mon) {
            $response = Http::get('https://pokeapi.co/api/v2/pokemon/' . $mon);
            return $response->body();
        });

        return json_decode($data);
    }

    /**
     * Map PokeVoid numeric type ID to a display name.
     * Matches the Type enum order in the PokeVoid source.
     */
    public static function getTypeName(int|string $typeId): string
    {
        // Order matches PokeVoid's Type enum (from scripts/parse_forms.py load_types())
        $types = [
            0  => 'Normal',
            1  => 'Fighting',
            2  => 'Flying',
            3  => 'Poison',
            4  => 'Ground',
            5  => 'Rock',
            6  => 'Bug',
            7  => 'Ghost',
            8  => 'Steel',
            9  => 'Fire',
            10 => 'Water',
            11 => 'Grass',
            12 => 'Electric',
            13 => 'Psychic',
            14 => 'Ice',
            15 => 'Dragon',
            16 => 'Dark',
            17 => 'Fairy',
        ];

        return $types[(int) $typeId] ?? 'Unknown';
    }

}
