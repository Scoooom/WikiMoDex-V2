<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rival extends Model
{
    protected $fillable = [
        'rival_id',
        'slug',
        'name',
        'role',
        'game',
        'type',
        'portrait',
        'encounter_pokemon',
        'rematch_pokemon',
    ];

    protected $casts = [
        'encounter_pokemon' => 'array',
        'rematch_pokemon'   => 'array',
    ];

    public function portraitUrl(): string
    {
        return '/rivals/' . $this->portrait;
    }
}
