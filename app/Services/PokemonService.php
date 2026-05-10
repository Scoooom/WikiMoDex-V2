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

}
