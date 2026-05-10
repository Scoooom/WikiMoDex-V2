<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AltBuild extends Model
{
    protected $fillable = [
        'build_id', 'name', 'species', 'dex_number', 'champion', 'rank',
        'type1', 'type2', 'stat_focus', 'ability1', 'ability2', 'ability3',
        'passive_ability', 'key_moves', 'prevents_evolution', 'prerequisite_build',
        'target_palette', 'dark_palette',
    ];

    protected $casts = [
        'prevents_evolution' => 'boolean',
        'key_moves'          => 'array',
        'target_palette'     => 'array',
        'dark_palette'       => 'array',
    ];

    public function getTypesAttribute(): string
    {
        return collect([$this->type1, $this->type2])->filter()->join(' / ');
    }

    public static function championLabel(): array
    {
        return [
            'brock'       => 'Brock',
            'misty'       => 'Misty',
            'apollo_diana'=> 'Apollo / Diana',
            'lt_surge'    => 'Lt. Surge',
        ];
    }
}
