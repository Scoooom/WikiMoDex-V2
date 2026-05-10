<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameItem extends Model
{
    protected $fillable = ['key', 'name', 'description', 'tier', 'pool', 'conditional'];

    protected $casts = ['conditional' => 'boolean'];

    public const TIER_ORDER = ['MEH', 'COMMON', 'GREAT', 'ULTRA', 'ROGUE', 'MASTER', 'LUXURY', 'OMEGA'];

    public const TIER_LABELS = [
        'MEH'    => 'Meh',
        'COMMON' => 'Common',
        'GREAT'  => 'Great',
        'ULTRA'  => 'Ultra',
        'ROGUE'  => 'Rogue',
        'MASTER' => 'Master',
        'LUXURY' => 'Luxury',
        'OMEGA'  => 'Ω Items',
    ];

    public const TIER_COLORS = [
        'MEH'    => '#888888',
        'COMMON' => '#9db8e8',
        'GREAT'  => '#5aabff',
        'ULTRA'  => '#c9a8ff',
        'ROGUE'  => '#e8824a',
        'MASTER' => '#f5d76e',
        'LUXURY' => '#ff6eef',
        'OMEGA'  => '#7c5cbf',
    ];
}
